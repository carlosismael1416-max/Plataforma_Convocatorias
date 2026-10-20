{{-- Lista de etiquetas con «Ver todos»: muestra las primeras $visibles. Uso:
     View::make(...)->viewData(['elementos' => [...], 'visibles' => 8, 'vacio' => '...']) --}}
@php
    $elementos = array_values($elementos ?? []);
    $visibles ??= 8;
@endphp
<div x-data="{ todos: false }">
    @if ($elementos === [])
        <span class="det-vacio">{{ $vacio ?? 'Sin elementos' }}</span>
    @else
        <div class="det-permisos">
            @foreach ($elementos as $i => $texto)
                <span class="det-permiso" @if ($i >= $visibles) x-show="todos" x-cloak @endif>✓ {{ $texto }}</span>
            @endforeach
        </div>
        @if (count($elementos) > $visibles)
            <button type="button" class="det-mas" x-on:click="todos = ! todos"
                x-text="todos ? 'Ver menos' : 'Ver todos ({{ count($elementos) }})'">Ver todos ({{ count($elementos) }})</button>
        @endif
    @endif
</div>
