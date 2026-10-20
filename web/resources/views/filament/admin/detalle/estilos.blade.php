{{-- Estilos compartidos de las cabeceras de detalle (usuario, rol). --}}
<style>
    .det-cab { position: relative; overflow: hidden; display: flex; gap: 20px; align-items: center; padding: 24px 26px; border-radius: 18px; background: linear-gradient(135deg, #0F2D5C 0%, #1A4B8C 60%, #2563EB 100%); color: #fff; }
    .det-cab::after { content: ''; position: absolute; right: -60px; top: -80px; width: 240px; height: 240px; border-radius: 50%; background: rgba(255, 255, 255, .07); }
    .det-avatar { position: relative; z-index: 1; width: 72px; height: 72px; flex-shrink: 0; border-radius: 20px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, .16); border: 1px solid rgba(255, 255, 255, .25); font: 800 26px 'DM Sans', sans-serif; letter-spacing: .02em; }
    .det-info { position: relative; z-index: 1; min-width: 0; flex: 1; }
    .det-info h2 { margin: 0; font: 800 22px 'DM Sans', sans-serif; }
    .det-info p { margin: 4px 0 0; color: #DBEAFE; font-size: 14px; }
    .det-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
    .det-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 11px; border-radius: 999px; background: rgba(255, 255, 255, .14); border: 1px solid rgba(255, 255, 255, .22); font-size: 12px; font-weight: 700; }
    .det-chip-ok { background: rgba(16, 185, 129, .25); border-color: rgba(110, 231, 183, .5); }
    .det-chip-no { background: rgba(239, 68, 68, .25); border-color: rgba(252, 165, 165, .5); }
    .det-cifras { position: relative; z-index: 1; display: flex; gap: 10px; }
    .det-cifra { min-width: 92px; padding: 10px 14px; border-radius: 14px; background: rgba(255, 255, 255, .12); border: 1px solid rgba(255, 255, 255, .2); text-align: center; }
    .det-cifra strong { display: block; font: 800 22px 'DM Sans', sans-serif; line-height: 1.1; }
    .det-cifra span { font-size: 11px; color: #DBEAFE; }
    .det-permisos { display: flex; flex-wrap: wrap; gap: 8px; }
    .det-permiso { display: inline-flex; align-items: center; gap: 6px; padding: 6px 11px; border-radius: 10px; background: #ECFDF5; border: 1px solid #A7F3D0; color: #047857; font-size: 13px; font-weight: 600; }
    .det-mas { margin-top: 12px; border: 0; background: transparent; color: #1A4B8C; font-weight: 700; font-size: 13px; cursor: pointer; padding: 0; }
    .det-vacio { color: #94A3B8; font-size: 13px; font-style: italic; }
    @media (max-width: 760px) { .det-cab { flex-direction: column; align-items: flex-start; } .det-cifras { flex-wrap: wrap; } }
</style>
