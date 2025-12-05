<?php

// Namespace para organizar las clases del modelo
namespace App\Models;

// Importar la clase base Model de Eloquent ORM
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Expediente - Representa un trámite en el sistema Mesa de Partes
 * Maneja todo el ciclo de vida de un expediente.
 */
class Expediente extends Model
{
    // Constante que define todos los estados posibles del expediente en el flujo.
    const ESTADOS = [
        'pendiente' => 'Pendiente',     // Estado inicial - expediente creado pero no procesado
        'registrado' => 'Registrado',   // Mesa de Partes ha registrado el expediente
        'clasificado' => 'Clasificado', // Se ha asignado tipo de trámite y área responsable
        'derivado' => 'Derivado',       // Enviado a área específica para procesamiento
        'en_proceso' => 'En Proceso',   // Funcionario está trabajando en el expediente
        'observado' => 'Observado',     // Requiere correcciones o información adicional
        'resuelto' => 'Resuelto',       // Trámite completado con resolución
        'aprobado' => 'Aprobado',       // Solicitud aprobada oficialmente
        'rechazado' => 'Rechazado',     // Solicitud rechazada con fundamentos
        'archivado' => 'Archivado'      // Expediente finalizado y archivado
    ];
    
    protected $fillable = [
        'codigo_expediente',
        'asunto',
        'id_tipo_tramite',
        'id_ciudadano',
        'id_persona',
        'fecha_registro',
        'estado',
        'prioridad',
        'canal',
        'id_area',
        'id_funcionario_asignado',
        'remitente',
        'dni_remitente',
        'observaciones',
        'observaciones_funcionario',
        'fecha_archivo',
        'fecha_resolucion'
    ];

    protected $casts = [
        'fecha_registro' => 'date',
        'fecha_archivo' => 'datetime',
        'fecha_resolucion' => 'datetime'
    ];

    // ===== RELACIONES ELOQUENT =====
    // Estas funciones definen las relaciones entre tablas usando Eloquent ORM
    
    /**
     * Relación: Un expediente pertenece a un ciudadano (usuario)
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ciudadano()
    {
        // belongsTo: relación muchos-a-uno con tabla users usando clave foránea id_ciudadano
        return $this->belongsTo(User::class, 'id_ciudadano');
    }

    /**
     * Relación: Un expediente pertenece a un tipo de trámite específico
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipoTramite()
    {
        // Vincula con la tabla tipo_tramites para obtener información del trámite
        return $this->belongsTo(TipoTramite::class, 'id_tipo_tramite');
    }

    /**
     * Relación: Un expediente puede tener múltiples documentos adjuntos
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documentos()
    {
        // hasMany: relación uno-a-muchos con tabla documentos
        return $this->hasMany(Documento::class, 'id_expediente');
    }

    /**
     * Relación: Un expediente puede tener múltiples derivaciones (movimientos entre áreas)
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function derivacions()
    {
        // Obtiene todas las derivaciones del expediente ordenadas por fecha
        return $this->hasMany(Derivacion::class, 'id_expediente');
    }
    
    /**
     * Alias para compatibilidad con código existente
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function derivaciones()
    {
        // Retorna la misma relación que derivacions() para mantener compatibilidad
        return $this->derivacions();
    }

    public function historial()
    {
        return $this->hasMany(HistorialExpediente::class, 'id_expediente');
    }

    public function observaciones()
    {
        return $this->hasMany(Observacion::class, 'id_expediente');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }

    public function funcionarioAsignado()
    {
        return $this->belongsTo(User::class, 'id_funcionario_asignado');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function resolucion()
    {
        return $this->hasOne(Resolucion::class, 'id_expediente');
    }

    // ===== MÉTODOS AUXILIARES =====
    // Funciones de utilidad para operaciones comunes del expediente
    
    /**
     * Agrega una entrada al historial del expediente
     * @param string $descripcion - Descripción de la acción realizada
     * @param int $usuarioId - ID del usuario que realiza la acción
     * @return \App\Models\HistorialExpediente
     */
    public function agregarHistorial($descripcion, $usuarioId)
    {
        // Crea un nuevo registro en la tabla historial_expedientes
        return $this->historial()->create([
            'descripcion' => $descripcion,  // Qué acción se realizó
            'id_usuario' => $usuarioId,     // Quién la realizó
            'fecha' => now()                // Cuándo se realizó (timestamp actual)
        ]);
    }

    /**
     * Obtiene la última derivación del expediente
     * @return \App\Models\Derivacion|null
     */
    public function derivacionActual()
    {
        // latest(): ordena por created_at DESC, first(): obtiene el primer resultado
        return $this->derivacions()->latest()->first();
    }

    /**
     * Verifica si un usuario puede eliminar este expediente según reglas de negocio
     * @param \App\Models\User $usuario - Usuario que intenta eliminar
     * @return bool
     */
    public function puedeEliminar($usuario)
    {
        // Obtener el rol del usuario para aplicar reglas específicas
        $rol = $usuario->role->nombre;
        
        // REGLA 1: Administrador tiene permisos totales
        if ($rol === 'Administrador') {
            return true; // Puede eliminar cualquier expediente en cualquier estado
        }
        
        // REGLA 2: Ciudadano solo puede eliminar sus propios expedientes no procesados
        if ($rol === 'Ciudadano') {
            // Verificar que sea su expediente Y que esté en estado pendiente
            return $this->id_ciudadano === $usuario->id && $this->estado === 'pendiente';
        }
        
        // REGLA 3: Mesa de Partes puede eliminar expedientes en estados iniciales
        if ($rol === 'Mesa de Partes') {
            // Solo puede eliminar si no ha sido procesado aún
            return in_array($this->estado, ['pendiente', 'registrado']);
        }
        
        // Por defecto, denegar eliminación para otros roles
        return false;
    }
    
    // Método para validar estados
    public static function getEstadosValidos()
    {
        return array_keys(self::ESTADOS);
    }
    
    public function getEstadoFormateado()
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }
    
    public function esEstadoValido($estado)
    {
        return array_key_exists($estado, self::ESTADOS);
    }
    
    // Estado inteligente basado en el proceso real
    public function getEstadoInteligenteAttribute()
    {
        // Si está clasificado = "Clasificado" (listo para derivar)
        if ($this->estado === 'clasificado') {
            return 'clasificado';
        }
        
        // Si tiene funcionario asignado y está derivado = "Asignado"
        if ($this->estado === 'derivado' && $this->id_funcionario_asignado) {
            return 'asignado';
        }
        
        // Si está derivado pero sin funcionario = "Derivado"
        if ($this->estado === 'derivado' && !$this->id_funcionario_asignado) {
            return 'derivado';
        }
        
        // Si está en proceso pero no ha sido recibido = "Por Recibir"
        if ($this->estado === 'derivado' && $this->id_funcionario_asignado) {
            $ultimaDerivacion = $this->derivacions()->latest()->first();
            if ($ultimaDerivacion && !$ultimaDerivacion->fecha_recepcion) {
                return 'por_recibir';
            }
        }
        
        return $this->estado;
    }
    
    public function getEstadoFormateadoInteligente()
    {
        $estadosInteligentes = [
            'pendiente' => 'Pendiente',
            'clasificado' => 'Clasificado',
            'asignado' => 'Asignado',
            'por_recibir' => 'Por Recibir',
            'derivado' => 'Derivado',
            'en_proceso' => 'En Proceso',
            'observado' => 'Observado',
            'resuelto' => 'Resuelto',
            'aprobado' => 'Aprobado',
            'rechazado' => 'Rechazado',
            'archivado' => 'Archivado'
        ];
        
        return $estadosInteligentes[$this->estado_inteligente] ?? $this->getEstadoFormateado();
    }
    
    public function getColorEstadoInteligente()
    {
        $colores = [
            'pendiente' => 'warning',      // Amarillo - Necesita acción
            'clasificado' => 'info',       // Azul - Clasificado, listo para derivar
            'asignado' => 'primary',       // Azul oscuro - Asignado a funcionario
            'por_recibir' => 'secondary',  // Gris - Esperando recepción
            'derivado' => 'success',       // Verde - Derivado correctamente
            'en_proceso' => 'primary',     // Azul - Trabajando en él
            'observado' => 'warning',      // Amarillo - Requiere atención
            'resuelto' => 'success',       // Verde - Completado
            'aprobado' => 'success',       // Verde - Aprobado
            'rechazado' => 'danger',       // Rojo - Rechazado
            'archivado' => 'secondary'     // Gris - Finalizado
        ];
        
        return $colores[$this->estado_inteligente] ?? 'secondary';
    }
}