<x-filament-panels::page>
    <style>
        .admin-roles {
            --text: #0F1827;
            --muted: #64748B;
            --primary: #059669;
            --primary-dark: #047857;
            --primary-soft: #D1FAE5;
            --border: #DDE3EE;
            color: var(--text);
        }

        .admin-roles * {
            box-sizing: border-box;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 22px;
        }

        .page-title {
            margin: 0;
            font-size: 26px;
            font-weight: 850;
        }

        .page-subtitle {
            margin-top: 7px;
            color: var(--muted);
            font-size: 14px;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 11px;
            border-radius: 999px;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 12px;
            font-weight: 850;
        }

        .role-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .stat {
            padding: 18px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: white;
        }

        .stat-icon {
            width: 38px;
            height: 38px;
            margin-bottom: 12px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 11px;
            font-weight: 900;
        }

        .stat-value {
            font-size: 25px;
            font-weight: 900;
        }

        .stat-label {
            margin-top: 3px;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
        }

        .stat-help {
            margin-top: 4px;
            color: #94A3B8;
            font-size: 12px;
        }

        .section-title {
            margin-bottom: 13px;
            font-size: 15px;
            font-weight: 850;
        }

        .role-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .role-card {
            padding: 19px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: white;
        }

        .role-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .role-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 900;
        }

        .docente {
            background: #DBEAFE;
            color: #1D4ED8;
        }

        .directivo {
            background: #F3E8FF;
            color: #7C3AED;
        }

        .admin {
            background: #D1FAE5;
            color: #047857;
        }

        .status {
            padding: 5px 8px;
            border-radius: 999px;
            background: #D1FAE5;
            color: #047857;
            font-size: 12px;
            font-weight: 850;
        }

        .role-name {
            margin-top: 14px;
            color: #1E293B;
            font-size: 15px;
            font-weight: 900;
        }

        .role-description {
            min-height: 45px;
            margin-top: 5px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .permissions-title {
            margin-top: 17px;
            padding-top: 15px;
            border-top: 1px solid #EEF2F7;
            color: #64748B;
            font-size: 12px;
            font-weight: 850;
            text-transform: uppercase;
        }

        .permissions {
            display: flex;
            flex-direction: column;
            gap: 7px;
            margin-top: 10px;
        }

        .permission {
            display: flex;
            gap: 8px;
            align-items: flex-start;
            color: #475569;
            font-size: 12px;
        }

        .check {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #D1FAE5;
            color: #047857;
            font-size: 11px;
            font-weight: 900;
        }

        .more {
            display: inline-block;
            margin-top: 11px;
            color: var(--primary);
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .more:hover {
            text-decoration: underline;
        }

        .notice {
            display: flex;
            gap: 11px;
            padding: 14px;
            margin-bottom: 22px;
            border: 1px solid #A7F3D0;
            border-radius: 11px;
            background: #ECFDF5;
            color: #047857;
            font-size: 12px;
            line-height: 1.55;
        }

        .notice-icon {
            width: 24px;
            height: 24px;
            flex: 0 0 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #D1FAE5;
            font-weight: 900;
        }

        .table-card {
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: white;
        }

        .table-header {
            margin-bottom: 18px;
        }

        .table-title {
            font-size: 15px;
            font-weight: 850;
        }

        .table-description {
            margin-top: 4px;
            color: var(--muted);
            font-size: 11px;
        }

        @media (max-width: 1000px) {
            .stats,
            .role-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 650px) {
            .page-header {
                flex-direction: column;
            }

            .table-card {
                padding: 14px;
            }
        }
    
    .ver-mas { margin-top: 6px; padding: 6px 0; border: 0; background: transparent; color: #047857; font-size: 13px; font-weight: 700; cursor: pointer; text-align: left; }
    .ver-mas:hover { text-decoration: underline; }
    .role-links { display: flex; gap: 16px; flex-wrap: wrap; margin-top: auto; padding-top: 12px; }
</style>

    <div class="admin-roles">

        <div class="page-header">

            <div>
                <h1 class="page-title">
                    Roles y Permisos
                </h1>

                <div class="page-subtitle">
                    Administra los perfiles de acceso y las funciones disponibles para cada tipo de usuario.
                </div>
            </div>

            <div class="role-badge">
                <span class="role-dot"></span>
                Administrador
            </div>

        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-icon">{!! \App\Filament\Apariencia\Iconos::svg('escudo', 20) !!}</div>
                <div class="stat-value">{{ $totalRoles }}</div>
                <div class="stat-label">Roles registrados</div>
                <div class="stat-help">{{ $rolesActivos }} activo(s)</div>
            </div>

            <div class="stat">
                <div class="stat-icon">{!! \App\Filament\Apariencia\Iconos::svg('usuarios', 20) !!}</div>
                <div class="stat-value">{{ $usuariosConRol }}</div>
                <div class="stat-label">Usuarios asignados</div>
                <div class="stat-help">Cuentas con un rol asignado</div>
            </div>

            <div class="stat">
                <div class="stat-icon">{!! \App\Filament\Apariencia\Iconos::svg('aprobado', 20) !!}</div>
                <div class="stat-value">{{ $totalPermisos }}</div>
                <div class="stat-label">Permisos definidos</div>
                <div class="stat-help">Registrados en el catálogo de permisos</div>
            </div>
        </div>

        <div class="section-title">
            Permisos por rol
        </div>

        <div class="role-grid">
            @foreach ($roles as $rol)
                @php
                    $clase = ['DOCENTE' => 'docente', 'DIRECTIVO' => 'directivo', 'ADMINISTRADOR' => 'admin'][$rol->nombre] ?? 'admin';
                    $inicial = ['DOCENTE' => 'D', 'DIRECTIVO' => 'DI', 'ADMINISTRADOR' => 'A', 'SISTEMA' => 'S'][$rol->nombre] ?? mb_substr($rol->nombre, 0, 1);
                @endphp

                <article class="role-card">
                    <div class="role-card-top">
                        <div class="role-icon {{ $clase }}">{{ $inicial }}</div>
                        <span class="status">{{ $rol->estado ? 'Activo' : 'Inactivo' }}</span>
                    </div>

                    <div class="role-name">
                        {{ \Illuminate\Support\Str::ucfirst(mb_strtolower($rol->nombre)) }}
                    </div>

                    <div class="role-description">
                        {{ $rol->descripcion ?: 'Sin descripción.' }}
                        · {{ $rol->users_count }} usuario(s)
                    </div>

                    <div class="permissions-title">
                        Permisos ({{ $rol->permisos->count() }})
                    </div>

                    {{-- Solo los primeros 5; el resto con «Ver más» (antes la lista ocupaba toda la tarjeta). --}}
                    <div class="permissions" x-data="{ todos: false }">
                        @forelse ($rol->permisos->sortBy('descripcion')->values() as $i => $permiso)
                            <div class="permission" title="{{ $permiso->descripcion }}" @if ($i >= 5) x-show="todos" x-cloak @endif>
                                <span class="check">✓</span>
                                {{ $permiso->descripcion ?: \Illuminate\Support\Str::ucfirst(str_replace('_', ' ', $permiso->nombre)) }}
                            </div>
                        @empty
                            <div class="permission">Sin permisos asignados.</div>
                        @endforelse

                        @if ($rol->permisos->count() > 5)
                            <button type="button" class="ver-mas" x-on:click="todos = ! todos"
                                x-text="todos ? 'Ver menos' : 'Ver más permisos ({{ $rol->permisos->count() - 5 }})'">
                                Ver más permisos ({{ $rol->permisos->count() - 5 }})
                            </button>
                        @endif
                    </div>

                    <div class="role-links">
                        <a class="more" href="{{ \App\Filament\Resources\Roles\RoleResource::getUrl('view', ['record' => $rol]) }}">Ver detalle</a>
                        <a class="more" href="{{ \App\Filament\Resources\Roles\RoleResource::getUrl('edit', ['record' => $rol]) }}">Editar permisos →</a>
                    </div>
                </article>
            @endforeach
        </div>

        <section class="table-card">

            <div class="table-header">

                <div class="table-title">
                    Roles registrados
                </div>

                <div class="table-description">
                    Consulta los roles existentes, su estado y sus datos administrativos.
                </div>

            </div>

            {{ $this->table }}

        </section>

    </div>
</x-filament-panels::page>
