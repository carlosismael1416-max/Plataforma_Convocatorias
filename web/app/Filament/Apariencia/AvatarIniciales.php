<?php

namespace App\Filament\Apariencia;

use App\Models\User;
use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Database\Eloquent\Model;

/**
 * Avatar con las iniciales del usuario y el color de su rol, generado
 * localmente. Sustituye al proveedor por defecto (ui-avatars.com), que
 * recibía el nombre de cada usuario en cada carga de página.
 */
class AvatarIniciales implements AvatarProvider
{
    private const COLORES = [
        'DOCENTE' => '#3B82F6',
        'DIRECTIVO' => '#7C3AED',
        'ADMINISTRADOR' => '#059669',
    ];

    public function get(Model $record): string
    {
        $nombre = trim(($record->name ?? '') . ' ' . ($record->apellidos ?? ''));

        $iniciales = collect(preg_split('/\s+/', $nombre ?: 'U'))
            ->filter()
            ->take(2)
            ->map(fn (string $parte): string => mb_strtoupper(mb_substr($parte, 0, 1)))
            ->implode('');

        $color = $record instanceof User
            ? (self::COLORES[$record->role?->nombre] ?? '#1A4B8C')
            : '#1A4B8C';

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64">'
            . '<rect width="64" height="64" fill="' . $color . '"/>'
            . '<text x="50%" y="50%" dy=".35em" text-anchor="middle" fill="#fff" '
            . 'font-family="DM Sans, Arial, sans-serif" font-size="26" font-weight="700">'
            . htmlspecialchars($iniciales, ENT_XML1)
            . '</text></svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
