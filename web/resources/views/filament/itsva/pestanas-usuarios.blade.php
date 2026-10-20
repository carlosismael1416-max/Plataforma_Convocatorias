{{-- Pestañas «Usuarios / Departamentos» del admin. Uso:
     @include('filament.itsva.pestanas-usuarios', ['activa' => 'usuarios'|'departamentos']) --}}
<nav class="itsva-pestanas" aria-label="Secciones de usuarios" style="margin-bottom: 16px">
    <style>
        .itsva-pestanas { display: flex; gap: 6px; width: fit-content; max-width: 100%; padding: 5px; background: #EEF2F7; border-radius: 12px; overflow-x: auto; }
        .itsva-pestana { display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 9px; color: #475569; font-size: 13px; font-weight: 700; text-decoration: none; white-space: nowrap; }
        .itsva-pestana:hover { color: #047857; }
        .itsva-pestana.activa { background: #fff; color: #047857; box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
        .itsva-pestana-n { padding: 1px 8px; border-radius: 999px; background: #E2E8F0; font-size: 11px; }
        .itsva-pestana.activa .itsva-pestana-n { background: #D1FAE5; }
    </style>
    <a href="{{ \App\Filament\Resources\Users\UserResource::getUrl('index') }}" @class(['itsva-pestana', 'activa' => $activa === 'usuarios']) @if ($activa === 'usuarios') aria-current="page" @endif>
        👥 Usuarios <span class="itsva-pestana-n">{{ \App\Models\User::count() }}</span>
    </a>
    <a href="{{ \App\Filament\Resources\Departamentos\DepartamentoResource::getUrl('index') }}" @class(['itsva-pestana', 'activa' => $activa === 'departamentos']) @if ($activa === 'departamentos') aria-current="page" @endif>
        🏢 Departamentos <span class="itsva-pestana-n">{{ \App\Models\Departamento::count() }}</span>
    </a>
</nav>
