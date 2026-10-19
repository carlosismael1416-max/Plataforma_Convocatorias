<x-filament-panels::page>
@php
    $panel = filament()->getId();
    $estados = [
        'BORRADOR' => ['Borrador', 'borrador'],
        'GENERADA' => ['Generada', 'generada'],
        'EDITANDO' => ['Editando', 'editando'],
        'LISTA' => ['Lista', 'lista'],
        'ENVIADA' => ['Enviada', 'enviada'],
        'APROBADA' => ['Aprobada', 'aprobada'],
        'RECHAZADA' => ['Rechazada', 'rechazada'],
    ];
    $hayFiltros = trim($buscar) !== '' || $estado !== '';
@endphp

<style>
    .prop-page { color: #1A2C4E; }
    .prop-head { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 22px; }
    .prop-head h2 { margin: 0; font-size: 22px; font-weight: 700; }
    .prop-head p { margin: 5px 0 0; color: #64748B; font-size: 13px; }
    .prop-new { display: inline-flex; align-items: center; gap: 6px; border-radius: 10px; background: #1A4B8C; color: #fff; min-height: 42px; padding: 0 18px; font-size: 13px; font-weight: 700; text-decoration: none; box-shadow: 0 4px 12px rgba(26,75,140,.25); }
    .prop-new:hover { background: #153D73; }

    .prop-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .prop-stat { display: flex; gap: 14px; align-items: center; background: #fff; border: 1px solid #DDE3EE; border-radius: 14px; padding: 18px; text-align: left; cursor: pointer; transition: box-shadow .15s, transform .15s, border-color .15s; font: inherit; color: inherit; }
    .prop-stat:hover { box-shadow: 0 8px 20px rgba(15,23,42,.08); transform: translateY(-1px); }
    .prop-stat.activo { border-color: #1A4B8C; box-shadow: 0 0 0 2px rgba(26,75,140,.15); }
    .prop-stat-icono { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .prop-stat-label { color: #64748B; font-size: 12px; }
    .prop-stat-number { margin-top: 2px; font-size: 26px; font-weight: 800; line-height: 1.1; }

    .prop-filtros { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; background: #fff; border: 1px solid #DDE3EE; border-radius: 14px; padding: 12px; margin-bottom: 16px; }
    .prop-filtros input, .prop-filtros select { min-height: 40px; border: 1px solid #DDE3EE; border-radius: 9px; padding: 0 12px; font-size: 13px; background: #F8FAFC; color: #1A2C4E; }
    .prop-filtros input { flex: 1 1 260px; }
    .prop-limpiar { border: 0; background: transparent; color: #1A4B8C; font-size: 13px; font-weight: 600; cursor: pointer; }

    .prop-lista { display: grid; gap: 12px; }
    .prop-item { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1.4fr) auto; gap: 18px; align-items: center; background: #fff; border: 1px solid #DDE3EE; border-left: 4px solid var(--color-estado, #CBD5E1); border-radius: 14px; padding: 16px 18px; cursor: pointer; transition: box-shadow .15s, transform .15s; }
    .prop-item:hover { box-shadow: 0 10px 24px rgba(15,23,42,.08); transform: translateY(-1px); }
    .prop-item:focus-visible { outline: 2px solid #1A4B8C; outline-offset: 2px; }
    .prop-titulo { color: #1A2C4E; font-size: 15px; font-weight: 700; text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .prop-meta { display: flex; flex-wrap: wrap; gap: 6px 12px; margin-top: 7px; color: #64748B; font-size: 12px; align-items: center; }

    .prop-conv { display: flex; gap: 12px; align-items: flex-start; background: #F8FAFC; border: 1px solid #EEF2F7; border-radius: 12px; padding: 11px 13px; min-width: 0; }
    .prop-conv-icono { width: 34px; height: 34px; border-radius: 10px; background: #E8EEF8; color: #1A4B8C; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 16px; }
    .prop-conv-titulo { color: #1A2C4E; font-size: 13px; font-weight: 600; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .prop-conv-sub { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
    .prop-chip { display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; background: #EEF2F7; color: #475569; }
    .prop-chip-rojo { background: #FEE2E2; color: #B91C1C; }
    .prop-chip-ambar { background: #FEF3C7; color: #B45309; }
    .prop-chip-verde { background: #D1FAE5; color: #047857; }

    .status { display: inline-flex; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: .02em; }
    .status-borrador { background: #F1F5F9; color: #475569; }
    .status-generada { background: #DBEAFE; color: #2563EB; }
    .status-editando { background: #EDE9FE; color: #7C3AED; }
    .status-lista { background: #D1FAE5; color: #059669; }
    .status-enviada { background: #FEF3C7; color: #D97706; }
    .status-aprobada { background: #D1FAE5; color: #047857; }
    .status-rechazada { background: #FEE2E2; color: #DC2626; }

    .prop-acciones { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; }
    .prop-btn { display: inline-flex; align-items: center; border: 1px solid #DDE3EE; border-radius: 9px; background: #fff; color: #1A4B8C; padding: 8px 12px; font-size: 12px; font-weight: 600; text-decoration: none; white-space: nowrap; }
    .prop-btn:hover { background: #F0F4FB; }
    .prop-btn-main { background: #1A4B8C; border-color: #1A4B8C; color: #fff; }
    .prop-btn-main:hover { background: #153D73; }

    .prop-empty { background: #fff; border: 1px dashed #CBD5E1; border-radius: 14px; padding: 55px 20px; text-align: center; }
    .prop-empty-icon { width: 56px; height: 56px; margin: 0 auto 13px; border-radius: 16px; background: #E8EEF8; display: flex; align-items: center; justify-content: center; font-size: 26px; }
    .prop-empty h3 { margin: 0 0 5px; font-size: 16px; }
    .prop-empty p { margin: 0; color: #64748B; font-size: 13px; }

    @media (max-width: 1100px) {
        .prop-item { grid-template-columns: 1fr; }
        .prop-acciones { justify-content: flex-start; }
    }
    @media (max-width: 800px) {
        .prop-stats { grid-template-columns: repeat(2, 1fr); }
        .prop-head { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="prop-page">
    <div class="prop-head">
        <div>
            <h2>Mis propuestas</h2>
            <p>Administra y continúa el desarrollo de tus propuestas de proyecto.</p>
        </div>
        <a href="{{ route('filament.' . $panel . '.pages.generar-propuesta') }}" class="prop-new">+ Nueva propuesta</a>
    </div>

    <div class="prop-stats">
        @foreach ([
            ['', 'Total de propuestas', $total, '📄', '#E8EEF8', '#1A4B8C'],
            ['EN_PROCESO', 'En proceso', $enProceso, '✏️', '#EDE9FE', '#7C3AED'],
            ['LISTA', 'Listas para enviar', $listas, '✅', '#D1FAE5', '#059669'],
            ['ENVIADAS', 'Enviadas', $enviadas, '📨', '#FEF3C7', '#D97706'],
        ] as [$filtro, $etiqueta, $valor, $icono, $fondo, $color])
            <button type="button" wire:click="$set('estado', '{{ $filtro }}')" @class(['prop-stat', 'activo' => $estado === $filtro && ($filtro !== '' || ! $hayFiltros)])>
                <span class="prop-stat-icono" style="background: {{ $fondo }}">{{ $icono }}</span>
                <span>
                    <span class="prop-stat-label">{{ $etiqueta }}</span>
                    <span class="prop-stat-number" style="display:block; color: {{ $color }}">{{ $valor }}</span>
                </span>
            </button>
        @endforeach
    </div>

    <div class="prop-filtros">
        <input type="search" wire:model.live.debounce.400ms="buscar" placeholder="Buscar por propuesta, convocatoria u organismo…" aria-label="Buscar propuestas">
        <select wire:model.live="estado" aria-label="Estado">
            <option value="">Todos los estados</option>
            @foreach ($estados as $clave => [$texto])
                <option value="{{ $clave }}">{{ $texto }}</option>
            @endforeach
        </select>
        <select wire:model.live="orden" aria-label="Orden">
            <option value="actualizacion">Última actualización</option>
            <option value="creacion">Más recientes</option>
            <option value="titulo">Título (A-Z)</option>
            <option value="estado">Estado</option>
        </select>
        @if ($hayFiltros)
            <button type="button" class="prop-limpiar" wire:click="limpiarFiltros">Limpiar filtros</button>
        @endif
    </div>

    <div class="prop-lista">
        @forelse ($propuestas as $propuesta)
            @php
                [$textoEstado, $claseEstado] = $estados[$propuesta->estado] ?? [ucfirst(strtolower((string) $propuesta->estado)), 'borrador'];
                $colorEstado = ['borrador' => '#CBD5E1', 'generada' => '#2563EB', 'editando' => '#7C3AED', 'lista' => '#059669', 'enviada' => '#D97706', 'aprobada' => '#047857', 'rechazada' => '#DC2626'][$claseEstado];
                $urlEditor = route('filament.' . $panel . '.pages.editor-propuesta', ['propuesta' => $propuesta->id]);
                $urlVista = route('filament.' . $panel . '.pages.vista-previa-propuesta', ['propuesta' => $propuesta->id]);
                $conv = $propuesta->convocatoria;
                $cierre = $conv?->fecha_cierre;
                $diasCierre = $cierre ? (int) today()->diffInDays($cierre->copy()->startOfDay(), false) : null;
            @endphp

            <div class="prop-item" style="--color-estado: {{ $colorEstado }}"
                 role="link" tabindex="0"
                 x-on:click="if (! $event.target.closest('a, button')) window.location = @js($urlEditor)"
                 x-on:keydown.enter="window.location = @js($urlEditor)">
                <div style="min-width: 0">
                    <a href="{{ $urlEditor }}" class="prop-titulo">{{ $propuesta->titulo ?: 'Propuesta sin título' }}</a>
                    <div class="prop-meta">
                        <span class="status status-{{ $claseEstado }}">{{ $textoEstado }}</span>
                        <span>Versión {{ $propuesta->version ?? 1 }}</span>
                        <span>Creada {{ $propuesta->created_at?->format('d/m/Y') }}</span>
                        <span>Actualizada {{ $propuesta->updated_at?->diffForHumans() }}</span>
                    </div>
                </div>

                @if ($conv)
                    <div class="prop-conv">
                        <span class="prop-conv-icono">🏛</span>
                        <div style="min-width: 0">
                            <div class="prop-conv-titulo" title="{{ $conv->titulo }}">{{ $conv->titulo }}</div>
                            <div class="prop-conv-sub">
                                @if ($conv->organismo)
                                    <span class="prop-chip">{{ \Illuminate\Support\Str::limit($conv->organismo->nombre, 45) }}</span>
                                @endif
                                @if ($cierre === null)
                                    <span class="prop-chip">Sin fecha de cierre</span>
                                @elseif ($diasCierre < 0 || $conv->estado === 'CERRADA')
                                    <span class="prop-chip prop-chip-rojo">Cerrada el {{ $cierre->format('d/m/Y') }}</span>
                                @elseif ($diasCierre <= 7)
                                    <span class="prop-chip prop-chip-ambar">Cierra en {{ $diasCierre }} {{ $diasCierre === 1 ? 'día' : 'días' }}</span>
                                @else
                                    <span class="prop-chip prop-chip-verde">Cierra {{ $cierre->format('d/m/Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="prop-conv"><span class="prop-conv-icono">—</span><div class="prop-conv-sub">Sin convocatoria asociada</div></div>
                @endif

                <div class="prop-acciones">
                    <a href="{{ $urlEditor }}" class="prop-btn prop-btn-main">Continuar</a>
                    <a href="{{ $urlVista }}" class="prop-btn">Vista previa</a>
                    @if ($conv)
                        <button type="button" class="prop-btn" wire:click="verConvocatoria({{ $propuesta->id }})">Convocatoria</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="prop-empty">
                <div class="prop-empty-icon">📄</div>
                @if ($hayFiltros)
                    <h3>No hay propuestas con esos filtros</h3>
                    <p><button type="button" class="prop-limpiar" wire:click="limpiarFiltros">Limpiar filtros</button></p>
                @else
                    <h3>Aún no tienes propuestas</h3>
                    <p>Explora convocatorias y crea tu primera propuesta.</p>
                @endif
            </div>
        @endforelse
    </div>

    <div style="margin-top: 16px">
        {{ $propuestas->links('pagination.itsva') }}
    </div>
</div>
</x-filament-panels::page>
