<x-filament-panels::page>
@php
    $panel = filament()->getId();
    $ruta = fn (string $pagina) => route('filament.' . $panel . '.pages.' . $pagina, ['propuesta' => $propuesta->id]);
    $dinero = fn ($monto) => '$' . number_format((float) $monto, 2);
@endphp

<style>
    .inf { color: #1A2C4E; font-family: 'DM Sans', sans-serif; }
    .inf-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; margin-bottom: 18px; }
    .inf-top h1 { margin: 0; font-size: 22px; font-weight: 700; }
    .inf-top p { margin: 6px 0 0; color: #64748B; font-size: 12px; }
    .inf-acciones { display: flex; flex-wrap: wrap; gap: 8px; }
    .inf-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 38px; padding: 0 14px; border-radius: 8px; border: 1px solid #DDE3EE; background: #fff; color: #1A4B8C; font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: none; }
    .inf-btn-azul { background: #1A4B8C; border-color: #1A4B8C; color: #fff; }
    .inf-btn-rojo { color: #B91C1C; border-color: #FECACA; }
    .inf-btn-chico { min-height: 30px; padding: 0 10px; font-size: 11px; }
    .inf-btn:disabled { opacity: .5; cursor: not-allowed; }
    .inf-card { background: #fff; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; margin-bottom: 16px; }
    .inf-card h2 { margin: 0 0 4px; font-size: 16px; font-weight: 700; }
    .inf-sub { margin: 0 0 14px; color: #64748B; font-size: 12px; }
    .inf-guia { margin: 0 0 14px; padding: 12px 14px; background: #F1F5FB; border-radius: 10px; font-size: 12px; color: #334155; }
    .inf-guia ul { margin: 6px 0 0; padding-left: 18px; list-style: disc; }
    .inf-meta { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-top: 10px; font-size: 12px; color: #64748B; }
    .inf-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px; }
    .inf-campo label { display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px; }
    .inf-campo input, .inf-campo select, .inf-campo textarea { width: 100%; border: 1px solid #DDE3EE; border-radius: 8px; padding: 8px 10px; font-size: 13px; color: #1E293B; background: #fff; }
    .inf-campo textarea { min-height: 84px; }
    .inf-campo.ancho { grid-column: 1 / -1; }
    .inf-error { color: #B91C1C; font-size: 11px; margin-top: 3px; }
    .inf-form { border: 1px dashed #93A8C9; border-radius: 12px; padding: 16px; margin-bottom: 14px; background: #FBFCFE; }
    .inf-proyecto { border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px; margin-bottom: 12px; }
    .inf-proyecto-top { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .inf-prioridad { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 50%; background: #1A4B8C; color: #fff; font-weight: 700; font-size: 13px; margin-right: 10px; flex: none; }
    .inf-titulo { display: flex; align-items: center; font-size: 15px; font-weight: 700; }
    .inf-chip { display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #EEF2FF; color: #3730A3; margin-top: 6px; }
    .inf-chip-ok { background: #DCFCE7; color: #166534; }
    .inf-chip-falta { background: #FEF3C7; color: #92400E; }
    .inf-cifras { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 8px; margin: 12px 0; }
    .inf-cifra { background: #F8FAFC; border-radius: 8px; padding: 8px 10px; }
    .inf-cifra span { display: block; font-size: 10px; color: #64748B; text-transform: uppercase; letter-spacing: .03em; }
    .inf-cifra strong { font-size: 14px; }
    .inf-laminas { display: grid; gap: 6px; margin-top: 8px; }
    .inf-lamina { display: flex; justify-content: space-between; align-items: center; gap: 8px; padding: 7px 10px; border-radius: 8px; background: #F8FAFC; font-size: 12px; }
    .inf-lamina.falta { background: #FFFBEB; }
    .inf-tabla { width: 100%; border-collapse: collapse; font-size: 12px; }
    .inf-tabla th { text-align: left; background: #1A4B8C; color: #fff; padding: 8px; font-weight: 700; }
    .inf-tabla td { border-bottom: 1px solid #E2E8F0; padding: 8px; }
    .inf-tabla .num { text-align: right; white-space: nowrap; }
    .inf-tabla tfoot td { font-weight: 700; background: #F1F5FB; }
    .inf-aviso { padding: 10px 12px; border-radius: 10px; background: #FFFBEB; color: #92400E; font-size: 12px; margin-top: 10px; }
</style>

<div class="inf">
    <div class="inf-top">
        <div>
            <h1>🏗️ Infraestructura de la propuesta</h1>
            <p>{{ $propuesta->titulo }} · {{ $propuesta->convocatoria?->titulo }}</p>
        </div>
        <div class="inf-acciones">
            <a class="inf-btn" href="{{ $ruta('editor-propuesta') }}">← Datos de la propuesta</a>
            <a class="inf-btn" href="{{ $ruta('presupuesto-propuesta') }}">Presupuesto</a>
            <a class="inf-btn" href="{{ $ruta('vista-previa-propuesta') }}">Vista previa</a>
            @if ($propuesta->esInfraestructura())
                <a class="inf-btn inf-btn-azul" href="{{ $ruta('objetivos-propuesta') }}">Continuar a Objetivos →</a>
            @endif
        </div>
    </div>

    @if (! $propuesta->esInfraestructura())
        <section class="inf-card">
            <h2>Propuesta de infraestructura (modelo FAM)</h2>
            <p class="inf-sub">Esta propuesta usa el formato de proyecto de investigación.</p>
            <div class="inf-guia">
                Usa este formato para convocatorias de infraestructura física educativa, como el Fondo de Aportaciones Múltiples (FAM). Se arma como pide su guía:
                <ul>
                    <li>Un diagnóstico de la capacidad física instalada y su grado de utilización (máximo 15 cuartillas).</li>
                    <li>Hasta 5 proyectos priorizados: obra nueva, continuidad, equipamiento, adecuaciones, mantenimiento o sustentabilidad.</li>
                    <li>Las láminas de presentación que pide cada tipo de proyecto (fotos, planos, render, especificaciones).</li>
                    <li>El monto de cada proyecto sale de tu presupuesto con las partidas del clasificador.</li>
                </ul>
            </div>
            @if ($editable)
                <button type="button" class="inf-btn inf-btn-azul" wire:click="usarFormatoInfraestructura">Usar formato de infraestructura</button>
            @endif
        </section>
    @else
        {{-- 1. Diagnóstico --}}
        <section class="inf-card">
            <h2>1. Diagnóstico de la infraestructura física</h2>
            <p class="inf-sub">Sustenta por qué se pide ampliar, rehabilitar, dar mantenimiento, equipar o construir.</p>
            <div class="inf-guia">
                Aborda, según la guía del FAM:
                <ul>
                    <li>La situación actual de la capacidad física instalada y su grado de utilización.</li>
                    <li>Los principales problemas y áreas de oportunidad.</li>
                    <li>El impacto de los espacios solicitados en los programas académicos y en la vocación de la región.</li>
                    <li>Las obras en proceso, las causas de su demora y las acciones para concluirlas.</li>
                    <li>La priorización de las necesidades, con base en los planes de estudio y el Plan Maestro de Infraestructura Física.</li>
                </ul>
            </div>
            <x-editor-enriquecido campo="diagnostico" :editable="$editable" :limite="\App\Filament\Docente\Pages\InfraestructuraPropuesta::LIMITE_DIAGNOSTICO" placeholder="Describe la capacidad física instalada, su uso y las necesidades priorizadas..." />
            @error('diagnostico')<div class="inf-error">{{ $message }}</div>@enderror
            <div class="inf-meta">
                <span>Aproximadamente {{ $cuartillas }} de 15 cuartillas.</span>
                @if ($editable)
                    <button type="button" class="inf-btn inf-btn-azul" wire:click="guardarDiagnostico" wire:loading.attr="disabled" wire:target="guardarDiagnostico">Guardar diagnóstico</button>
                @endif
            </div>
        </section>

        {{-- 2. Proyectos --}}
        <section class="inf-card">
            <div class="inf-proyecto-top">
                <div>
                    <h2>2. Proyectos priorizados ({{ $proyectos->count() }} de {{ $maximo }})</h2>
                    <p class="inf-sub" style="margin: 0 0 14px">El orden es la prioridad. El monto FAM de cada proyecto es lo que le asignes en el presupuesto.</p>
                </div>
                @if ($editable && $proyectoEditando === null && $proyectos->count() < $maximo)
                    <div><button type="button" class="inf-btn inf-btn-azul" wire:click="nuevoProyecto">＋ Agregar proyecto</button></div>
                @endif
            </div>

            @if ($proyectoEditando !== null)
                <div class="inf-form">
                    <h3 style="margin: 0 0 12px; font-size: 14px">{{ $proyectoEditando === 0 ? 'Nuevo proyecto' : 'Editar proyecto' }}</h3>
                    <div class="inf-grid">
                        <div class="inf-campo ancho">
                            <label>Nombre del proyecto *</label>
                            <input type="text" wire:model="proyecto.nombre" maxlength="300" placeholder="Ej. Equipamiento del laboratorio de ingeniería industrial">
                            @error('proyecto.nombre')<div class="inf-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="inf-campo ancho">
                            <label>Tipo de proyecto *</label>
                            <select wire:model.live="proyecto.tipo_proyecto">
                                @foreach ($tipos as $clave => $texto)
                                    <option value="{{ $clave }}">{{ $texto }}</option>
                                @endforeach
                            </select>
                            @error('proyecto.tipo_proyecto')<div class="inf-error">{{ $message }}</div>@enderror
                            @if (! empty($proyecto['tipo_proyecto']) && isset($requeridas[$proyecto['tipo_proyecto']]))
                                <div style="font-size: 11px; color: #64748B; margin-top: 4px">Láminas que pide: {{ collect($requeridas[$proyecto['tipo_proyecto']])->map(fn ($l) => $laminas[$l])->implode(' · ') }}</div>
                            @endif
                        </div>
                        <div class="inf-campo ancho"><label>Objetivo del proyecto</label><textarea wire:model="proyecto.objetivo"></textarea>@error('proyecto.objetivo')<div class="inf-error">{{ $message }}</div>@enderror</div>
                        <div class="inf-campo ancho"><label>Justificación (problemática detectada, congruente con el diagnóstico)</label><textarea wire:model="proyecto.justificacion"></textarea>@error('proyecto.justificacion')<div class="inf-error">{{ $message }}</div>@enderror</div>
                        <div class="inf-campo ancho"><label>Descripción y acciones</label><textarea wire:model="proyecto.descripcion"></textarea>@error('proyecto.descripcion')<div class="inf-error">{{ $message }}</div>@enderror</div>
                        <div class="inf-campo ancho"><label>Impacto del proyecto</label><textarea wire:model="proyecto.impacto"></textarea>@error('proyecto.impacto')<div class="inf-error">{{ $message }}</div>@enderror</div>
                        <div class="inf-campo"><label>Campus o plantel</label><input type="text" wire:model="proyecto.campus" maxlength="255"></div>
                        <div class="inf-campo"><label>Municipio</label><input type="text" wire:model="proyecto.municipio" maxlength="150"></div>
                        <div class="inf-campo"><label>Coordenadas (latitud, longitud)</label><input type="text" wire:model="proyecto.coordenadas" placeholder="20.6896, -88.2016">@error('proyecto.coordenadas')<div class="inf-error">{{ $message }}</div>@enderror</div>
                        <div class="inf-campo"><label>Superficie (m²)</label><input type="number" step="0.01" min="0" wire:model="proyecto.superficie_m2">@error('proyecto.superficie_m2')<div class="inf-error">{{ $message }}</div>@enderror</div>
                        <div class="inf-campo"><label>Alumnas beneficiadas</label><input type="number" min="0" wire:model="proyecto.alumnos_mujeres">@error('proyecto.alumnos_mujeres')<div class="inf-error">{{ $message }}</div>@enderror</div>
                        <div class="inf-campo"><label>Alumnos beneficiados</label><input type="number" min="0" wire:model="proyecto.alumnos_hombres">@error('proyecto.alumnos_hombres')<div class="inf-error">{{ $message }}</div>@enderror</div>
                        <div class="inf-campo"><label>Personal académico beneficiado</label><input type="number" min="0" wire:model="proyecto.personal_academico">@error('proyecto.personal_academico')<div class="inf-error">{{ $message }}</div>@enderror</div>
                        <div class="inf-campo"><label>Fecha de inicio</label><input type="date" wire:model="proyecto.fecha_inicio">@error('proyecto.fecha_inicio')<div class="inf-error">{{ $message }}</div>@enderror</div>
                        <div class="inf-campo"><label>Fecha de término</label><input type="date" wire:model="proyecto.fecha_fin">@error('proyecto.fecha_fin')<div class="inf-error">{{ $message }}</div>@enderror</div>
                        <div class="inf-campo"><label>Monto de otros fondos ($)</label><input type="number" step="0.01" min="0" wire:model="proyecto.monto_otros_fondos">@error('proyecto.monto_otros_fondos')<div class="inf-error">{{ $message }}</div>@enderror</div>
                        <div class="inf-campo ancho"><label>Financiamiento previo (año, fondo y monto, si es continuidad)</label><textarea wire:model="proyecto.financiamiento_previo" style="min-height: 60px"></textarea></div>
                    </div>
                    <div class="inf-acciones" style="margin-top: 14px">
                        <button type="button" class="inf-btn inf-btn-azul" wire:click="guardarProyecto" wire:loading.attr="disabled" wire:target="guardarProyecto">Guardar proyecto</button>
                        <button type="button" class="inf-btn" wire:click="cancelarProyecto">Cancelar</button>
                    </div>
                </div>
            @endif

            @forelse ($proyectos as $p)
                @php
                    $faltan = $p->laminasFaltantes();
                    $requeridasP = $requeridas[$p->tipo_proyecto] ?? [];
                @endphp
                <article class="inf-proyecto" wire:key="proyecto-{{ $p->id }}">
                    <div class="inf-proyecto-top">
                        <div>
                            <div class="inf-titulo"><span class="inf-prioridad">{{ $p->prioridad }}</span>{{ $p->nombre }}</div>
                            <span class="inf-chip">{{ $p->tipoTexto() }}</span>
                            <span class="inf-chip {{ $p->completo() ? 'inf-chip-ok' : 'inf-chip-falta' }}">{{ $p->completo() ? '✓ Completo' : 'Incompleto' }}</span>
                        </div>
                        @if ($editable)
                            <div class="inf-acciones">
                                <button type="button" class="inf-btn inf-btn-chico" wire:click="moverProyecto({{ $p->id }}, 'arriba')" @disabled($loop->first) title="Subir prioridad">↑</button>
                                <button type="button" class="inf-btn inf-btn-chico" wire:click="moverProyecto({{ $p->id }}, 'abajo')" @disabled($loop->last) title="Bajar prioridad">↓</button>
                                <button type="button" class="inf-btn inf-btn-chico" wire:click="editarProyecto({{ $p->id }})">Editar</button>
                                <button type="button" class="inf-btn inf-btn-chico inf-btn-rojo" wire:click="eliminarProyecto({{ $p->id }})" wire:confirm="¿Eliminar este proyecto y sus láminas? Sus conceptos de presupuesto se conservan.">Eliminar</button>
                            </div>
                        @endif
                    </div>

                    <div class="inf-cifras">
                        <div class="inf-cifra"><span>Monto FAM</span><strong>{{ $dinero($p->montoFam()) }}</strong></div>
                        <div class="inf-cifra"><span>Otros fondos</span><strong>{{ $dinero($p->monto_otros_fondos) }}</strong></div>
                        <div class="inf-cifra"><span>Total</span><strong>{{ $dinero($p->montoTotal()) }}</strong></div>
                        <div class="inf-cifra"><span>Beneficiarios</span><strong>{{ number_format($p->beneficiarios()) }}</strong></div>
                        <div class="inf-cifra"><span>Superficie</span><strong>{{ $p->superficie_m2 !== null ? number_format((float) $p->superficie_m2, 2) . ' m²' : '—' }}</strong></div>
                        <div class="inf-cifra"><span>Periodo</span><strong>{{ $p->fecha_inicio?->format('d/m/Y') ?? '—' }} – {{ $p->fecha_fin?->format('d/m/Y') ?? '—' }}</strong></div>
                    </div>

                    @if ($p->itemsPresupuesto->isEmpty())
                        <div class="inf-aviso">Sin conceptos de presupuesto. En <a href="{{ $ruta('presupuesto-propuesta') }}">Presupuesto</a> asigna cada concepto a este proyecto.</div>
                    @endif

                    <div style="margin-top: 12px; font-size: 12px; font-weight: 700">Láminas de presentación</div>
                    <div class="inf-laminas">
                        @foreach ($requeridasP as $requerida)
                            @if (in_array($requerida, $faltan, true))
                                <div class="inf-lamina falta">
                                    <span>✗ {{ $laminas[$requerida] }}</span>
                                    @if ($editable)<button type="button" class="inf-btn inf-btn-chico" wire:click="abrirLamina({{ $p->id }}, '{{ $requerida }}')">Subir</button>@endif
                                </div>
                            @endif
                        @endforeach
                        @foreach ($p->laminas as $lamina)
                            <div class="inf-lamina" wire:key="lamina-{{ $lamina->id }}">
                                <span>✓ {{ $lamina->nombre }} <span style="color: #64748B">· {{ $laminas[$lamina->tipo_lamina] ?? 'Lámina' }}</span></span>
                                <span class="inf-acciones">
                                    <button type="button" class="inf-btn inf-btn-chico" wire:click="descargarLamina({{ $lamina->id }})">Descargar</button>
                                    @if ($editable)<button type="button" class="inf-btn inf-btn-chico inf-btn-rojo" wire:click="eliminarLamina({{ $lamina->id }})" wire:confirm="¿Eliminar esta lámina?">Eliminar</button>@endif
                                </span>
                            </div>
                        @endforeach
                        @if ($editable)
                            <div><button type="button" class="inf-btn inf-btn-chico" wire:click="abrirLamina({{ $p->id }})">＋ Otra lámina</button></div>
                        @endif
                    </div>

                    @if ($laminaProyectoId === $p->id)
                        <div class="inf-form" style="margin-top: 12px">
                            <div class="inf-grid">
                                <div class="inf-campo">
                                    <label>Lámina *</label>
                                    <select wire:model="laminaTipo">
                                        <option value="">Elige…</option>
                                        @foreach ($laminas as $clave => $texto)
                                            <option value="{{ $clave }}">{{ $texto }}</option>
                                        @endforeach
                                    </select>
                                    @error('laminaTipo')<div class="inf-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="inf-campo">
                                    <label>Nombre *</label>
                                    <input type="text" wire:model="laminaNombre" maxlength="255">
                                    @error('laminaNombre')<div class="inf-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="inf-campo ancho">
                                    <label>Archivo (PDF o imagen, hasta 15 MB) *</label>
                                    <input type="file" wire:model="laminaArchivo" accept="application/pdf,image/jpeg,image/png,image/webp">
                                    <div wire:loading wire:target="laminaArchivo" style="font-size: 11px; color: #64748B">Subiendo…</div>
                                    @error('laminaArchivo')<div class="inf-error">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="inf-acciones" style="margin-top: 12px">
                                <button type="button" class="inf-btn inf-btn-azul" wire:click="guardarLamina" wire:loading.attr="disabled" wire:target="guardarLamina,laminaArchivo">Guardar lámina</button>
                                <button type="button" class="inf-btn" wire:click="cerrarLamina">Cancelar</button>
                            </div>
                        </div>
                    @endif
                </article>
            @empty
                @if ($proyectoEditando === null)
                    <p style="font-size: 13px; color: #64748B">Aún no hay proyectos. Agrega hasta 5, en orden de prioridad.</p>
                @endif
            @endforelse
        </section>

        {{-- 3. Resumen (Formato A) --}}
        <section class="inf-card">
            <h2>3. Resumen de los proyectos</h2>
            <p class="inf-sub">Como el «Formato A: datos generales y resumen de los proyectos» del FAM.</p>
            <table class="inf-tabla">
                <thead>
                    <tr><th>Prioridad</th><th>Proyecto</th><th>Tipo</th><th class="num">FAM</th><th class="num">Otros fondos</th><th class="num">Total</th></tr>
                </thead>
                <tbody>
                    @forelse ($proyectos as $p)
                        <tr><td>{{ $p->prioridad }}</td><td>{{ $p->nombre }}</td><td>{{ $p->tipoTexto() }}</td><td class="num">{{ $dinero($p->montoFam()) }}</td><td class="num">{{ $dinero($p->monto_otros_fondos) }}</td><td class="num">{{ $dinero($p->montoTotal()) }}</td></tr>
                    @empty
                        <tr><td colspan="6" style="color: #64748B">Sin proyectos.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr><td colspan="3">Total</td><td class="num">{{ $dinero($totalFam) }}</td><td class="num">{{ $dinero($totalOtros) }}</td><td class="num">{{ $dinero($totalFam + $totalOtros) }}</td></tr>
                </tfoot>
            </table>
            @if ($sinProyecto > 0)
                <div class="inf-aviso">{{ $sinProyecto }} {{ $sinProyecto === 1 ? 'concepto del presupuesto no está asignado' : 'conceptos del presupuesto no están asignados' }} a un proyecto: no suman al monto FAM. Asígnalos en <a href="{{ $ruta('presupuesto-propuesta') }}">Presupuesto</a>.</div>
            @endif
        </section>

        @if ($editable)
            <div style="text-align: right">
                <button type="button" class="inf-btn inf-btn-chico" wire:click="usarFormatoInvestigacion" wire:confirm="¿Volver al formato de proyecto de investigación? Los proyectos y láminas se conservan.">Volver al formato de investigación</button>
            </div>
        @endif
    @endif
</div>
</x-filament-panels::page>
