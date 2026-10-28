<x-filament-panels::page>
    <style>
        .gp { --text: #0F1827; --muted: #64748B; --border: #DDE3EE; color: var(--text); }
        .gp-cab { margin-bottom: 18px; }
        .gp-cab h2 { margin: 0; font: 800 24px 'DM Sans', sans-serif; }
        .gp-cab p { margin: 6px 0 0; max-width: 860px; color: var(--muted); font-size: 14px; line-height: 1.5; }

        /* Tarjetas que filtran la tabla (mismo patrón que Convocatorias). */
        .gp-stats { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
        .gp-stat { display: block; width: 100%; padding: 16px; border: 1px solid var(--border); border-radius: 14px; background: #fff; color: inherit; font: inherit; text-align: left; cursor: pointer; box-shadow: 0 1px 2px rgba(15, 24, 39, .03); transition: box-shadow .15s, transform .15s, border-color .15s; }
        .gp-stat:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(15, 24, 39, .08); }
        .gp-stat.activa { border-color: #059669; box-shadow: 0 0 0 2px rgba(5, 150, 105, .18); }
        .gp-stat-icono { display: inline-flex; align-items: center; justify-content: center; min-width: 46px; height: 30px; padding: 0 8px; margin-bottom: 10px; border-radius: 9px; font-size: 12px; font-weight: 900; }
        .gp-verde { background: #D1FAE5; color: #047857; }
        .gp-azul { background: #DBEAFE; color: #1D4ED8; }
        .gp-ambar { background: #FEF3C7; color: #B45309; }
        .gp-gris { background: #F1F5F9; color: #475569; }
        .gp-stat-valor { font-size: 22px; font-weight: 900; }
        .gp-stat-nombre { margin-top: 2px; color: #334155; font-size: 12px; font-weight: 800; line-height: 1.3; }
        .gp-stat-ayuda { margin-top: 4px; color: #94A3B8; font-size: 11px; }
        .gp-oculto { margin-top: 4px; color: #B91C1C; font-size: 11px; font-weight: 700; }

        .gp-tabla { min-width: 0; overflow: hidden; padding: 20px; border: 1px solid var(--border); border-radius: 16px; background: #fff; }
        .gp-tabla-cab { margin-bottom: 16px; }
        .gp-tabla-titulo { font-size: 15px; font-weight: 850; }
        .gp-tabla-ayuda { margin-top: 4px; color: var(--muted); font-size: 12px; line-height: 1.5; }
        /* Encabezado fijo; la tabla se desplaza por dentro. */
        .gp-tabla .fi-ta-content { position: relative; max-height: 68vh; overflow: auto !important; scrollbar-gutter: stable; overscroll-behavior: contain; }
        .gp-tabla .fi-ta-content thead th { position: sticky; top: 0; z-index: 8; background: #fff; }

        @media (max-width: 1200px) { .gp-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 640px) { .gp-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } .gp-tabla { padding: 14px; } }
    </style>

    <div class="gp">
        <div class="gp-cab">
            <h2>Gestión de propuestas</h2>
            <p>Configura las opciones que usan los docentes al preparar sus propuestas.</p>
        </div>

        <div class="gp-stats">
            <button type="button" wire:click="verCapitulo('')" @class(['gp-stat', 'activa' => $this->capitulo === '']) aria-pressed="{{ $this->capitulo === '' ? 'true' : 'false' }}">
                <span class="gp-stat-icono gp-verde">Todos</span>
                <div class="gp-stat-valor">{{ number_format($totalElegibles) }}</div>
                <div class="gp-stat-nombre">Partidas que elige el docente</div>
                <div class="gp-stat-ayuda">{{ number_format($totalPartidas) }} en el clasificador · {{ $enUso }} en uso</div>
            </button>

            @foreach ($capitulos as $c)
                <button type="button" wire:click="verCapitulo('{{ $c['digito'] }}')" @class(['gp-stat', 'activa' => $this->capitulo === $c['digito']]) aria-pressed="{{ $this->capitulo === $c['digito'] ? 'true' : 'false' }}" title="{{ $c['clave'] }} · {{ $c['nombre'] }}">
                    <span @class(['gp-stat-icono', 'gp-ambar' => $c['tipo'] === 'Gasto de capital', 'gp-azul' => $c['tipo'] !== 'Gasto de capital'])>{{ $c['clave'] }}</span>
                    <div class="gp-stat-valor">{{ number_format($c['elegibles']) }}</div>
                    <div class="gp-stat-nombre">{{ $c['nombre'] }}</div>
                    <div class="gp-stat-ayuda">{{ $c['conceptos'] }} conceptos · {{ $c['tipo'] }}</div>
                    @unless ($c['activa'])
                        <div class="gp-oculto">No se ofrece</div>
                    @endunless
                </button>
            @endforeach
        </div>

        <section class="gp-tabla">
            <div class="gp-tabla-cab">
                <div class="gp-tabla-titulo">
                    Clasificador por objeto del gasto{{ $this->capitulo !== '' ? ' · ' . ($capitulos->firstWhere('digito', $this->capitulo)['clave'] ?? '') . ' ' . ($capitulos->firstWhere('digito', $this->capitulo)['nombre'] ?? '') : ' · capítulos 1000 a 6000' }}
                </div>
                <div class="gp-tabla-ayuda">
                    Capítulo, concepto, partida genérica y específica (CONAC). El docente elige la partida específica, o la genérica cuando no tiene específicas, y ve su capítulo, concepto, definición y tipo de gasto. Lo que ya usa algún concepto no se elimina: deja de ofrecerse y los conceptos lo conservan. Pasa el cursor sobre una definición recortada para leerla completa.
                </div>
            </div>

            {{ $this->table }}
        </section>
    </div>
</x-filament-panels::page>
