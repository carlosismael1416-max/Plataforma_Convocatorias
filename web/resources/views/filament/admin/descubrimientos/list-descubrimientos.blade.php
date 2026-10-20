<x-filament-panels::page>
@php
    $activa = $this->activeTab ?? 'todos';
    $colores = [
        'green' => ['#D1FAE5', '#047857'], 'yellow' => ['#FEF3C7', '#B45309'], 'blue' => ['#DBEAFE', '#1D4ED8'],
        'gray' => ['#F1F5F9', '#475569'], 'red' => ['#FEE2E2', '#B91C1C'],
    ];
@endphp

<style>
    .desc-cab h2 { margin: 0; color: #0F1827; font-size: 22px; font-weight: 800; }
    .desc-cab p { margin: 6px 0 18px; color: #64748B; font-size: 14px; max-width: 820px; line-height: 1.5; }
    .desc-tarjetas { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
    .desc-tarjeta { display: block; width: 100%; text-align: left; font: inherit; cursor: pointer; padding: 16px; border: 1px solid #DDE3EE; border-radius: 14px; background: #fff; box-shadow: 0 1px 2px rgba(15, 24, 39, .03); transition: box-shadow .15s, transform .15s, border-color .15s; }
    .desc-tarjeta:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(15, 24, 39, .08); }
    .desc-tarjeta.activa { border-color: #059669; box-shadow: 0 0 0 2px rgba(5, 150, 105, .18); }
    .desc-icono { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }
    .desc-valor { color: #0F1827; font-size: 24px; font-weight: 800; line-height: 1.1; }
    .desc-etq { margin-top: 2px; color: #0F1827; font-size: 13px; font-weight: 700; }
    .desc-ayuda { margin-top: 3px; color: #94A3B8; font-size: 11px; }
    .desc-aviso { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 18px; padding: 12px 16px; border: 1px solid #BFDBFE; border-radius: 12px; background: #EFF6FF; color: #1E3A8A; font-size: 13px; line-height: 1.5; }
    .desc-tabla { padding: 20px; border: 1px solid #DDE3EE; border-radius: 16px; background: #fff; }
    .desc-tabla h3 { margin: 0; color: #0F1827; font-size: 16px; font-weight: 800; }
    .desc-tabla > p { margin: 4px 0 14px; color: #64748B; font-size: 12px; }
    @media (max-width: 1100px) { .desc-tarjetas { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 640px) { .desc-tarjetas { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>

@include('filament.itsva.pestanas-motor', ['activa' => 'descubrimientos'])

<div class="desc-cab">
    <h2>Descubrimientos del buscador</h2>
    <p>Candidatos que el motor encontró en internet. No son convocatorias todavía: el motor los evalúa y, si cumplen, los extrae, valida y publica; entonces aparecen en Convocatorias. Esta lista es de consulta y se maneja sola.</p>
</div>

<div class="desc-tarjetas">
    @foreach ($tarjetas as [$clave, $etiqueta, $valor, $ayuda, $icono, $color])
        <button type="button" wire:click="$set('activeTab', '{{ $clave }}')" @class(['desc-tarjeta', 'activa' => $activa === $clave]) aria-pressed="{{ $activa === $clave ? 'true' : 'false' }}">
            <span class="desc-icono" style="background: {{ $colores[$color][0] }}; color: {{ $colores[$color][1] }}">{!! \App\Filament\Apariencia\Iconos::svg($icono, 18) !!}</span>
            <div class="desc-valor">{{ $valor }}</div>
            <div class="desc-etq">{{ $etiqueta }}</div>
            <div class="desc-ayuda">{{ $ayuda }}</div>
        </button>
    @endforeach
</div>

<div class="desc-aviso">
    <span aria-hidden="true">ℹ️</span>
    <span>Que muchos queden <strong>descartados</strong> es normal: el buscador revisa noticias y portales, y la mayoría son notas, eventos u ofertas de otros estados. Las fuentes oficiales (SECIHTI y Secretaría de Ciencia de Yucatán) entran directo como convocatorias.</span>
</div>

<section class="desc-tabla">
    <h3>{{ collect($tarjetas)->firstWhere(0, $activa)[1] ?? 'Descubrimientos' }}</h3>
    <p>Toda la fila abre el detalle: por qué se aceptó o descartó, enlaces y fechas.</p>
    {{ $this->table }}
</section>
</x-filament-panels::page>
