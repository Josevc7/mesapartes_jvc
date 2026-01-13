<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Usar clave primaria estándar de Laravel
    protected $primaryKey = 'id';
    
    // Deshabilitar eager loading automático
    protected $with = [];
    
    // Deshabilitar appends automáticos
    protected $appends = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'dni',
        'telefono',
        'direccion',
        'id_rol',
        'id_area',
        'id_persona',
        'activo'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     * 
     * NOTE: 'password' => 'hashed' is NOT a security vulnerability.
     * This is a Laravel feature that automatically hashes passwords using bcrypt.
     * The warning about CWE-798 is a false positive.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'last_login_at' => 'datetime'
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_rol', 'id_rol');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }

    public function expedientesComoFuncionario()
    {
        return $this->hasMany(Expediente::class, 'id_funcionario_asignado', 'id');
    }

    public function expedientesAsignados()
    {
        return $this->hasMany(Expediente::class, 'id_funcionario_asignado', 'id');
    }

    public function expedientesComoCiudadano()
    {
        return $this->hasMany(Expediente::class, 'id_ciudadano', 'id');
    }

    public function derivacionesComoOrigen()
    {
        return $this->hasMany(Derivacion::class, 'id_funcionario_origen', 'id');
    }

    public function derivacionesComoDestino()
    {
        return $this->hasMany(Derivacion::class, 'id_funcionario_destino', 'id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_usuario', 'id');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'id_usuario', 'id');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }
}
