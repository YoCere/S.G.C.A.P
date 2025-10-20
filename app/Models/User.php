<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'activo', // ✅ AGREGADO
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'activo' => 'boolean', // ✅ AGREGADO
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    // ✅ SCOPES PARA FILTRAR USUARIOS
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeInactivos($query)
    {
        return $query->where('activo', false);
    }

    // ✅ RELACIÓN CON PAGOS (SI EXISTE)
    public function pagos()
    {
        return $this->hasMany(\App\Models\Pago::class, 'registrado_por');
    }

    // ✅ MÉTODO PARA VERIFICAR SI PUEDE SER DESACTIVADO
    public function puedeDesactivar()
    {
        // No permitir auto-desactivación
        if ($this->id === auth()->id()) {
            return false;
        }

        // Verificar si tiene registros de pagos
        if ($this->pagos()->exists()) {
            return false;
        }

        return true;
    }

    // ✅ MÉTODO PARA OBTENER ESTADO LEGIBLE
    public function getEstadoAttribute()
    {
        return $this->activo ? 'Activo' : 'Inactivo';
    }

    // ✅ MÉTODO PARA OBTENER COLOR DEL BADGE
    public function getEstadoColorAttribute()
    {
        return $this->activo ? 'success' : 'warning';
    }
}