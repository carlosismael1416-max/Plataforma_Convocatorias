{{-- Interruptor del buscador y motor de extracción. Requiere los datos de ControlaMotor::datosInterruptor(). --}}
<section class="itsva-interruptor {{ $motorActivo ? 'itsva-interruptor-on' : 'itsva-interruptor-off' }}">
    <div class="itsva-interruptor-info">
        <span class="itsva-interruptor-punto"></span>
        <div>
            <p class="itsva-interruptor-titulo">
                Buscador y motor de extracción: {{ $motorActivo ? 'encendidos' : 'apagados' }}
            </p>
            <p class="itsva-interruptor-sub">
                @if ($motorActivo)
                    Buscan, extraen y publican convocatorias en cada ciclo (00:00, 06:00, 12:00 y 18:00).
                @else
                    No se buscan ni se extraen convocatorias; lo ya publicado sigue visible.
                @endif
                @if ($motorCambiadoPor)
                    Último cambio: {{ $motorCambiadoPor }}, {{ $motorCambiadoEn }}.
                @endif
            </p>
        </div>
    </div>

    @if ($motorActivo)
        <button
            type="button"
            class="itsva-interruptor-btn itsva-interruptor-apagar"
            wire:click="cambiarMotor(false)"
            wire:confirm="¿Apagar el buscador y el motor de extracción? No se buscarán ni extraerán convocatorias hasta que los enciendas."
            wire:loading.attr="disabled"
            wire:target="cambiarMotor"
        >
            Apagar motor
        </button>
    @else
        <button
            type="button"
            class="itsva-interruptor-btn itsva-interruptor-encender"
            wire:click="cambiarMotor(true)"
            wire:loading.attr="disabled"
            wire:target="cambiarMotor"
        >
            Encender motor
        </button>
    @endif
</section>
