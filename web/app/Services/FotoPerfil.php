<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Foto de perfil: se recorta al centro, se reduce a 256 × 256 y se guarda
 * como JPEG en el disco privado (storage/app/private/fotos-perfil). Se sirve
 * solo a usuarios con sesión por la ruta `usuarios.foto`.
 */
class FotoPerfil
{
    public const CARPETA = 'fotos-perfil';

    private const LADO = 256;

    public static function guardar(User $usuario, UploadedFile $archivo): void
    {
        $origen = @imagecreatefromstring((string) file_get_contents($archivo->getRealPath()));

        if ($origen === false) {
            throw new RuntimeException('No se pudo leer la imagen.');
        }

        $ancho = imagesx($origen);
        $alto = imagesy($origen);
        $lado = min($ancho, $alto);

        $destino = imagecreatetruecolor(self::LADO, self::LADO);
        imagefill($destino, 0, 0, imagecolorallocate($destino, 255, 255, 255));
        imagecopyresampled($destino, $origen, 0, 0, intdiv($ancho - $lado, 2), intdiv($alto - $lado, 2), self::LADO, self::LADO, $lado, $lado);

        ob_start();
        imagejpeg($destino, null, 85);
        $jpeg = (string) ob_get_clean();
        imagedestroy($origen);
        imagedestroy($destino);

        $ruta = self::CARPETA . '/' . $usuario->id . '-' . Str::random(12) . '.jpg';
        Storage::disk('local')->put($ruta, $jpeg);

        $anterior = $usuario->foto;
        $usuario->foto = $ruta;
        RegistroActividad::sinRegistroAutomatico(fn () => $usuario->save());

        if ($anterior) {
            Storage::disk('local')->delete($anterior);
        }

        RegistroActividad::registrar('PERFIL', 'Cambió su foto de perfil', $usuario);
    }

    public static function quitar(User $usuario): void
    {
        if (! $usuario->foto) {
            return;
        }

        Storage::disk('local')->delete($usuario->foto);
        $usuario->foto = null;
        RegistroActividad::sinRegistroAutomatico(fn () => $usuario->save());

        RegistroActividad::registrar('PERFIL', 'Quitó su foto de perfil', $usuario);
    }
}
