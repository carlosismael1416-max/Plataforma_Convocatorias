
<div style="display: grid; gap: 1.25rem;">
    <div>
        <strong>Importe máximo</strong>
        <p style="font-size: 1.5rem; font-weight: bold;">
            {{ number_format((float) $apoyo->monto_maximo, 2) }}
            {{ $apoyo->moneda }}
        </p>
        <p>
            {{ $apoyo->componente === 'MEXICANO' ? 'México' : 'Francia' }}
            ·
            {{ match ($apoyo->unidad) {
                'PROYECTO_TOTAL' => 'Proyecto completo',
                'ETAPA_ANUAL' => 'Etapa anual',
                'PASAJE' => 'Pasaje',
                'DIA' => 'Por día',
                default => $apoyo->unidad,
            } }}
        </p>
    </div>

    <div>
        <strong>Incluido en</strong>
        <p>
            @if ($apoyo->incluido_en_clave === 'APOYO_MAXIMO_PROYECTO')
                Apoyo máximo del proyecto; no debe sumarse nuevamente.
            @elseif ($apoyo->incluido_en_clave)
                {{ str_replace('_', ' ', $apoyo->incluido_en_clave) }}
            @else
                No
            @endif
        </p>
    </div>

    <div>
        <strong>Condiciones</strong>
        <ul style="padding-left: 1.5rem; list-style: disc;">
            @foreach (explode('; ', $condicionesTexto) as $condicion)
                <li>{{ $condicion }}</li>
            @endforeach
        </ul>
    </div>

    <div>
        <strong>Documentos de referencia</strong>

        @forelse ($apoyo->fuentes_documentales ?? [] as $fuente)
            <div style="margin-top: 0.75rem;">
                @php
                    $url = $fuente['url_pdf'] ?? null;
                    $urlValida = is_string($url)
                        && (
                            str_starts_with($url, 'https://')
                            || str_starts_with($url, 'http://')
                        );
                @endphp

                @if ($urlValida)
                    <a
                        href="{{ $url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        style="color: #2563eb; text-decoration: underline;"
                    >
                        {{ $fuente['documento'] ?? 'Documento PDF' }}
                        (página {{ $fuente['pagina'] ?? '?' }})
                    </a>
                @else
                    <p>Documento sin enlace disponible.</p>
                @endif

                @if (!empty($fuente['fragmento']))
                    <details style="margin-top: 0.5rem;">
                        <summary>Ver fragmento original</summary>
                        <p style="margin-top: 0.5rem;">
                            {{ $fuente['fragmento'] }}
                        </p>
                    </details>
                @endif
            </div>
        @empty
            <p>No hay documentos registrados.</p>
        @endforelse
    </div>

    <div>
        <strong>Estado de revisión</strong>
        <p>{{ $apoyo->estado_documental }}</p>
    </div>
</div>
