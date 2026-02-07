<?php

// Namespace para organizar las clases del modelo
namespace App\Models;

// Importar la clase base Model de Eloquent ORM
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Modelo Expediente - Representa un trámite en el sistema Mesa de Partes
 * Maneja todo el ciclo de vida de un expediente.
 */
class Expediente extends Model
{
    // Usar clave primaria personalizada
    protected $primaryKey = 'id_expediente';
    
    // ESTADOS: Ahora se obtienen de la tabla estados_expediente via relación
    // La constante se mantiene para compatibilidad, pero la fuente de verdad es la BD
    const ESTADOS = [
        'pendiente' => 'Pendiente',
        'recepcionado' => 'Recepcionado',
        'registrado' => 'Registrado',
        'clasificado' => 'Clasificado',
        'derivado' => 'Derivado',
        'asignado' => 'Asignado',
        'en_proceso' => 'En Proceso',
        'devuelto_jefe' => 'Devuelto al Jefe',
        'en_revision' => 'En Revisión',
        'observado' => 'Observado',
        'resuelto' => 'Resuelto',
        'aprobado' => 'Aprobado',
        'rechazado' => 'Rechazado',
        'notificado' => 'Notificado',
        'archivado' => 'Archivado'
    ];
    
    protected $fillable = [
        'codigo_expediente',
        'asunto',
        'asunto_documento',
        'asunto_tramite',
        'descripcion',
        'id_tipo_tramite',
        'tipo_documento_entrante',
        'numero_documento_entrante',
        'folios',
        'id_ciudadano',
        'id_persona',
        'fecha_registro',
        'id_estado',
        'prioridad',
        'canal',
        'id_area',
        'id_funcionario_asignado',
        'remitente',
        'dni_remitente',
        'observaciones',
        'observaciones_funcionario',
        'fecha_archivo',
        'fecha_resolucion',
        'estado'
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

    /**
     * Relación: Un expediente tiene un estado (FK a estados_expediente)
     */
    public function estadoExpediente()
    {
        return $this->belongsTo(EstadoExpediente::class, 'id_estado', 'id_estado');
    }

    /**
     * Accessor para compatibilidad: devuelve el slug del estado
     * Permite seguir usando $expediente->estado en lugar de $expediente->estadoExpediente->slug
     */
    public function getEstadoAttribute(): ?string
    {
        return $this->estadoExpediente?->slug;
    }

    /**
     * Mutator: permite asignar estado por slug o por id
     * $expediente->estado = 'derivado' o $expediente->estado = 4
     */
    public function setEstadoAttribute($value): void
    {
        if (is_numeric($value)) {
            $this->attributes['id_estado'] = $value;
        } else {
            $estado = EstadoExpediente::where('slug', $value)->first();
            $this->attributes['id_estado'] = $estado?->id_estado;
        }
    }

    // ===== MÉTODOS AUXILIARES =====
    // Funciones de utilidad para operaciones comunes del expediente
    
    /**
     * Agrega una entrada al historial del expediente
     * @param string $descripcion - Descripción de la acción realizada
     * @param int $usuarioId - ID del usuario que realiza la acción
     * @param array $opciones - Opciones adicionales: estado, accion, id_area, detalle
     * @return \App\Models\HistorialExpediente
     */
    public function agregarHistorial($descripcion, $usuarioId, $opciones = [])
    {
        // Compatibilidad: si $opciones es string, es el estado (formato anterior)
        if (is_string($opciones)) {
            $opciones = ['estado' => $opciones];
        }

        // Crea un nuevo registro en la tabla historial_expedientes
        return $this->historial()->create([
            'descripcion' => $descripcion,
            'id_usuario' => $usuarioId,
            'id_area' => $opciones['id_area'] ?? $this->id_area,
            'accion' => $opciones['accion'] ?? null,
            'detalle' => $opciones['detalle'] ?? null,
            'estado' => $opciones['estado'] ?? $this->estado,
            'fecha' => now()
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
    
    // Método para validar estados (ahora consulta la BD)
    public static function getEstadosValidos()
    {
        return EstadoExpediente::where('activo', true)->pluck('slug')->toArray();
    }

    public function getEstadoFormateado()
    {
        return $this->estadoExpediente?->nombre ?? $this->estado;
    }

    public function esEstadoValido($estado)
    {
        return EstadoExpediente::where('slug', $estado)->where('activo', true)->exists();
    }

    /**
     * Obtiene el color del estado desde la BD
     */
    public function getColorEstadoAttribute(): string
    {
        return $this->estadoExpediente?->color ?? '#6c757d';
    }

    /**
     * Obtiene el badge HTML del estado
     */
    public function getBadgeEstadoAttribute(): string
    {
        return $this->estadoExpediente?->badge_html ?? '<span class="badge bg-secondary">' . $this->estado . '</span>';
    }
    
    // Estado inteligente basado en el proceso real
    // OPTIMIZADO: Usa relaciones cargadas en lugar de consultas adicionales
    public function getEstadoInteligenteAttribute()
    {
        // Si está clasificado = "Clasificado" (listo para derivar)
        if ($this->estado === 'clasificado') {
            return 'clasificado';
        }

        // Si está derivado pero sin funcionario = "Derivado"
        if ($this->estado === 'derivado' && !$this->id_funcionario_asignado) {
            return 'derivado';
        }

        // Si tiene funcionario asignado y está derivado
        if ($this->estado === 'derivado' && $this->id_funcionario_asignado) {
            // OPTIMIZACIÓN: Usar relación ya cargada si existe, evita consulta N+1
            if ($this->relationLoaded('derivaciones') && $this->derivaciones->isNotEmpty()) {
                $ultimaDerivacion = $this->derivaciones->sortByDesc('created_at')->first();
                if ($ultimaDerivacion && !$ultimaDerivacion->fecha_recepcion) {
                    return 'por_recibir';
                }
            }
            return 'asignado';
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
            'devuelto_jefe' => 'Devuelto al Jefe',
            'en_revision' => 'En Revisión',
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
            'devuelto_jefe' => 'warning',  // Naranja - Devuelto al jefe
            'en_revision' => 'info',       // Azul - En revisión del jefe
            'observado' => 'warning',      // Amarillo - Requiere atención
            'resuelto' => 'success',       // Verde - Completado
            'aprobado' => 'success',       // Verde - Aprobado
            'rechazado' => 'danger',       // Rojo - Rechazado
            'archivado' => 'secondary'     // Gris - Finalizado
        ];

        return $colores[$this->estado_inteligente] ?? 'secondary';
    }

    // ===== ACCESSORS PARA PLAZOS =====
    // Eliminan la duplicación de código que se repetía 10+ veces en controladores

    /**
     * Obtener la derivación activa (pendiente más reciente)
     * Usa relación cargada si existe para evitar N+1
     */
    public function getDerivacionActivaAttribute(): ?Derivacion
    {
        if ($this->relationLoaded('derivaciones')) {
            return $this->derivaciones
                ->where('estado', 'pendiente')
                ->sortByDesc('created_at')
                ->first();
        }

        return $this->derivaciones()
            ->where('estado', 'pendiente')
            ->latest()
            ->first();
    }

    /**
     * Obtener fecha límite de la derivación activa
     */
    public function getFechaLimiteDerivacionAttribute(): ?Carbon
    {
        return $this->derivacion_activa?->fecha_limite;
    }

    /**
     * Calcular días vencidos (0 si no está vencido)
     */
    public function getDiasVencidoAttribute(): int
    {
        $fechaLimite = $this->fecha_limite_derivacion;

        if (!$fechaLimite) {
            return 0;
        }

        return $fechaLimite->isPast()
            ? (int) $fechaLimite->diffInDays(now())
            : 0;
    }

    /**
     * Calcular días restantes (null si no hay fecha límite)
     */
    public function getDiasRestantesAttribute(): ?int
    {
        $fechaLimite = $this->fecha_limite_derivacion;

        if (!$fechaLimite) {
            return null;
        }

        return $fechaLimite->isFuture()
            ? (int) now()->diffInDays($fechaLimite)
            : 0;
    }

    /**
     * Verificar si está vencido
     */
    public function getEstaVencidoAttribute(): bool
    {
        return $this->dias_vencido > 0;
    }

    /**
     * Verificar si está próximo a vencer (3 días o menos)
     */
    public function getProximoAVencerAttribute(): bool
    {
        $diasRestantes = $this->dias_restantes;
        return $diasRestantes !== null && $diasRestantes <= 3 && $diasRestantes > 0;
    }

    /**
     * Verificar si es crítico (vencido o próximo a vencer)
     */
    public function getEsCriticoAttribute(): bool
    {
        return $this->esta_vencido || $this->proximo_a_vencer;
    }

    /**
     * Obtener el plazo original de la derivación activa
     */
    public function getPlazoOriginalAttribute(): ?int
    {
        return $this->derivacion_activa?->plazo_dias;
    }
    public function cambiarEstadoPorSlug(string $slug): void
    {
      $estado = \App\Models\EstadoExpediente::where('slug', $slug)->firstOrFail();
      $this->id_estado_expediente = $estado->id_estado_expediente; // ajusta el nombre real del PK
      $this->save();
     }

     public function getEstadoSlugAttribute(): ?string
     {
      return $this->estadoExpediente->slug ?? null;
     }
}