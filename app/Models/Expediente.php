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
    // Usar clave primaria personalizada
    protected $primaryKey = 'id_expediente';
    
    // FLUJO OFICIAL DE MESA DE PARTES - Estados según normativa gubernamental
    const ESTADOS = [
        'recepcionado' => 'Recepcionado',   // 1. Documento recibido por Mesa de Partes
        'registrado' => 'Registrado',       // 2. Registrado con número de expediente
        'clasificado' => 'Clasificado',     // 3. Clasificado según tipo de trámite
        'derivado' => 'Derivado',           // 4. Derivado a área competente
        'en_proceso' => 'En Proceso',       // 5. En procesamiento por funcionario
        'observado' => 'Observado',         // 5a. Requiere subsanación/información adicional
        'resuelto' => 'Resuelto',           // 6. Resuelto con respuesta oficial
        'notificado' => 'Notificado',       // 7. Notificado al administrado
        'archivado' => 'Archivado'          // 8. Archivado definitivamente
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
        return $this->belongsTo(User::class, 'id_ciudadano', 'id');
    }

    /**
     * Relación: Un expediente pertenece a un tipo de trámite específico
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipoTramite()
    {
        // Vincula con la tabla tipo_tramites para obtener información del trámite
        return $this->belongsTo(TipoTramite::class, 'id_tipo_tramite', 'id_tipo_tramite');
    }

    /**
     * Relación: Un expediente puede tener múltiples documentos adjuntos
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documentos()
    {
        // hasMany: relación uno-a-muchos con tabla documentos
        return $this->hasMany(Documento::class, 'id_expediente', 'id_expediente');
    }

    /**
     * Relación: Un expediente puede tener múltiples derivaciones (movimientos entre áreas)
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function derivaciones()
    {
        // Obtiene todas las derivaciones del expediente ordenadas por fecha
        return $this->hasMany(Derivacion::class, 'id_expediente', 'id_expediente');
    }

    /**
     * Alias para compatibilidad con código antiguo (DEPRECATED)
     * @deprecated Use derivaciones() instead
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function derivacions()
    {
        // Retorna la misma relación que derivaciones() para mantener compatibilidad
        return $this->derivaciones();
    }

    public function historial()
    {
        return $this->hasMany(HistorialExpediente::class, 'id_expediente', 'id_expediente');
    }

    public function observaciones()
    {
        return $this->hasMany(Observacion::class, 'id_expediente', 'id_expediente');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }

    public function funcionarioAsignado()
    {
        return $this->belongsTo(User::class, 'id_funcionario_asignado', 'id');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function resolucion()
    {
        return $this->hasOne(Resolucion::class, 'id_expediente', 'id_expediente');
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
        return $this->derivaciones()->latest()->first();
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
            // Verificar que sea su expediente Y que esté en estado inicial
            return $this->id_ciudadano === $usuario->id && $this->estado === 'recepcionado';
        }
        
        // REGLA 3: Mesa de Partes puede eliminar expedientes en estados iniciales
        if ($rol === 'Mesa de Partes') {
            // Solo puede eliminar si no ha sido derivado aún
            return in_array($this->estado, ['recepcionado', 'registrado']);
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
            $ultimaDerivacion = $this->derivaciones()->latest()->first();
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