<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'apellidos',
        'email',
        'password',
        'role_id',
        'departamento_id',
        'estado',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'estado' => 'boolean',
            'ultimo_acceso' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function tieneRol(string $rol): bool
    {
        return $this->role?->nombre === $rol;
    }

    public function tienePermiso(string $permiso): bool
    {
        if (! $this->role) {
            return false;
        }

        return $this->role
            ->permisos()
            ->where('nombre', $permiso)
            ->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->estado) {
            return false;
        }

        return match ($panel->getId()) {
            'admin' => $this->tieneRol('ADMINISTRADOR'),
            'directivo' => $this->tieneRol('DIRECTIVO'),
            'docente' => $this->tieneRol('DOCENTE'),
            default => false,
        };
    }
	public function convocatoriasGuardadas(): HasMany
{
    return $this->hasMany(
        UsuarioConvocatoria::class,
        'user_id'
    );
}
public function propuestas(): HasMany
{
    return $this->hasMany(Propuesta::class);
}

    public function convocatoriasSubidas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            Convocatoria::class,
            'subido_por_user_id'
        );
    }
}
