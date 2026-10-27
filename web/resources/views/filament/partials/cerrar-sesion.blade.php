{{-- Fila «Cerrar sesión» de la sección Seguridad del perfil (los tres paneles). --}}
<div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 16px; padding-top: 16px; border-top: 1px solid #EEF2F7">
    <div>
        <div style="font-size: 13px; font-weight: 700; color: #0F1827">Cerrar sesión</div>
        <div style="font-size: 12px; color: #64748B">Sal de tu cuenta en este navegador (también de las otras cuentas que hayas agregado).</div>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="display: inline-flex; align-items: center; gap: 6px; min-height: 38px; padding: 0 16px; border: 1px solid #FECACA; border-radius: 10px; background: #fff; color: #B91C1C; font: 700 13px 'DM Sans', sans-serif; cursor: pointer">
            ⏻ Cerrar sesión
        </button>
    </form>
</div>
