<x-filament-panels::page>
@php
    $estados = [
        'PENDIENTE_REVISION' => ['No publicada', '#FEF3C7', '#B45309'],
        'DESCARTADA' => ['Descartada', '#FEE2E2', '#B91C1C'],
        'PUBLICADA' => ['Publicada', '#D1FAE5', '#047857'],
        'CERRADA' => ['Cerrada', '#F1F5F9', '#475569'],
    ];
    [$textoEstado, $fondoEstado, $colorEstado] = $estados[$convocatoria->estado] ?? [$convocatoria->estado, '#F1F5F9', '#475569'];
    $tipoActual = \App\Models\Convocatoria::TIPOS[$convocatoria->tipo ?? ''] ?? null;
    $acciones = ['APROBAR' => ['Publicó', '✅'], 'RECHAZAR' => ['Descartó', '🚫'], 'SOLICITAR_CORRECCIONES' => ['Pidió correcciones', '✏️'], 'REENVIAR_REVISION' => ['Reenvió a revisión', '↩️']];
    $fecha = fn ($f) => $f ? $f->locale('es')->isoFormat('D [de] MMMM [de] YYYY') : 'Sin fecha';
    $monto = fn ($m) => $m !== null ? '$' . number_format((float) $m, 2) . ' ' . ($convocatoria->moneda ?: 'MXN') : null;
@endphp

<style>
    .rv { color: #0F1827; }
    .rv-volver { display: inline-flex; gap: 6px; margin-bottom: 12px; color: #047857; font-size: 13px; font-weight: 700; text-decoration: none; }
    .rv-cab { padding: 22px 24px; margin-bottom: 18px; border: 1px solid #DDE3EE; border-radius: 18px; background: #fff; }
    .rv-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
    .rv-chip { padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; background: #F1F5F9; color: #475569; }
    .rv-cab h2 { margin: 0; font: 800 21px/1.3 'DM Sans', sans-serif; }
    .rv-sub { display: flex; flex-wrap: wrap; gap: 6px 16px; margin-top: 8px; color: #64748B; font-size: 13px; }
    .rv-sub a { color: #047857; font-weight: 600; word-break: break-all; }
    .rv-rejilla { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr); gap: 18px; align-items: start; }
    .rv-tarjeta { padding: 20px 22px; margin-bottom: 18px; border: 1px solid #DDE3EE; border-radius: 16px; background: #fff; }
    .rv-tarjeta h3 { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin: 0 0 4px; font: 800 16px 'DM Sans', sans-serif; }
    .rv-ayuda { margin: 0 0 14px; color: #64748B; font-size: 13px; line-height: 1.5; }
    .rv-datos { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 14px; }
    .rv-dato { padding: 10px 12px; border-radius: 10px; background: #F8FAFC; }
    .rv-dato span { display: block; color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; }
    .rv-dato strong { display: block; margin-top: 2px; font-size: 14px; }
    .rv-texto { color: #334155; font-size: 14px; line-height: 1.6; white-space: pre-line; max-height: 260px; overflow: auto; padding-right: 6px; }
    .rv-texto + .rv-etq { margin-top: 14px; }
    .rv-etq { margin-bottom: 4px; color: #64748B; font-size: 12px; font-weight: 700; text-transform: uppercase; }
    .rv-boton { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 40px; padding: 0 16px; border: 1px solid #DDE3EE; border-radius: 10px; background: #fff; color: #0F1827; font: 700 13px 'DM Sans', sans-serif; text-decoration: none; cursor: pointer; }
    .rv-boton:hover { background: #F8FAFC; }
    .rv-boton:disabled { opacity: .5; cursor: not-allowed; }
    .rv-verde { background: #059669; border-color: #059669; color: #fff; }
    .rv-verde:hover { background: #047857; }
    .rv-rojo { color: #B91C1C; border-color: #FECACA; }
    .rv-rojo:hover { background: #FEF2F2; }
    .rv-resultado { margin-top: 14px; padding: 14px; border-radius: 12px; font-size: 13px; line-height: 1.5; }
    .rv-resultado.ok { background: #ECFDF5; border: 1px solid #A7F3D0; }
    .rv-resultado.no { background: #FFFBEB; border: 1px solid #FDE68A; }
    .rv-resultado strong { display: block; margin-bottom: 6px; font-size: 14px; }
    .rv-resultado ul { margin: 6px 0 0; padding-left: 18px; }
    .rv-campo { margin-bottom: 12px; }
    .rv-campo label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 700; }
    .rv-campo select, .rv-campo textarea { width: 100%; border: 1px solid #DDE3EE; border-radius: 10px; padding: 9px 12px; font: 14px 'DM Sans', sans-serif; background: #F8FAFC; color: #0F1827; }
    .rv-campo textarea { min-height: 90px; resize: vertical; }
    .rv-error { margin-top: 4px; color: #B91C1C; font-size: 12px; }
    .rv-aviso { margin-bottom: 12px; padding: 10px 12px; border-radius: 10px; background: #FEF2F2; color: #991B1B; font-size: 13px; }
    .rv-aviso ul { margin: 4px 0 0; padding-left: 18px; }
    .rv-botones { display: flex; flex-wrap: wrap; gap: 8px; }
    .rv-hist { display: grid; gap: 10px; }
    .rv-hist-fila { display: grid; grid-template-columns: 28px minmax(0, 1fr); gap: 10px; font-size: 13px; }
    .rv-hist-fila small { color: #64748B; }
    .rv-hist-obs { margin-top: 3px; color: #334155; }
    @media (max-width: 1000px) { .rv-rejilla { grid-template-columns: 1fr; } }
    @media (max-width: 600px) { .rv-datos { grid-template-columns: 1fr; } }
</style>

<div class="rv">
    <a href="{{ $urlLista }}" class="rv-volver">← Convocatorias</a>

    <div class="rv-cab">
        <div class="rv-chips">
            <span class="rv-chip" style="background: {{ $fondoEstado }}; color: {{ $colorEstado }}">{{ $textoEstado }}</span>
            @if ($tipoActual)
                <span class="rv-chip" style="background: {{ $tipoActual[2] }}; color: {{ $tipoActual[3] }}">{{ $tipoActual[1] }} {{ $tipoActual[0] }}</span>
            @elseif ($convocatoria->tipo === 'NO_APLICA')
                <span class="rv-chip">No aplica (resultados, vacantes o no elegible)</span>
            @endif
            <span class="rv-chip">{{ $convocatoria->origen === 'MANUAL' ? 'Subida a mano' : 'Encontrada por el motor' }}</span>
        </div>
        <h2>{{ $convocatoria->titulo ?: 'Sin título' }}</h2>
        <div class="rv-sub">
            @if ($convocatoria->fuente)<span>Fuente: {{ $convocatoria->fuente->nombre }}</span>@endif
            @if ($convocatoria->url_original)<a href="{{ $convocatoria->url_original }}" target="_blank" rel="noopener noreferrer">{{ $convocatoria->url_original }} ↗</a>@endif
        </div>
    </div>

    <div class="rv-rejilla">
        <div>
            <section class="rv-tarjeta">
                <h3>
                    Datos de la convocatoria
                    <a href="{{ $urlEditar }}" class="rv-boton">✏️ Corregir datos</a>
                </h3>
                <p class="rv-ayuda">Revisa que la información coincida con la página oficial. Si algo falta o está mal (por ejemplo la fecha de cierre), corrígelo antes de publicar.</p>

                <div class="rv-datos">
                    <div class="rv-dato"><span>Cierre</span><strong>{{ $fecha($convocatoria->fecha_cierre) }}</strong></div>
                    <div class="rv-dato"><span>Publicación</span><strong>{{ $fecha($convocatoria->fecha_publicacion) }}</strong></div>
                    <div class="rv-dato"><span>Organismo</span><strong>{{ $convocatoria->organismo?->nombre ?? 'Sin organismo' }}</strong></div>
                    <div class="rv-dato"><span>Monto</span><strong>{{ $monto($convocatoria->monto_maximo) ?? $monto($convocatoria->monto_minimo) ?? 'No especificado' }}</strong></div>
                    <div class="rv-dato"><span>Dirigida a</span><strong>{{ collect((array) $convocatoria->dirigido_a)->map(fn ($a) => \App\Models\Convocatoria::AUDIENCIAS[$a] ?? $a)->implode(', ') ?: 'Sin indicar' }}</strong></div>
                    <div class="rv-dato"><span>Registrada</span><strong>{{ $convocatoria->created_at?->timezone('America/Merida')->format('d/m/Y H:i') }}</strong></div>
                </div>

                <div class="rv-etq">Descripción</div>
                <div class="rv-texto rico-html">{!! $convocatoria->descripcionHtml() !!}</div>
                @if (filled($convocatoria->objetivo))
                    <div class="rv-etq">Objetivo</div>
                    <div class="rv-texto rico-html">{!! $convocatoria->objetivoHtml() !!}</div>
                @endif
            </section>

            @if ($decisiones->isNotEmpty())
                <section class="rv-tarjeta">
                    <h3>Decisiones anteriores</h3>
                    <div class="rv-hist">
                        @foreach ($decisiones as $d)
                            @php [$verbo, $icono] = $acciones[$d->accion] ?? [$d->accion, '•']; @endphp
                            <div class="rv-hist-fila">
                                <span aria-hidden="true">{{ $icono }}</span>
                                <div>
                                    <strong>{{ $verbo }}</strong> · {{ trim(($d->revisor?->name ?? 'Usuario') . ' ' . ($d->revisor?->apellidos ?? '')) }}
                                    <small>· {{ $d->fecha_decision?->timezone('America/Merida')->format('d/m/Y H:i') }}</small>
                                    @if ($d->observaciones)<div class="rv-hist-obs">«{{ $d->observaciones }}»</div>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <div>
            <section class="rv-tarjeta">
                <h3>1. Validador automático</h3>
                <p class="rv-ayuda">Vuelve a leer la página oficial con el mismo validador del motor (puntaje mínimo 70) y te dice por qué no la publicó. Si aquí no tiene fecha de cierre y la página oficial sí la trae, la guarda sola; no cambia nada más.</p>
                <button type="button" class="rv-boton" wire:click="validarAhora" wire:loading.attr="disabled" wire:target="validarAhora" @disabled(! $convocatoria->url_original)>
                    <span wire:loading.remove wire:target="validarAhora">🔎 Validar ahora</span>
                    <span wire:loading wire:target="validarAhora">Leyendo la página oficial…</span>
                </button>

                @if ($validacion)
                    <div @class(['rv-resultado', 'ok' => $validacion['publicable'] ?? false, 'no' => ! ($validacion['publicable'] ?? false)])>
                        <strong>{{ ($validacion['publicable'] ?? false) ? '✅ Hoy sí cumple los criterios' : '⚠️ No cumple los criterios automáticos' }} · {{ $validacion['score'] ?? 0 }}/{{ $validacion['umbral'] ?? 70 }}</strong>
                        @if (! empty($validacion['bloqueos']))
                            Bloqueos:
                            <ul>@foreach ($validacion['bloqueos'] as $b)<li>{{ $b }}</li>@endforeach</ul>
                        @endif
                        @if (! empty($validacion['razones']))
                            <details style="margin-top: 6px"><summary>Detalle del puntaje</summary><ul>@foreach ($validacion['razones'] as $r)<li>{{ $r }}</li>@endforeach</ul></details>
                        @endif
                        @if ($fechaAplicada)
                            <div style="margin-top: 8px">📅 No tenía fecha de cierre: se guardó la de la página oficial, <strong style="display: inline">{{ \Illuminate\Support\Carbon::parse($fechaAplicada)->format('d/m/Y') }}</strong>. El motor la toma en cuenta en su próxima revisión.</div>
                            @if ($evidenciaFecha)<div class="rv-hist-obs" style="margin-top: 4px">Según la página: «{{ \Illuminate\Support\Str::limit($evidenciaFecha, 220) }}»</div>@endif
                        @elseif (($datosMotor['fecha_cierre'] ?? null) && ($datosMotor['fecha_cierre'] !== $convocatoria->fecha_cierre?->toDateString()))
                            <div style="margin-top: 8px">La página oficial dice que cierra el <strong style="display: inline">{{ \Illuminate\Support\Carbon::parse($datosMotor['fecha_cierre'])->format('d/m/Y') }}</strong>; aquí está guardado {{ $convocatoria->fecha_cierre?->format('d/m/Y') ?? 'sin fecha' }}. Como ya tenía una fecha, no se cambia sola: corrígela en «Corregir datos» si es así.</div>
                            @if ($evidenciaFecha)<div class="rv-hist-obs" style="margin-top: 4px">Según la página: «{{ \Illuminate\Support\Str::limit($evidenciaFecha, 220) }}»</div>@endif
                        @endif
                    </div>
                @elseif (! $convocatoria->url_original)
                    <div class="rv-resultado no">No tiene enlace a la página oficial; decide con los datos registrados.</div>
                @endif
            </section>

            <section class="rv-tarjeta">
                <h3>2. Tu decisión</h3>

                @if (! $revisable)
                    <p class="rv-ayuda">Esta convocatoria ya está {{ mb_strtolower($textoEstado) }}; no necesita revisión.</p>
                    <a href="{{ $urlDetalle }}" class="rv-boton">Ver detalle</a>
                @else
                    <p class="rv-ayuda">
                        @if ($convocatoria->estado === 'DESCARTADA')
                            Está descartada: el motor no la vuelve a revisar. Puedes publicarla o devolverla a «no publicadas» para que el motor la revalide.
                        @else
                            Si la publicas, la verán docentes y directivos aunque el validador no la haya aprobado. Si la descartas, el motor ya no la revalidará ni la publicará.
                        @endif
                    </p>

                    <div class="rv-campo">
                        <label for="rv-tipo">Tipo de convocatoria</label>
                        <select id="rv-tipo" wire:model.live="tipo">
                            <option value="">Elige el tipo…</option>
                            @foreach (\App\Models\Convocatoria::TIPOS as $clave => [$etiqueta, $icono])
                                <option value="{{ $clave }}">{{ $icono }} {{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rv-campo">
                        <label for="rv-obs">Motivo de la decisión</label>
                        <textarea id="rv-obs" wire:model="observaciones" placeholder="Ejemplo: revisé la página oficial; la fecha de cierre es correcta y aplica para docentes del ITSVA."></textarea>
                        @error('observaciones')<div class="rv-error">{{ $message }}</div>@enderror
                    </div>

                    @if ($impedimentos)
                        <div class="rv-aviso">Para publicarla falta:<ul>@foreach ($impedimentos as $i)<li>{{ $i }}</li>@endforeach</ul></div>
                    @endif
                    @error('decision')<div class="rv-aviso">{{ $message }}</div>@enderror
                    @if (! $impedimentos && ! $convocatoria->fecha_cierre)
                        <div class="rv-aviso" style="background: #FFFBEB; color: #92400E">No tiene fecha de cierre: si la publicas, se mostrará como vigente hasta que alguien la corrija o la cierre. Si la página oficial tiene la fecha, agrégala en «Corregir datos».</div>
                    @endif

                    <div class="rv-botones">
                        <button type="button" class="rv-boton rv-verde" wire:click="publicar" wire:confirm="¿Publicar esta convocatoria? La verán docentes y directivos." wire:loading.attr="disabled" @disabled($impedimentos)>✅ Publicar</button>
                        @if ($convocatoria->estado === 'PENDIENTE_REVISION')
                            <button type="button" class="rv-boton rv-rojo" wire:click="descartar" wire:confirm="¿Descartar esta convocatoria? El motor ya no la publicará." wire:loading.attr="disabled">🚫 Descartar</button>
                        @else
                            <button type="button" class="rv-boton" wire:click="devolver" wire:loading.attr="disabled">↩️ Devolver a no publicadas</button>
                        @endif
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
</x-filament-panels::page>
