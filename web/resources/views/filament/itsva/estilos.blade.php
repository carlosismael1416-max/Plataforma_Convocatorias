{{--
    Apariencia de los tres paneles según el diseño de Figma
    (Plataforma de Gestión de Convocatorias): sidebar azul marino de
    240 px a toda la altura, header blanco de 64 px, DM Sans en títulos
    e Inter en texto. Se carga después de los estilos de Filament.
--}}
{{-- Bunny Fonts (el proveedor que Filament usa por defecto; no rastrea). --}}
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|inter:400,500,600,700&display=swap" rel="stylesheet">

<style>
    :root {
        --itsva-fondo: #F4F6FA;
        --itsva-texto: #1A2C4E;
        --itsva-texto-suave: #64748B;
        --itsva-borde: #DDE3EE;
        --itsva-primario: #1A4B8C;
        --itsva-acento: #2563EB;
        --itsva-sidebar: #0F2A57;
        --itsva-sidebar-activo: #1E3F7A;
        --itsva-sidebar-texto: #93B4D8;
        --itsva-docente: #3B82F6;
        --itsva-directivo: #7C3AED;
        --itsva-admin: #059669;
        --itsva-display: 'DM Sans', sans-serif;
        --itsva-cuerpo: 'Inter', sans-serif;
    }

    body.fi-body {
        background: var(--itsva-fondo);
        color: var(--itsva-texto);
        font-family: var(--itsva-cuerpo);
    }

    .fi-body h1,
    .fi-body h2,
    .fi-body h3,
    .fi-header-heading {
        font-family: var(--itsva-display);
    }

    /* ---------------- Sidebar ---------------- */

    .fi-sidebar.fi-main-sidebar {
        background: var(--itsva-sidebar);
        border-right: 1px solid rgba(255, 255, 255, .06);
    }

    .fi-sidebar .fi-sidebar-header-ctn,
    .fi-sidebar .fi-sidebar-header {
        background: transparent;
        box-shadow: none;
    }

    .fi-sidebar .fi-sidebar-header {
        min-height: 64px;
        padding: 0 16px;
        border-bottom: 1px solid rgba(255, 255, 255, .08);
    }

    .itsva-logo {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .itsva-logo-marca {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--itsva-acento);
        color: #fff;
        font: 700 14px var(--itsva-display);
    }

    .itsva-logo-texto {
        display: flex;
        flex-direction: column;
        line-height: 1.15;
    }

    .itsva-logo-nombre {
        color: #fff;
        font: 700 14px var(--itsva-display);
    }

    .itsva-logo-sub {
        color: var(--itsva-sidebar-texto);
        font-size: 10px;
    }

    .itsva-rol {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin: 10px 16px 6px;
        padding: 3px 8px;
        border-radius: 6px;
        font: 500 12px var(--itsva-display);
    }

    .itsva-rol-punto {
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: currentColor;
    }

    .itsva-rol-docente { color: var(--itsva-docente); background: rgba(59, 130, 246, .13); }
    .itsva-rol-directivo { color: #A78BFA; background: rgba(124, 58, 237, .18); }
    .itsva-rol-admin { color: #34D399; background: rgba(5, 150, 105, .18); }

    .fi-sidebar .fi-sidebar-nav {
        padding: 4px 8px;
    }

    .fi-sidebar .fi-sidebar-group-label {
        color: rgba(147, 180, 216, .7);
        font-family: var(--itsva-display);
    }

    .fi-sidebar .fi-sidebar-item-btn {
        margin-bottom: 2px;
        padding: 10px 12px;
        border-left: 3px solid transparent;
        border-radius: 8px;
        background: transparent;
    }

    .fi-sidebar .fi-sidebar-item-btn .fi-sidebar-item-label,
    .fi-sidebar .fi-sidebar-item-btn .fi-sidebar-item-icon {
        color: var(--itsva-sidebar-texto);
        font: 500 14px var(--itsva-display);
    }

    .fi-sidebar .fi-sidebar-item-btn:hover {
        background: rgba(255, 255, 255, .06);
    }

    .fi-sidebar .fi-sidebar-item-btn:hover .fi-sidebar-item-label,
    .fi-sidebar .fi-sidebar-item-btn:hover .fi-sidebar-item-icon {
        color: #fff;
    }

    .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
        background: var(--itsva-sidebar-activo);
        border-left-color: var(--itsva-docente);
    }

    .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn .fi-sidebar-item-label,
    .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn .fi-sidebar-item-icon {
        color: #fff;
    }

    .fi-sidebar .fi-sidebar-item-badge-ctn .fi-badge {
        background: #DC2626;
        color: #fff;
    }

    .itsva-usuario {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: auto;
        padding: 16px;
        border-top: 1px solid rgba(255, 255, 255, .08);
        text-decoration: none;
    }

    .itsva-usuario-texto {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .itsva-usuario-nombre,
    .itsva-usuario-correo {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .itsva-usuario-nombre {
        color: #fff;
        font: 500 12px var(--itsva-display);
    }

    .itsva-usuario-correo {
        color: #64A0C8;
        font-size: 10px;
    }

    .itsva-avatar {
        display: inline-flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 999px;
        color: #fff;
        font: 700 11px var(--itsva-display);
    }

    .itsva-avatar-docente { background: var(--itsva-docente); }
    .itsva-avatar-directivo { background: var(--itsva-directivo); }
    .itsva-avatar-admin { background: var(--itsva-admin); }

    /* ---------------- Header ---------------- */

    .itsva-header {
        position: sticky;
        top: 0;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        height: 64px;
        padding: 0 24px;
        background: #fff;
        border-bottom: 1px solid var(--itsva-borde);
    }

    .itsva-header-titulo {
        margin: 0;
        color: var(--itsva-texto);
        font: 700 18px var(--itsva-display);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .itsva-header-acciones {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .itsva-header-busqueda {
        position: relative;
        display: flex;
        align-items: center;
    }

    .itsva-header-busqueda svg {
        position: absolute;
        left: 11px;
        width: 15px;
        height: 15px;
        color: #94A3B8;
        pointer-events: none;
    }

    .itsva-header-busqueda input {
        width: 220px;
        padding: 7px 14px 7px 34px;
        border: 1px solid var(--itsva-borde);
        border-radius: 8px;
        background: var(--itsva-fondo);
        color: var(--itsva-texto);
        font: 400 14px var(--itsva-cuerpo);
        outline: none;
    }

    .itsva-header-busqueda input:focus {
        border-color: var(--itsva-acento);
        box-shadow: 0 0 0 2px rgba(37, 99, 235, .2);
    }

    .itsva-header-campana {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: 1px solid var(--itsva-borde);
        border-radius: 8px;
        color: var(--itsva-texto-suave);
    }

    .itsva-header-campana:hover {
        background: #F8FAFC;
    }

    .itsva-header-campana svg {
        width: 18px;
        height: 18px;
    }

    .itsva-header-contador {
        position: absolute;
        top: -5px;
        right: -5px;
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        border-radius: 999px;
        background: #DC2626;
        color: #fff;
        font: 700 9px/16px var(--itsva-display);
        text-align: center;
    }

    .itsva-header .fi-user-menu-trigger {
        padding: 4px 10px 4px 4px;
        border: 1px solid var(--itsva-borde);
        border-radius: 8px;
    }

    .itsva-header .fi-user-menu-trigger:hover {
        background: #F8FAFC;
    }

    .itsva-menu {
        position: relative;
    }

    .itsva-menu-boton {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 4px 10px 4px 4px;
        border: 1px solid var(--itsva-borde);
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
    }

    .itsva-menu-boton:hover {
        background: #F8FAFC;
    }

    .itsva-menu-boton > svg {
        width: 14px;
        height: 14px;
        color: #94A3B8;
    }

    .itsva-menu-avatar {
        width: 28px;
        height: 28px;
        border-radius: 999px;
    }

    .itsva-menu-texto {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        line-height: 1.2;
    }

    .itsva-menu-nombre {
        margin: 0;
        color: var(--itsva-texto);
        font: 600 14px var(--itsva-display);
    }

    .itsva-menu-rol {
        font: 500 12px var(--itsva-display);
    }

    .itsva-menu-rol-docente { color: var(--itsva-acento); }
    .itsva-menu-rol-directivo { color: var(--itsva-directivo); }
    .itsva-menu-rol-admin { color: var(--itsva-admin); }

    .itsva-menu-panel {
        position: absolute;
        right: 0;
        z-index: 50;
        width: 208px;
        margin-top: 8px;
        padding-bottom: 4px;
        border: 1px solid var(--itsva-borde);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 20px 25px -5px rgba(15, 23, 42, .12);
    }

    .itsva-menu-cabecera {
        padding: 12px;
        margin-bottom: 4px;
        border-bottom: 1px solid var(--itsva-borde);
    }

    .itsva-menu-correo {
        margin: 2px 0 0;
        color: #94A3B8;
        font-size: 12px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .itsva-menu-opcion {
        display: block;
        width: calc(100% - 8px);
        margin: 0 4px;
        padding: 8px 12px;
        border: 0;
        border-radius: 8px;
        background: none;
        color: var(--itsva-texto);
        font: 400 14px var(--itsva-display);
        text-align: left;
        text-decoration: none;
        cursor: pointer;
    }

    .itsva-menu-opcion:hover {
        background: #F8FAFC;
    }

    .itsva-menu-salir {
        color: #DC2626;
    }

    .itsva-menu-salir:hover {
        background: #FEF2F2;
    }

    @media (max-width: 1023px) {
        .itsva-menu-texto {
            display: none;
        }

        .itsva-header {
            padding-left: 64px;
        }

        .itsva-header-busqueda {
            display: none;
        }
    }

    /* ---------------- Contenido ---------------- */

    .fi-main {
        padding: 24px;
    }

    /* Filament reserva espacio para el título aunque esté vacío. */
    .fi-page-header-main-ctn {
        padding-block: 0;
        row-gap: 24px;
    }

    .fi-header-heading {
        color: var(--itsva-texto);
        font-size: 22px;
        font-weight: 700;
    }

    .fi-header-subheading {
        color: var(--itsva-texto-suave);
    }

    /* Botón primario del diseño (Filament elegía un tono claro por contraste). */
    .fi-btn:not(.fi-outlined).fi-color.fi-color-primary:not(label) {
        --bg: #1A4B8C;
        --hover-bg: #163F78;
        --text: #fff;
        --hover-text: #fff;
    }

    /* ---------------- Paginación ---------------- */

    .itsva-paginacion {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 16px;
    }

    .itsva-paginacion-info {
        color: var(--itsva-texto-suave);
        font-size: 13px;
    }

    .itsva-paginacion-botones {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .itsva-pag-btn {
        min-width: 36px;
        height: 36px;
        padding: 0 12px;
        border: 1px solid var(--itsva-borde);
        border-radius: 8px;
        background: #fff;
        color: var(--itsva-texto);
        font: 600 13px var(--itsva-display);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .itsva-pag-btn:hover:not(.itsva-pag-off):not(.itsva-pag-actual) {
        border-color: var(--itsva-primario);
        color: var(--itsva-primario);
    }

    .itsva-pag-actual {
        border-color: var(--itsva-primario);
        background: var(--itsva-primario);
        color: #fff;
    }

    .itsva-pag-off {
        color: #CBD5E1;
        cursor: default;
    }
</style>
