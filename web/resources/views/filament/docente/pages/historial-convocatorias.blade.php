<x-filament-panels::page>
@php
    $hayFiltros = trim($buscar) !== '' || $organismo !== '' || $anio !== '';
@endphp

<style>
    .hist { color: #1A2C4E; }
    .hist-hero { position: relative; overflow: hidden; display: flex; justify-content: space-between; align-items: center; gap: 20px; padding: 26px 28px; margin-bottom: 18px; border-radius: 18px; background: linear-gradient(135deg, #334155 0%, #1E293B 100%); color: #fff; }
    .hist-hero::after { content: ''; position: absolute; right: -40px; top: -60px; width: 220px; height: 220px; border-radius: 50%; background: rgba(255,255,255,.06); }
    .hist-hero h2 { margin: 0; font-size: 22px; font-weight: 800; }
    .hist-hero p { margin: 6px 0 0; color: #CBD5E1; font-size: 13px; max-width: 620px; line-height: 1.5; }
    .hist-total { position: relative; z-index: 1; text-align: center; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15); border-radius: 14px; padding: 12px 20px; }
    .hist-total strong { display: block; font-size: 28px; line-height: 1; }
    .hist-total span { font-size: 11px; color: #CBD5E1; }

    .hist-filtros { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; background: #fff; border: 1px solid #DDE3EE; border-radius: 14px; padding: 12px; margin-bottom: 16px; }
    .hist-filtros input, .hist-filtros select { min-height: 40px; border: 1px solid #DDE3EE; border-radius: 9px; padding: 0 12px; font-size: 13px; background: #F8FAFC; color: #1A2C4E; }
    .hist-filtros input { flex: 1 1 260px; }
    .hist-filtros select { max-width: 260px; }
    .hist-limpiar { border: 0; background: transparent; color: #1A4B8C; font-size: 13px; font-weight: 600; cursor: pointer; }

    .hist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px; }
    .hist-card { position: relative; display: flex; flex-direction: column; gap: 10px; background: #fff; border: 1px solid #DDE3EE; border-radius: 14px; padding: 18px; cursor: pointer; transition: box-shadow .15s, transform .15s, border-color .15s; }
    .hist-card:hover { box-shadow: 0 10px 24px rgba(15,23,42,.08); transform: translateY(-1px); border-color: #CBD5E1; }
    .hist-cinta { display: inline-flex; width: fit-content; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: #F1F5F9; color: #475569; font-size: 11px; font-weight: 700; }
    .hist-titulo { color: #1A2C4E; font-size: 15px; font-weight: 700; line-height: 1.35; text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .hist-org { color: #64748B; font-size: 12px; }
    .hist-desc { color: #64748B; font-size: 12px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .hist-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: auto; }
    .hist-chip { padding: 3px 9px; border-radius: 999px; background: #EEF2F7; color: #475569; font-size: 11px; font-weight: 600; }
    .hist-chip-morado { background: #EDE9FE; color: #6D28D9; }
    .hist-chip-verde { background: #D1FAE5; color: #047857; }

    .hist-vacio { grid-column: 1 / -1; background: #fff; border: 1px dashed #CBD5E1; border-radius: 14px; padding: 50px 20px; text-align: center; color: #64748B; font-size: 13px; }
    .hist-vacio h3 { margin: 0 0 6px; color: #1A2C4E; font-size: 16px; }

    @media (max-width: 700px) {
        .hist-hero { flex-direction: column; align-items: flex-start; }
        .hist-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="hist">
    <section class="hist-hero">
        <div style="position: relative; z-index: 1">
            <h2>Historial de convocatorias</h2>
            <p>Convocatorias que ya cerraron. Sirven de referencia para saber qué se ha publicado, qué montos y requisitos se pidieron y prepararse para la próxima edición.</p>
        </div>
        <div class="hist-total"><strong>{{ $total }}</strong><span>cerradas</span></div>
    </section>

    <div class="hist-filtros">
        <input type="search" wire:model.live.debounce.400ms="buscar" placeholder="Buscar por título, descripción u organismo…" aria-label="Buscar en el historial">
        <select wire:model.live="organismo" aria-label="Organismo">
            <option value="">Todos los organismos</option>
            @foreach ($organismos as $o)
                <option value="{{ $o->id }}">{{ $o->nombre }}</option>
            @endforeach
        </select>
        <select wire:model.live="anio" aria-label="Año de cierre">
            <option value="">Cualquier año</option>
            @foreach ($anios as $a)
                <option value="{{ $a }}">{{ $a }}</option>
            @endforeach
        </select>
        <select wire:model.live="orden" aria-label="Orden">
            <option value="recientes">Cerradas recientemente</option>
            <option value="antiguas">Más antiguas</option>
            <option value="titulo">Título (A-Z)</option>
        </select>
        @if ($hayFiltros)
            <button type="button" class="hist-limpiar" wire:click="limpiarFiltros">Limpiar filtros</button>
        @endif
    </div>

    <div class="hist-grid">
        @forelse ($convocatorias as $c)
            @php $url = $urlDetalle($c); @endphp
            <article class="hist-card" wire:key="hist-{{ $c->id }}"
                x-on:click="if (! $event.target.closest('a, button')) window.location = @js($url)">
                <span class="hist-cinta">🔒 Cerró {{ $c->fecha_cierre?->locale('es')->isoFormat('D MMM YYYY') ?? '(sin fecha)' }}</span>
                <a href="{{ $url }}" class="hist-titulo" title="{{ $c->titulo }}">{{ $c->titulo }}</a>
                @if ($c->organismo)
                    <div class="hist-org">{{ $c->organismo->nombre }}</div>
                @endif
                @if ($c->descripcion)
                    <div class="hist-desc">{{ \Illuminate\Support\Str::limit(strip_tags($c->descripcion), 220) }}</div>
                @endif
                <div class="hist-chips">
                    @if ($c->categoria)
                        <span class="hist-chip">{{ $c->categoria->nombre }}</span>
                    @endif
                    @if ($c->monto_maximo !== null)
                        <span class="hist-chip hist-chip-morado">${{ number_format((float) $c->monto_maximo, 0) }} {{ $c->moneda ?? 'MXN' }}</span>
                    @endif
                    @if (in_array((int) $c->id, $conPropuesta, true))
                        <span class="hist-chip hist-chip-verde">Tuviste propuesta</span>
                    @endif
                </div>
            </article>
        @empty
            <div class="hist-vacio">
                @if ($hayFiltros)
                    <h3>Sin resultados</h3>
                    <p>No hay convocatorias cerradas con esos filtros. <button type="button" class="hist-limpiar" wire:click="limpiarFiltros">Limpiar filtros</button></p>
                @else
                    <h3>Aún no hay convocatorias cerradas</h3>
                    <p>Cuando una convocatoria publicada cierre, aparecerá aquí.</p>
                @endif
            </div>
        @endforelse
    </div>

    <div style="margin-top: 16px">
        {{ $convocatorias->links('pagination.itsva') }}
    </div>
</div>
</x-filament-panels::page>
