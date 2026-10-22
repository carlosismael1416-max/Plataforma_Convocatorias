{{-- Pestañas «Usuarios / Departamentos» del admin. Uso:
     @include('filament.itsva.pestanas-usuarios', ['activa' => 'usuarios'|'departamentos']) --}}
@include('filament.itsva.pestanas-estilos')
<nav class="itsva-pestanas" aria-label="Secciones de usuarios" style="margin-bottom: 16px">
    <a href="{{ \App\Filament\Resources\Users\UserResource::getUrl('index') }}" @class(['itsva-pestana', 'activa' => $activa === 'usuarios']) @if ($activa === 'usuarios') aria-current="page" @endif>
        👥 Usuarios <span class="itsva-pestana-n">{{ \App\Models\User::count() }}</span>
    </a>
    <a href="{{ \App\Filament\Resources\Departamentos\DepartamentoResource::getUrl('index') }}" @class(['itsva-pestana', 'activa' => $activa === 'departamentos']) @if ($activa === 'departamentos') aria-current="page" @endif>
        🏢 Departamentos <span class="itsva-pestana-n">{{ \App\Models\Departamento::count() }}</span>
    </a>
</nav>
