{{--
    Actividad reciente del usuario en sesión: sus 3 acciones más recientes de
    la tabla `actividades`. La lista se va recorriendo sola conforme registra
    acciones nuevas. Uso: @include('filament.partials.actividad-reciente', ['historialUrl' => null])
--}}
@php
    $recientes = \App\Models\Actividad::query()
        ->where('user_id', auth()->id())
        ->latest('updated_at')
        ->latest('id')
        ->limit(3)
        ->get();
    $historialUrl ??= null;
@endphp

<style>
    .ar-tarjeta { padding: 22px 24px; border: 1px solid #DDE3EE; border-radius: 18px; background: #fff; }
    .ar-cab { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
    .ar-titulo { font: 800 17px 'DM Sans', sans-serif; color: #0F1827; }
    .ar-ayuda { margin-top: 3px; color: #64748B; font-size: 13px; }
    .ar-vertodo { flex-shrink: 0; color: #047857; font-size: 13px; font-weight: 700; text-decoration: none; }
    .ar-vertodo:hover { text-decoration: underline; }
    .ar-lista { display: grid; gap: 10px; }
    .ar-fila { display: grid; grid-template-columns: 38px minmax(0, 1fr) auto; gap: 12px; align-items: center; padding: 12px 14px; border: 1px solid #EEF2F7; border-radius: 12px; background: #F8FAFC; }
    .ar-icono { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #E2E8F0; font-size: 17px; }
    .ar-accion { color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .ar-desc { color: #0F1827; font-size: 14px; font-weight: 600; line-height: 1.35; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ar-fecha { color: #64748B; font-size: 12px; text-align: right; white-space: nowrap; }
    .ar-vacio { padding: 18px; border: 1px dashed #DDE3EE; border-radius: 12px; color: #64748B; font-size: 14px; text-align: center; }
    @media (max-width: 640px) { .ar-fila { grid-template-columns: 38px minmax(0, 1fr); } .ar-fecha { grid-column: 2; text-align: left; } .ar-desc { white-space: normal; } }
</style>

<section class="ar-tarjeta" aria-labelledby="ar-titulo">
    <div class="ar-cab">
        <div>
            <div class="ar-titulo" id="ar-titulo">Actividad reciente</div>
            <div class="ar-ayuda">Tus 3 acciones más recientes en la plataforma.</div>
        </div>
        @if ($historialUrl)
            <a href="{{ $historialUrl }}" class="ar-vertodo">Ver historial completo →</a>
        @endif
    </div>

    @if ($recientes->isEmpty())
        <div class="ar-vacio">Todavía no hay acciones registradas para tu cuenta.</div>
    @else
        <div class="ar-lista">
            @foreach ($recientes as $actividad)
                @php [$etiqueta, $icono] = $actividad->infoAccion(); $momento = $actividad->updated_at ?? $actividad->created_at; @endphp
                <div class="ar-fila" wire:key="actividad-reciente-{{ $actividad->id }}">
                    <span class="ar-icono" aria-hidden="true">{{ $icono }}</span>
                    <div style="min-width: 0">
                        <div class="ar-accion">{{ $etiqueta }}</div>
                        <div class="ar-desc" title="{{ $actividad->descripcion }}">{{ $actividad->descripcion }}</div>
                    </div>
                    <div class="ar-fecha" title="{{ $momento?->timezone('America/Merida')->format('d/m/Y H:i') }}">
                        {{ $momento?->locale('es')->diffForHumans() }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
