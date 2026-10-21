<?php

namespace App\Filament\Concerns;

use App\Models\Convocatoria;
use Filament\Notifications\Notification;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * «Subir convocatoria» → «Desde un enlace»: el usuario pega la URL, el
 * extractor hace el mismo proceso que el motor (descarga, extrae, clasifica
 * y valida, sin guardar) y el formulario se llena con lo encontrado. Si no
 * cumple los criterios del validador, se le avisa y decide: publicar de
 * todas maneras o no publicar. Si el enlace ya existe publicado, no se
 * duplica; si existe como pendiente interna del motor, se publica esa.
 */
trait ImportaDesdeEnlace
{
    public string $enlace = '';

    /** Respuesta del extractor (datos, validacion, existente…). */
    public ?array $analisis = null;

    /** El usuario aceptó publicar aunque no cumpla los criterios. */
    public bool $publicarIgual = false;

    /** Sección activa: 'automatica' (desde un enlace) o 'manual'. */
    public string $modo = 'automatica';

    public function mountImportaDesdeEnlace(): void
    {
        if (request()->query('modo') === 'manual') {
            $this->modo = 'manual';
        }
    }

    public function cambiarModo(string $modo): void
    {
        $this->modo = $modo === 'manual' ? 'manual' : 'automatica';

        // A mano no hay criterios automáticos que confirmar; lo ya llenado
        // desde un enlace se conserva para terminarlo a mano.
        if ($this->modo === 'manual') {
            $this->reset(['analisis', 'publicarIgual']);
        }
    }

    /** En automática, el formulario aparece cuando ya se analizó un enlace. */
    public function mostrarFormulario(): bool
    {
        return $this->modo === 'manual' || $this->analisis !== null;
    }

    protected function getFormActions(): array
    {
        return $this->mostrarFormulario() ? parent::getFormActions() : [];
    }

    public function analizarEnlace(): void
    {
        abort_unless(static::canAccess(), 403);

        $enlace = trim($this->enlace);

        if (! filter_var($enlace, FILTER_VALIDATE_URL) || ! preg_match('#^https?://#i', $enlace)) {
            throw ValidationException::withMessages([
                'enlace' => 'Escribe un enlace completo que empiece con http:// o https://.',
            ]);
        }

        try {
            $respuesta = Http::acceptJson()
                ->timeout(120)
                ->post(rtrim((string) config('services.extractor.url'), '/') . '/convocatorias/analizar-url', [
                    'url' => $enlace,
                ]);
        } catch (ConnectionException) {
            $this->avisar('El motor de extracción no responde', 'Usa la subida manual o inténtalo más tarde.');

            return;
        }

        if (! $respuesta->successful()) {
            $this->avisar(
                match ($respuesta->status()) {
                    409 => 'El motor de extracción está apagado',
                    422 => 'No se pudo usar ese enlace',
                    429 => 'Hay otro enlace en análisis',
                    default => 'No se pudo extraer la convocatoria',
                },
                (string) ($respuesta->json('detail') ?? 'Usa la subida manual.')
            );

            return;
        }

        $this->analisis = $respuesta->json();
        $this->publicarIgual = false;

        $datos = $this->analisis['datos'] ?? [];

        $this->form->fill(array_filter([
            'titulo' => $datos['titulo'] ?? null,
            'url_original' => $datos['url_original'] ?? $enlace,
            'descripcion' => $datos['descripcion'] ?? null,
            'objetivo' => $datos['objetivo'] ?? null,
            'fecha_publicacion' => $datos['fecha_publicacion'] ?? null,
            'fecha_inicio' => $datos['fecha_inicio'] ?? null,
            'fecha_cierre' => $datos['fecha_cierre'] ?? null,
            'monto_minimo' => $datos['monto_minimo'] ?? null,
            'monto_maximo' => $datos['monto_maximo'] ?? null,
            'moneda' => $datos['moneda'] ?? 'MXN',
            'organismo_id' => $datos['organismo_id'] ?? null,
            'categoria_id' => $datos['categoria_id'] ?? null,
            'tipo' => isset(Convocatoria::TIPOS[$datos['tipo'] ?? '']) ? $datos['tipo'] : 'FINANCIAMIENTO',
            'dirigido_a' => $datos['dirigido_a'] ?? [],
        ], fn ($valor) => $valor !== null));

        Notification::make()
            ->title(($this->analisis['validacion']['publicable'] ?? false)
                ? 'Convocatoria lista para publicar'
                : 'Revisa la convocatoria antes de publicar')
            ->body('Llenamos el formulario con lo que encontramos. Revisa y corrige lo que haga falta.')
            ->success()
            ->send();
    }

    public function publicarDeTodasManeras(): void
    {
        $this->publicarIgual = true;
    }

    /** «No publicar»: se descarta lo analizado y el formulario queda vacío. */
    public function descartarAnalisis(): void
    {
        $this->reset(['enlace', 'analisis', 'publicarIgual']);
        $this->form->fill();
    }

    /**
     * Convocatoria que ya tiene ese enlace (por url_hash, calculado en el
     * servidor; no se confía en lo que devuelva el navegador).
     */
    protected function convocatoriaConMismoEnlace(?string $url): ?Convocatoria
    {
        if (blank($url)) {
            return null;
        }

        return Convocatoria::query()->where('url_hash', Convocatoria::hashUrl($url))->first();
    }

    /**
     * Reglas antes de guardar (también para la subida manual): no duplicar
     * una convocatoria que ya está en la plataforma y no publicar algo que
     * no cumple los criterios sin la confirmación del usuario. Devuelve la
     * pendiente interna del motor que se debe publicar en lugar de crear
     * otra, si existe.
     */
    protected function validarImportacion(array $data): ?Convocatoria
    {
        if (! $this->mostrarFormulario()) {
            throw ValidationException::withMessages([
                'enlace' => 'Analiza un enlace primero o cambia a la sección «Manual».',
            ]);
        }

        $existente = $this->convocatoriaConMismoEnlace($data['url_original'] ?? null);

        if ($existente && $existente->estado === 'DESCARTADA') {
            throw ValidationException::withMessages([
                'data.url_original' => 'Un administrador ya revisó este enlace y decidió no publicarlo. Si crees que es un error, pídele que lo revise de nuevo.',
            ]);
        }

        if ($existente && $existente->estado !== 'PENDIENTE_REVISION') {
            throw ValidationException::withMessages([
                'data.url_original' => 'Esta convocatoria ya está en la plataforma (enlace repetido). Búscala en el buscador o en el historial.',
            ]);
        }

        if (
            $this->analisis !== null
            && ! ($this->analisis['validacion']['publicable'] ?? false)
            && ! $this->publicarIgual
        ) {
            throw ValidationException::withMessages([
                'enlace' => 'La convocatoria no cumple los criterios automáticos. Elige «Publicar de todas maneras» o «No publicar».',
            ]);
        }

        return $existente;
    }

    private function avisar(string $titulo, string $cuerpo): void
    {
        Notification::make()->title($titulo)->body($cuerpo)->warning()->persistent()->send();
    }
}
