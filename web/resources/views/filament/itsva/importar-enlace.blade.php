{{-- «Subir convocatoria»: botones de sección (Automática / Manual) y, en
     automática, la caja del enlace y el resultado del análisis. Se renderiza
     dentro del formulario de creación (trait ImportaDesdeEnlace). --}}
@php
    $pagina = $getLivewire();
    $analisis = $pagina->analisis;
    $validacion = $analisis['validacion'] ?? null;
    $existente = $analisis['existente'] ?? null;
    $panel = filament()->getId();
    $modo = $pagina->modo;
    $desdeArchivo = isset($analisis['archivo']);
    // Un archivo nunca trae enlace ni fuente: no es un error del documento.
    if ($desdeArchivo && $validacion) {
        $validacion['bloqueos'] = array_values(array_filter(
            $validacion['bloqueos'] ?? [],
            fn ($b) => ! str_contains($b, 'URL oficial') && ! str_contains($b, 'fuente asociada')
        ));
    }
@endphp

<div class="imp">
    <style>
        .imp { display: grid; gap: 14px; }
        .imp-modos { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .imp-modo { display: flex; gap: 12px; align-items: center; padding: 14px 16px; border: 2px solid #DDE3EE; border-radius: 14px; background: #fff; color: #1A2C4E; text-align: left; font: inherit; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
        .imp-modo:hover { border-color: #93C5FD; }
        .imp-modo.activo { border-color: #1A4B8C; background: #F0F5FC; box-shadow: 0 0 0 3px rgba(26, 75, 140, .12); }
        .imp-modo strong { display: block; font-size: 15px; }
        .imp-modo small { display: block; margin-top: 2px; color: #64748B; font-size: 12px; }
        .imp-modo-icono { width: 40px; height: 40px; flex-shrink: 0; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: #E8EEF8; font-size: 20px; }
        .imp-modo.activo .imp-modo-icono { background: #1A4B8C; }
        @media (max-width: 640px) { .imp-modos { grid-template-columns: 1fr; } }
        .imp-caja { position: relative; overflow: hidden; border-radius: 16px; padding: 20px; background: linear-gradient(135deg, #1A4B8C 0%, #2563EB 100%); color: #fff; }
        .imp-caja::after { content: ''; position: absolute; right: -50px; top: -70px; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,.08); }
        .imp-caja h3 { margin: 0; font-size: 17px; font-weight: 800; }
        .imp-caja p { margin: 4px 0 14px; color: #DBEAFE; font-size: 13px; }
        .imp-fila { position: relative; z-index: 1; display: flex; gap: 8px; flex-wrap: wrap; }
        .imp-fila input { flex: 1 1 320px; min-height: 44px; border: 0; border-radius: 10px; padding: 0 14px; font-size: 14px; color: #1A2C4E; background: #fff; }
        .imp-btn { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 10px; border: 0; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; }
        .imp-btn-blanco { background: #fff; color: #1A4B8C; }
        .imp-btn-blanco:disabled { opacity: .7; cursor: progress; }
        .imp-error { position: relative; z-index: 1; margin-top: 8px; color: #FECACA; font-size: 13px; font-weight: 600; }
        .imp-cargando { position: relative; z-index: 1; margin-top: 10px; font-size: 13px; color: #DBEAFE; }
        .imp-nota { margin-top: 10px; color: #BFDBFE; font-size: 12px; position: relative; z-index: 1; }
        .imp-o { position: relative; z-index: 1; display: flex; align-items: center; gap: 10px; margin: 16px 0 12px; color: #BFDBFE; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
        .imp-o::before, .imp-o::after { content: ''; flex: 1; height: 1px; background: rgba(255,255,255,.25); }
        .imp-archivo { flex: 1 1 320px; display: flex; align-items: center; gap: 10px; min-height: 44px; padding: 0 14px; border: 2px dashed rgba(255,255,255,.5); border-radius: 10px; color: #fff; font-size: 13px; cursor: pointer; }
        .imp-archivo:hover { background: rgba(255,255,255,.08); }
        .imp-archivo span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .imp-leido { margin-bottom: 6px; font-size: 12px; opacity: .85; }

        .imp-res { border-radius: 14px; padding: 16px 18px; border: 1px solid; }
        .imp-ok { background: #ECFDF5; border-color: #A7F3D0; color: #065F46; }
        .imp-mal { background: #FFFBEB; border-color: #FCD34D; color: #92400E; }
        .imp-dup { background: #EFF6FF; border-color: #BFDBFE; color: #1E3A8A; }
        .imp-res h4 { margin: 0 0 6px; font-size: 15px; font-weight: 800; }
        .imp-res ul { margin: 6px 0 0; padding-left: 18px; font-size: 13px; line-height: 1.6; }
        .imp-puntaje { display: inline-flex; padding: 2px 10px; border-radius: 999px; background: rgba(255,255,255,.7); font-size: 12px; font-weight: 800; margin-left: 6px; }
        .imp-acciones { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
        .imp-btn-ambar { background: #D97706; color: #fff; }
        .imp-btn-gris { background: #fff; color: #475569; border: 1px solid #CBD5E1; }
        .imp-btn-azul { background: #1A4B8C; color: #fff; }
        .imp-confirmado { margin-top: 10px; font-size: 13px; font-weight: 700; }
    </style>

    <div class="imp-modos" role="tablist" aria-label="Forma de subir la convocatoria">
        <button type="button" role="tab" aria-selected="{{ $modo === 'automatica' ? 'true' : 'false' }}"
            @class(['imp-modo', 'activo' => $modo === 'automatica']) wire:click="cambiarModo('automatica')">
            <span class="imp-modo-icono">✨</span>
            <span>
                <strong>Automática</strong>
                <small>Pegas el enlace y la llenamos por ti</small>
            </span>
        </button>
        <button type="button" role="tab" aria-selected="{{ $modo === 'manual' ? 'true' : 'false' }}"
            @class(['imp-modo', 'activo' => $modo === 'manual']) wire:click="cambiarModo('manual')">
            <span class="imp-modo-icono">✍️</span>
            <span>
                <strong>Manual</strong>
                <small>Llenas el formulario tú mismo</small>
            </span>
        </button>
    </div>

    @if ($modo === 'automatica')
    <section class="imp-caja">
        <h3>✨ Pega el enlace o sube el archivo de la convocatoria</h3>
        <p>La leemos igual que el motor, llenamos el formulario y te decimos si cumple los criterios. Después podrás revisarla y corregirla antes de publicar.</p>

        <div class="imp-fila">
            <input type="url" wire:model="enlace" wire:keydown.enter.prevent="analizarEnlace"
                placeholder="https://organismo.gob.mx/convocatorias/…" aria-label="Enlace de la convocatoria">
            <button type="button" class="imp-btn imp-btn-blanco" wire:click="analizarEnlace"
                wire:loading.attr="disabled" wire:target="analizarEnlace">
                <span wire:loading.remove wire:target="analizarEnlace">🔍 Analizar enlace</span>
                <span wire:loading wire:target="analizarEnlace">Analizando…</span>
            </button>
        </div>

        <div class="imp-cargando" wire:loading wire:target="analizarEnlace">
            Leyendo la página y sus documentos PDF… puede tardar hasta un minuto.
        </div>

        @error('enlace')
            <div class="imp-error">{{ $message }}</div>
        @enderror

        <div class="imp-o">o sube el archivo</div>

        <div class="imp-fila">
            <label class="imp-archivo">
                <input type="file" wire:model="archivoConvocatoria" accept="application/pdf,image/jpeg,image/png,image/webp" style="display: none">
                📄 <span wire:loading.remove wire:target="archivoConvocatoria">{{ $pagina->archivoConvocatoria ? $pagina->archivoConvocatoria->getClientOriginalName() : 'Elige el PDF o la imagen (cartel, captura) — hasta 15 MB' }}</span>
                <span wire:loading wire:target="archivoConvocatoria">Subiendo archivo…</span>
            </label>
            <button type="button" class="imp-btn imp-btn-blanco" wire:click="analizarArchivo"
                wire:loading.attr="disabled" wire:target="analizarArchivo,archivoConvocatoria">
                <span wire:loading.remove wire:target="analizarArchivo">📄 Analizar archivo</span>
                <span wire:loading wire:target="analizarArchivo">Leyendo…</span>
            </button>
        </div>

        <div class="imp-cargando" wire:loading wire:target="analizarArchivo">
            Leyendo el archivo… las imágenes y los PDF escaneados tardan un poco más.
        </div>

        @error('archivoConvocatoria')
            <div class="imp-error">{{ $message }}</div>
        @enderror

        <div class="imp-nota">Solo se leen sitios públicos y archivos PDF o de imagen. Nada se publica hasta que pulses «Publicar convocatoria»; si la publicas, el archivo queda como documento de la convocatoria.</div>
    </section>

    @if ($analisis)
        @if ($existente && $existente['estado'] !== 'PENDIENTE_REVISION')
            <section class="imp-res imp-dup">
                <h4>ℹ️ Esta convocatoria ya está en la plataforma</h4>
                <div style="font-size: 13px">«{{ $existente['titulo'] }}» ({{ $existente['estado'] === 'CERRADA' ? 'cerrada' : 'publicada' }}). No hace falta subirla otra vez.</div>
                <div class="imp-acciones">
                    @if ($panel !== 'admin')
                        <a class="imp-btn imp-btn-azul" href="{{ route('filament.' . $panel . '.pages.detalle-convocatoria', ['record' => $existente['id']]) }}">Ver convocatoria</a>
                    @endif
                    <button type="button" class="imp-btn imp-btn-gris" wire:click="descartarAnalisis">Subir otra</button>
                </div>
            </section>
        @elseif ($validacion['publicable'] ?? false)
            <section class="imp-res imp-ok">
                @if ($desdeArchivo)<div class="imp-leido">📄 {{ $analisis['archivo'] }} · {{ number_format($analisis['caracteres_leidos'] ?? 0) }} caracteres leídos</div>@endif
                <h4>✅ Cumple los criterios <span class="imp-puntaje">{{ $validacion['score'] }}/{{ $validacion['umbral'] }}</span></h4>
                <div style="font-size: 13px">Revisa los datos que llenamos abajo y pulsa «Publicar convocatoria».</div>
                <div class="imp-acciones">
                    <button type="button" class="imp-btn imp-btn-gris" wire:click="descartarAnalisis">No publicar</button>
                </div>
            </section>
        @else
            <section class="imp-res imp-mal">
                @if ($desdeArchivo)<div class="imp-leido">📄 {{ $analisis['archivo'] }} · {{ number_format($analisis['caracteres_leidos'] ?? 0) }} caracteres leídos. Si tienes el enlace oficial, agrégalo abajo: ayuda a verificarla.</div>@endif
                <h4>⚠️ No cumple todos los criterios automáticos <span class="imp-puntaje">{{ $validacion['score'] ?? 0 }}/{{ $validacion['umbral'] ?? 70 }}</span></h4>
                <div style="font-size: 13px">El motor no la publicaría sola por:</div>
                <ul>
                    @foreach (($validacion['bloqueos'] ?? []) as $bloqueo)
                        <li>{{ $bloqueo }}</li>
                    @endforeach
                    @if (($validacion['score'] ?? 0) < ($validacion['umbral'] ?? 70))
                        <li>Faltan datos (puntaje menor a {{ $validacion['umbral'] ?? 70 }}). Puedes completarlos en el formulario.</li>
                    @endif
                </ul>

                @if ($pagina->publicarIgual)
                    <div class="imp-confirmado">Marcada para publicar de todas maneras. Revisa los datos y pulsa «Publicar convocatoria».</div>
                    <div class="imp-acciones">
                        <button type="button" class="imp-btn imp-btn-gris" wire:click="descartarAnalisis">No publicar</button>
                    </div>
                @else
                    <div class="imp-acciones">
                        <button type="button" class="imp-btn imp-btn-ambar" wire:click="publicarDeTodasManeras">Publicar de todas maneras</button>
                        <button type="button" class="imp-btn imp-btn-gris" wire:click="descartarAnalisis">No publicar</button>
                    </div>
                @endif
            </section>
        @endif
    @endif
    @endif
</div>
