{{-- Paginación de las páginas Livewire con el estilo del diseño (en español). --}}
@if ($paginator->hasPages())
    @php($pagina = $paginator->getPageName())

    <nav class="itsva-paginacion" role="navigation" aria-label="Paginación">
        <span class="itsva-paginacion-info">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </span>

        <div class="itsva-paginacion-botones">
            @if ($paginator->onFirstPage())
                <span class="itsva-pag-btn itsva-pag-off">‹ Anterior</span>
            @else
                <button type="button" class="itsva-pag-btn" wire:click="previousPage('{{ $pagina }}')" wire:loading.attr="disabled">‹ Anterior</button>
            @endif

            @foreach ($elements as $elemento)
                @if (is_string($elemento))
                    <span class="itsva-pag-btn itsva-pag-off">{{ $elemento }}</span>
                @endif

                @if (is_array($elemento))
                    @foreach ($elemento as $numero => $url)
                        @if ($numero == $paginator->currentPage())
                            <span class="itsva-pag-btn itsva-pag-actual" aria-current="page">{{ $numero }}</span>
                        @else
                            <button type="button" class="itsva-pag-btn" wire:click="gotoPage({{ $numero }}, '{{ $pagina }}')">{{ $numero }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button type="button" class="itsva-pag-btn" wire:click="nextPage('{{ $pagina }}')" wire:loading.attr="disabled">Siguiente ›</button>
            @else
                <span class="itsva-pag-btn itsva-pag-off">Siguiente ›</span>
            @endif
        </div>
    </nav>
@endif
