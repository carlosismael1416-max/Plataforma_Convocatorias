{{-- Estilo de las pestañas del admin (Usuarios, Convocatorias). --}}
@once
<style>
    .itsva-pestanas { display: flex; gap: 6px; width: fit-content; max-width: 100%; padding: 5px; background: #EEF2F7; border-radius: 12px; overflow-x: auto; }
    .itsva-pestana { display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 9px; color: #475569; font-size: 13px; font-weight: 700; text-decoration: none; white-space: nowrap; }
    .itsva-pestana:hover { color: #047857; }
    .itsva-pestana.activa { background: #fff; color: #047857; box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
    .itsva-pestana-n { padding: 1px 8px; border-radius: 999px; background: #E2E8F0; font-size: 11px; }
    .itsva-pestana.activa .itsva-pestana-n { background: #D1FAE5; }
</style>
@endonce
