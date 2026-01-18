<?php

namespace App\Services;

use App\Models\Expediente;
use App\Models\Documento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ExpedienteService
{
    protected PersonaService $personaService;
    protected NumeracionService $numeracionService;

    public function __construct(
        PersonaService $personaService,
        NumeracionService $numeracionService
    ) {
        $this->personaService = $personaService;
        $this->numeracionService = $numeracionService;
    }

    /**
     * Registra un nuevo expediente con toda su información
     */
    public function registrarExpediente(array $data): Expediente
    {
        return DB::transaction(function () use ($data) {
            // 1. Obtener o crear persona
            $persona = $this->personaService->obtenerOCrear($data);

            // 2. Generar código de expediente
            $codigo = $this->numeracionService->generarCodigo();

            // 3. Preparar observaciones
            $observaciones = $this->prepararObservaciones($data);

            // 4. Crear expediente
            $expediente = Expediente::create([
                'codigo_expediente' => $codigo,
                'asunto' => $data['asunto'],
                'asunto_documento' => $data['asunto_documento'] ?? null,
                'asunto_tramite' => $data['asunto'] ?? null, // El campo "asunto" del form es el asunto del trámite
                'tipo_documento_entrante' => $data['tipo_documento_entrante'] ?? null,
                'folios' => $data['folios'] ?? 1,
                'id_persona' => $persona->id_persona,
                'remitente' => $this->personaService->obtenerNombreCompleto($persona),
                'dni_remitente' => $persona->numero_documento,
                'id_tipo_tramite' => $data['id_tipo_tramite'],
                'fecha_registro' => now(),
                'estado' => 'recepcionado',
                'canal' => 'presencial',
                'observaciones' => $observaciones,
            ]);

            // 5. Registrar historial inicial
            $expediente->agregarHistorial(
                'Expediente registrado en Mesa de Partes',
                auth()->id()
            );

            // 6. Adjuntar documento principal si existe
            if (isset($data['documento']) && $data['documento'] instanceof UploadedFile) {
                $this->adjuntarDocumentoPrincipal($expediente, $data['documento']);
            }

            return $expediente;
        });
    }

    /**
     * Prepara las observaciones concatenando todos los detalles
     */
    protected function prepararObservaciones(array $data): string
    {
        $observacionesArray = [];

        // Observaciones del trámite
        if (!empty($data['observaciones'])) {
            $observacionesArray[] = 'Trámite: ' . $data['observaciones'];
        }

        // Observaciones de documentos
        if (!empty($data['observaciones_documentos'])) {
            $observacionesArray[] = 'Documentos: ' . $data['observaciones_documentos'];
        }

        // Documentos verificados
        if (!empty($data['documentos_verificados'])) {
            $docsVerificados = implode(', ', $data['documentos_verificados']);
            $observacionesArray[] = 'Docs verificados: ' . $docsVerificados;
        }

        // Documentos adicionales
        if (!empty($data['documentos_adicionales'])) {
            $docsAdicionales = implode(', ', $data['documentos_adicionales']);
            $observacionesArray[] = 'Docs adicionales: ' . $docsAdicionales;
        }

        return implode(' | ', $observacionesArray);
    }

    /**
     * Genera la carpeta de almacenamiento para un expediente
     * Estructura: expedientes/{año}/{codigo_expediente}/
     */
    protected function getCarpetaExpediente(Expediente $expediente): string
    {
        $año = $expediente->created_at->year;
        return "expedientes/{$año}/{$expediente->codigo_expediente}";
    }

    /**
     * Adjunta el documento principal al expediente
     */
    protected function adjuntarDocumentoPrincipal(Expediente $expediente, UploadedFile $archivo): Documento
    {
        $carpeta = $this->getCarpetaExpediente($expediente);
        $nombreOriginal = $archivo->getClientOriginalName();
        $nombreLimpio = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreOriginal);

        $path = $archivo->storeAs($carpeta, $nombreLimpio, 'public');

        return Documento::create([
            'id_expediente' => $expediente->id_expediente,
            'nombre' => pathinfo($nombreOriginal, PATHINFO_FILENAME),
            'ruta_pdf' => $path,
            'tipo' => 'entrada',
        ]);
    }

    /**
     * Adjunta un documento adicional al expediente
     */
    public function adjuntarDocumento(
        Expediente $expediente,
        UploadedFile $archivo,
        string $nombre,
        string $tipo = 'entrada'
    ): Documento {
        $carpeta = $this->getCarpetaExpediente($expediente);
        $nombreOriginal = $archivo->getClientOriginalName();
        $nombreLimpio = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreOriginal);

        $path = $archivo->storeAs($carpeta, $nombreLimpio, 'public');

        $documento = Documento::create([
            'id_expediente' => $expediente->id_expediente,
            'nombre' => $nombre ?: pathinfo($nombreOriginal, PATHINFO_FILENAME),
            'ruta_pdf' => $path,
            'tipo' => $tipo,
        ]);

        // Registrar en historial
        $expediente->agregarHistorial(
            "Documento adjuntado: {$documento->nombre}",
            auth()->id()
        );

        return $documento;
    }
}
