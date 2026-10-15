<?php

namespace App\Filament\Apariencia;

use Filament\Panel;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;

/**
 * Apariencia común de los paneles según el diseño de Figma: sidebar azul
 * marino a toda la altura (sin barra superior de Filament), header propio
 * con búsqueda, notificaciones y menú de usuario (Mi perfil, Cerrar sesión), y tipografías del diseño.
 * Solo usa opciones y render hooks de Filament: no sobrescribe sus vistas.
 */
class AparienciaItsva
{
    /**
     * @param  'docente'|'directivo'|'admin'  $rol
     */
    public static function aplicar(Panel $panel, string $rol): Panel
    {
        return $panel
            ->topbar(false)
            // El header propio ya tiene búsqueda y menú de usuario; sin
            // barra superior Filament los movería a la sidebar.
            ->globalSearch(false)
            ->userMenu(false)
            ->darkMode(false)
            ->font('Inter')
            ->sidebarWidth('15rem')
            ->maxContentWidth(Width::Full)
            ->brandName('Convocatorias ITSVA')
            ->brandLogo(fn () => view('filament.itsva.logo'))
            ->brandLogoHeight('2rem')
            ->defaultAvatarProvider(AvatarIniciales::class)
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn () => view('filament.itsva.estilos')
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn () => view('filament.itsva.rol', ['rol' => $rol])
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn () => view('filament.itsva.usuario', ['rol' => $rol])
            )
            ->renderHook(
                PanelsRenderHook::CONTENT_BEFORE,
                fn () => view('filament.itsva.header')
            );
    }
}
