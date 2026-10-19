<x-filament-panels::page>
    <style>
        .ppr { display: flex; flex-direction: column; gap: 24px; color: #1A2C4E; }
        .ppr-cabecera h2 { margin: 0; font: 700 22px 'DM Sans', sans-serif; }
        .ppr-cabecera p { margin: 4px 0 0; color: #64748B; font-size: 14px; }
        .ppr-card { overflow: hidden; border: 1px solid #DDE3EE; border-radius: 12px; background: #fff; }
        .ppr-card-titulo { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid #F0F4FB; }
        .ppr-card-titulo h3 { margin: 0; font: 700 15px 'DM Sans', sans-serif; }
        .ppr-contador { padding: 2px 10px; border-radius: 999px; background: #EDE9FE; color: #7C3AED; font: 700 12px 'DM Sans', sans-serif; }
        .ppr-tabla { width: 100%; border-collapse: collapse; font-size: 13px; }
        .ppr-tabla th { padding: 10px 20px; background: #F8FAFC; color: #64748B; font: 600 11px 'DM Sans', sans-serif; letter-spacing: .05em; text-align: left; text-transform: uppercase; }
        .ppr-tabla td { padding: 14px 20px; border-top: 1px solid #F0F4FB; vertical-align: middle; }
        .ppr-titulo { font-weight: 600; }
        .ppr-sub { margin-top: 2px; color: #94A3B8; font-size: 12px; }
        .ppr-chip { display: inline-block; padding: 2px 8px; border-radius: 999px; font: 600 11px 'DM Sans', sans-serif; }
        .ppr-docente { background: #DBEAFE; color: #1D4ED8; }
        .ppr-directivo { background: #EDE9FE; color: #7C3AED; }
        .ppr-propia { background: #FEF3C7; color: #B45309; }
        .ppr-aprobada { background: #D1FAE5; color: #047857; }
        .ppr-rechazada { background: #FEE2E2; color: #B91C1C; }
        .ppr-btn { display: inline-flex; padding: 7px 14px; border-radius: 8px; background: #1A4B8C; color: #fff; font: 600 13px 'DM Sans', sans-serif; text-decoration: none; white-space: nowrap; }
        .ppr-btn:hover { background: #163F78; }
        .ppr-btn-sec { background: #E8EEF8; color: #1A4B8C; }
        .ppr-btn-sec:hover { background: #DCE5F3; }
        .ppr-vacio { padding: 40px 20px; color: #64748B; font-size: 14px; text-align: center; }
        @media (max-width: 900px) { .ppr-ocultar { display: none; } }
    </style>

    <div class="ppr">
        <div class="ppr-cabecera">
            <h2>Propuestas por revisar</h2>
            <p>Propuestas enviadas por docentes y directivos, incluidas las tuyas. Aprueba, devuelve con observaciones o rechaza.</p>
        </div>

        <section class="ppr-card">
            <div class="ppr-card-titulo">
                <h3>Pendientes</h3>
                <span class="ppr-contador">{{ count($pendientes) }}</span>
            </div>

            @if ($pendientes === [])
                <div class="ppr-vacio">No hay propuestas pendientes de revisión.</div>
            @else
                <table class="ppr-tabla">
                    <thead>
                        <tr>
                            <th>Propuesta</th>
                            <th>Autor</th>
                            <th class="ppr-ocultar">Enviada</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendientes as $p)
                            <tr data-href="{{ $p['url'] }}" tabindex="0">
                                <td>
                                    <div class="ppr-titulo">{{ $p['titulo'] }}</div>
                                    <div class="ppr-sub">{{ $p['convocatoria'] }}</div>
                                </td>
                                <td>
                                    {{ $p['autor'] }}
                                    <div style="margin-top: 4px;">
                                        <span class="ppr-chip {{ $p['rol'] === 'Directivo' ? 'ppr-directivo' : 'ppr-docente' }}">{{ $p['rol'] }}</span>
                                        @if ($p['propia'])
                                            <span class="ppr-chip ppr-propia">Tuya</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="ppr-ocultar">{{ $p['fecha'] }}</td>
                                <td style="text-align: right;">
                                    <a class="ppr-btn" href="{{ $p['url'] }}">Revisar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="ppr-card">
            <div class="ppr-card-titulo">
                <h3>Dictámenes recientes</h3>
            </div>

            @if ($dictaminadas === [])
                <div class="ppr-vacio">Todavía no hay propuestas dictaminadas.</div>
            @else
                <table class="ppr-tabla">
                    <thead>
                        <tr>
                            <th>Propuesta</th>
                            <th>Autor</th>
                            <th>Dictamen</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dictaminadas as $p)
                            <tr data-href="{{ $p['url'] }}" tabindex="0">
                                <td>
                                    <div class="ppr-titulo">{{ $p['titulo'] }}</div>
                                    <div class="ppr-sub">{{ $p['convocatoria'] }}</div>
                                </td>
                                <td>{{ $p['autor'] }}</td>
                                <td>
                                    <span class="ppr-chip {{ $p['estado'] === 'APROBADA' ? 'ppr-aprobada' : 'ppr-rechazada' }}">
                                        {{ $p['estado'] === 'APROBADA' ? 'Aprobada' : 'Rechazada' }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a class="ppr-btn ppr-btn-sec" href="{{ $p['url'] }}">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    </div>
</x-filament-panels::page>
