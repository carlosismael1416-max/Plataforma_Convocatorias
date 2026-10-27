<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Actividad extends Model
{
    protected $table = 'actividades';

    protected $fillable = ['user_id', 'accion', 'descripcion', 'sujeto_tipo', 'sujeto_id', 'ip'];

    /** Etiqueta e icono de cada acción. */
    public const ACCIONES = [
        'INICIO_SESION' => ['Inició sesión', '🔐'],
        'CIERRE_SESION' => ['Cerró sesión', '🚪'],
        'CONTRASENA' => ['Contraseña', '🔑'],
        'PERFIL' => ['Perfil', '👤'],
        'PROPUESTA' => ['Propuesta', '📄'],
        'PROPUESTA_ENVIADA' => ['Propuesta enviada', '📨'],
        'DICTAMEN' => ['Dictamen', '⚖️'],
        'CONVOCATORIA' => ['Convocatoria', '📚'],
        'MIS_CONVOCATORIAS' => ['Mis convocatorias', '⭐'],
        'USUARIO' => ['Usuarios', '👥'],
        'ROL' => ['Roles', '🛡️'],
        'FUENTE' => ['Fuentes', '🌐'],
        'DEPARTAMENTO' => ['Departamentos', '🏢'],
        'MOTOR' => ['Motor', '⚙️'],
        'REPORTE' => ['Reporte', '📊'],
        'BITACORA' => ['Bitácora', '🧾'],
        'CATALOGO' => ['Catálogos', '🗂️'],
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function infoAccion(): array
    {
        return self::ACCIONES[$this->accion] ?? [ucfirst(mb_strtolower(str_replace('_', ' ', $this->accion))), '•'];
    }
}
