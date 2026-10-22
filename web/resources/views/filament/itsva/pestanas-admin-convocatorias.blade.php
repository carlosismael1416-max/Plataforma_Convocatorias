{{-- Pestañas «Convocatorias / Organismos / Categorías» del admin. Uso:
     @include('filament.itsva.pestanas-admin-convocatorias', ['activa' => 'convocatorias'|'organismos'|'categorias']) --}}
@include('filament.itsva.pestanas-estilos')
<nav class="itsva-pestanas" aria-label="Secciones de convocatorias" style="margin-bottom: 16px">
    @foreach ([
        ['convocatorias', '📚 Convocatorias', \App\Filament\Resources\Convocatorias\ConvocatoriaResource::getUrl('index'), \App\Models\Convocatoria::count()],
        ['organismos', '🏛️ Organismos', \App\Filament\Resources\Organismos\OrganismoResource::getUrl('index'), \App\Models\Organismo::count()],
        ['categorias', '🏷️ Categorías', \App\Filament\Resources\Categorias\CategoriaResource::getUrl('index'), \App\Models\Categoria::count()],
    ] as [$clave, $etiqueta, $url, $total])
        <a href="{{ $url }}" @class(['itsva-pestana', 'activa' => $activa === $clave]) @if ($activa === $clave) aria-current="page" @endif>
            {{ $etiqueta }} <span class="itsva-pestana-n">{{ $total }}</span>
        </a>
    @endforeach
</nav>
