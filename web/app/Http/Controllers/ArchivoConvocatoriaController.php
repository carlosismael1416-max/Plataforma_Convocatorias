<?php

namespace App\Http\Controllers;

use App\Models\ConvocatoriaArchivo;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Archivo subido con «Subir convocatoria desde un archivo» (PDF o imagen).
 * Solo con sesión; los documentos del motor siguen siendo enlaces externos.
 */
class ArchivoConvocatoriaController extends Controller
{
    public function __invoke(ConvocatoriaArchivo $archivo): Response
    {
        abort_unless($archivo->ruta_archivo && Storage::disk('local')->exists($archivo->ruta_archivo), 404);

        $nombre = preg_replace('/[^\pL\pN ._-]/u', '', (string) $archivo->nombre) ?: 'convocatoria';

        return Storage::disk('local')->response($archivo->ruta_archivo, $nombre, [
            'Content-Type' => $archivo->mime_type ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }
}
