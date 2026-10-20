{{--
    Editor de texto con formato (tipo Word) para un campo de Livewire:
    fuente, tamaño, negrita, cursiva, subrayado, tachado, color de letra y de
    fondo, listas y alineación. Usa Quill (public/vendor/quill, sin CDN) y
    guarda HTML que el servidor sanea con App\Support\TextoEnriquecido.

    Uso: <x-editor-enriquecido campo="resumen" :editable="$editable" placeholder="…" />
--}}
@props([
    'campo',
    'editable' => true,
    'placeholder' => '',
    'limite' => 10000,
])

@php
    $fuentes = array_keys(\App\Support\TextoEnriquecido::FUENTES);
    $tamanos = \App\Support\TextoEnriquecido::TAMANOS;
    $pilas = \App\Support\TextoEnriquecido::FUENTES;
@endphp

@assets
<link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}">
<script src="{{ asset('vendor/quill/quill.js') }}"></script>
<style>
    .ee { border: 1px solid #DDE3EE; border-radius: 12px; background: #fff; overflow: hidden; }
    .ee:focus-within { border-color: #1A4B8C; box-shadow: 0 0 0 3px rgba(26, 75, 140, .12); }
    .ee .ql-toolbar.ql-snow { border: 0; border-bottom: 1px solid #EEF2F7; background: #F8FAFC; padding: 6px 8px; font-family: 'DM Sans', sans-serif; }
    .ee .ql-container.ql-snow { border: 0; font: 14px/1.6 'DM Sans', sans-serif; color: #1E293B; }
    .ee .ql-editor { min-height: 180px; max-height: 520px; overflow-y: auto; padding: 14px 16px; }
    .ee .ql-editor.ql-blank::before { color: #94A3B8; font-style: normal; left: 16px; }
    .ee .ql-snow .ql-picker.ql-font { width: 140px; }
    .ee .ql-snow .ql-picker.ql-size { width: 78px; }
    .ee .ql-snow .ql-picker.ql-font .ql-picker-label::before, .ee .ql-snow .ql-picker.ql-font .ql-picker-item::before { content: 'Fuente'; }
    .ee .ql-snow .ql-picker.ql-size .ql-picker-label::before, .ee .ql-snow .ql-picker.ql-size .ql-picker-item::before { content: 'Tamaño'; }
    @foreach ($pilas as $nombre => $pila)
        .ee .ql-snow .ql-picker.ql-font [data-value="{{ $nombre }}"]::before { content: '{{ $nombre }}' !important; font-family: {!! $pila !!}; }
    @endforeach
    @foreach ($tamanos as $tamano)
        .ee .ql-snow .ql-picker.ql-size [data-value="{{ $tamano }}"]::before { content: '{{ (int) $tamano }}' !important; }
    @endforeach
    .ee-pie { display: flex; justify-content: flex-end; padding: 6px 12px; border-top: 1px solid #EEF2F7; color: #64748B; font-size: 12px; background: #fff; }
    .ee-pie.excede { color: #B91C1C; font-weight: 700; }
    .ee.deshabilitado .ql-toolbar { display: none; }
    .ee.deshabilitado .ql-editor { background: #F8FAFC; color: #475569; }
</style>
<script>
    // Formatos con estilo en línea (se ven igual en la vista previa y el PDF).
    window.registrarFormatosItsva = function (tamanos, pilas) {
        if (window.formatosItsvaListos) return;
        window.formatosItsvaListos = true;
        const Parchment = Quill.import('parchment');

        // Fuente: se guarda la pila completa (Georgia, serif) y se lee por el primer nombre.
        class Fuente extends Parchment.StyleAttributor {
            add(nodo, valor) {
                if (! pilas[valor]) return false;
                nodo.style.fontFamily = pilas[valor];
                return true;
            }
            value(nodo) {
                const primera = (nodo.style.fontFamily || '').split(',')[0].replace(/["']/g, '').trim();
                return pilas[primera] ? primera : '';
            }
        }
        Quill.register({ 'formats/font': new Fuente('font', 'font-family', { scope: Parchment.Scope.INLINE }) }, true);

        const Tamano = Quill.import('attributors/style/size');
        Tamano.whitelist = tamanos;
        Quill.register(Tamano, true);
        Quill.register(Quill.import('attributors/style/align'), true);
    };

    window.editorEnriquecido = function (campo, editable, placeholder, limite, fuentes, tamanos, pilas) {
        // La instancia de Quill no va en el estado de Alpine: su proxy
        // reactivo rompe la selección del editor.
        let quill = null;

        return {
            caracteres: 0,
            limite,
            init() {
                window.registrarFormatosItsva(tamanos, pilas);

                quill = new Quill(this.$refs.editor, {
                    theme: 'snow',
                    placeholder,
                    readOnly: ! editable,
                    modules: {
                        toolbar: [
                            [{ font: fuentes }, { size: tamanos }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ color: [] }, { background: [] }],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            [{ align: [] }],
                            ['clean'],
                        ],
                    },
                });

                const inicial = this.$wire.get(campo) || '';
                if (inicial) {
                    quill.setContents(quill.clipboard.convert({ html: inicial }), 'silent');
                }
                this.contar();

                let espera = null;
                quill.on('text-change', () => {
                    this.contar();
                    clearTimeout(espera);
                    espera = setTimeout(() => this.sincronizar(), 300);
                });
                // Antes de cualquier acción (guardar, continuar) el valor ya está al día.
                quill.root.addEventListener('blur', () => this.sincronizar());
            },
            contar() {
                this.caracteres = Math.max(0, quill.getText().trim().length);
            },
            sincronizar() {
                const html = quill.getText().trim() === '' ? '' : quill.getSemanticHTML();
                this.$wire.set(campo, html, false);
            },
        };
    };
</script>
@endassets

<div
    wire:ignore
    x-data="editorEnriquecido(@js($campo), @js((bool) $editable), @js($placeholder), @js((int) $limite), @js($fuentes), @js($tamanos), @js($pilas))"
    @class(['ee', 'deshabilitado' => ! $editable])
>
    <div x-ref="editor"></div>
    <div class="ee-pie" :class="{ excede: caracteres > limite }">
        <span><span x-text="caracteres.toLocaleString('es-MX')"></span> / {{ number_format($limite) }} caracteres</span>
    </div>
</div>
