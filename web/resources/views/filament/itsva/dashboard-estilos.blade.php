{{-- Estilos de los dashboards (docente, directivo y admin) según Figma. --}}
    <style>
        .dd { color: #1A2C4E; font-family: 'Inter', sans-serif; }
        .dd h1, .dd h2, .dd h3, .dd .display { font-family: 'DM Sans', sans-serif; }

        .dd-hero {
            position: relative;
            overflow: hidden;
            margin-bottom: 32px;
            padding: 28px 32px;
            border-radius: 16px;
            background: linear-gradient(135deg, #0A1E42 0%, #0F2A57 45%, #1A4B8C 100%);
        }

        .dd-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: .1;
            background-image: radial-gradient(circle, #fff 1px, transparent 0);
            background-size: 28px 28px;
        }

        .dd-hero-deco {
            position: absolute;
            top: 50%;
            right: 32px;
            transform: translateY(-50%);
            color: #fff;
            opacity: .08;
            pointer-events: none;
        }

        .dd-hero > div { position: relative; }

        .dd-hero-pre {
            margin: 0 0 8px;
            color: #60A5FA;
            font: 600 12px 'DM Sans', sans-serif;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .dd-hero h1 {
            margin: 0 0 4px;
            color: #fff;
            font-size: 30px;
            font-weight: 700;
        }

        .dd-hero-sub {
            margin: 0 0 24px;
            color: #93B4D8;
            font-size: 14px;
        }

        .dd-hero-acciones { display: flex; flex-wrap: wrap; gap: 12px; }

        .dd-hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 12px;
            background: rgba(255, 255, 255, .1);
            color: #fff;
            font: 600 14px 'DM Sans', sans-serif;
            text-decoration: none;
        }

        .dd-hero-btn:hover { background: rgba(255, 255, 255, .2); }

        .dd-hero-btn-primario { border-color: #2563EB; background: #2563EB; }
        .dd-hero-btn-primario:hover { background: #1D4ED8; }

        .dd-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        @media (min-width: 1280px) {
            .dd-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        .dd-card {
            border: 1px solid #DDE3EE;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        }

        .dd-stat {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 20px;
            color: inherit;
            text-decoration: none;
            transition: border-color .15s, box-shadow .15s, transform .15s;
        }

        .dd-stat:hover { border-color: #1A4B8C; box-shadow: 0 4px 12px rgba(26, 75, 140, .12); transform: translateY(-1px); }
        .dd-stat:focus-visible { outline: 2px solid #2563EB; outline-offset: 2px; }

        .dd-stat-icono {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
        }

        .dd-azul { background: #E8EEF8; color: #1A4B8C; }
        .dd-morado { background: #EDE9FE; color: #7C3AED; }
        .dd-verde { background: #D1FAE5; color: #059669; }
        .dd-ambar { background: #FEF3C7; color: #D97706; }

        .dd-stat-etiqueta { margin: 0 0 4px; color: #64748B; font: 400 14px 'DM Sans', sans-serif; }
        .dd-stat-valor { margin: 0; font: 700 24px 'DM Sans', sans-serif; }
        .dd-stat-sub { margin: 2px 0 0; color: #94A3B8; font-size: 12px; }

        .dd-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 24px; }

        @media (min-width: 1280px) {
            .dd-grid { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
        }

        .dd-columna { display: flex; flex-direction: column; gap: 24px; }

        .dd-seccion {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .dd-seccion h2 { margin: 0; font-size: 18px; font-weight: 700; }
        .dd-seccion p { margin: 2px 0 0; color: #64748B; font-size: 14px; }

        .dd-enlace {
            color: #2563EB;
            font: 600 14px 'DM Sans', sans-serif;
            text-decoration: none;
            white-space: nowrap;
        }

        .dd-tarjetas { display: grid; grid-template-columns: minmax(0, 1fr); gap: 16px; }

        @media (min-width: 640px) {
            .dd-tarjetas { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        .dd-conv { padding: 20px; transition: box-shadow .15s; }
        .dd-conv:hover { box-shadow: 0 10px 15px -3px rgba(15, 23, 42, .08); }

        .dd-conv-cabecera { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
        .dd-conv-etiquetas { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 4px; }

        .dd-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 6px;
            font: 600 10px 'DM Sans', sans-serif;
            letter-spacing: .05em;
        }

        .dd-badge-publicada { background: #DBEAFE; color: #1A4B8C; }
        .dd-badge-porcerrar { background: #FEF3C7; color: #D97706; }
        .dd-badge-borrador { background: #F1F5F9; color: #64748B; }
        .dd-badge-revisando { background: #DBEAFE; color: #2563EB; }
        .dd-badge-aprobada { background: #D1FAE5; color: #059669; }
        .dd-badge-enviada { background: #E0E7FF; color: #4338CA; }
        .dd-badge-rechazada { background: #FEE2E2; color: #DC2626; }

        .dd-categoria {
            padding: 2px 8px;
            border-radius: 999px;
            background: #F0F4FB;
            color: #64748B;
            font: 400 12px 'DM Sans', sans-serif;
        }

        .dd-conv h3 {
            display: -webkit-box;
            overflow: hidden;
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .dd-conv-organismo { margin: 4px 0 0; color: #64748B; font-size: 12px; }

        .dd-fav {
            flex-shrink: 0;
            padding: 0;
            border: 0;
            background: none;
            color: #CBD5E1;
            cursor: pointer;
        }

        .dd-fav-activo { color: #E11D48; }

        .dd-datos { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px; }
        .dd-dato { padding: 8px; border-radius: 8px; background: #F8FAFC; }
        .dd-dato-alerta { background: #FEF3C7; }
        .dd-dato-titulo { margin: 0; color: #94A3B8; font: 400 12px 'DM Sans', sans-serif; }
        .dd-dato-valor { margin: 0; font: 700 14px 'DM Sans', sans-serif; }
        .dd-dato-monto { color: #1A4B8C; }
        .dd-dato-alerta .dd-dato-valor { color: #D97706; }

        .dd-botones { display: flex; gap: 8px; }

        .dd-btn {
            display: inline-flex;
            flex: 1;
            align-items: center;
            justify-content: center;
            padding: 5px 12px;
            border: 0;
            border-radius: 8px;
            font: 600 12px 'DM Sans', sans-serif;
            text-decoration: none;
            cursor: pointer;
        }

        .dd-btn:hover { opacity: .85; }
        .dd-btn-primario { background: #1A4B8C; color: #fff; }
        .dd-btn-secundario { background: #E8EEF8; color: #1A4B8C; }
        .dd-btn-secundario[disabled] { cursor: default; opacity: .6; }

        .dd-lista > * + * { border-top: 1px solid #F0F4FB; }

        .dd-venc {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: inherit;
            text-decoration: none;
        }

        .dd-venc:hover, .dd-prop:hover { background: rgba(249, 250, 251, .7); }
        .dd-venc-dias { width: 40px; text-align: center; }
        .dd-venc-num { font: 700 18px/1 'DM Sans', sans-serif; color: #1A4B8C; }
        .dd-venc-num-pronto { color: #D97706; }
        .dd-venc-txt { color: #94A3B8; font-size: 12px; }
        .dd-venc-info { flex: 1; min-width: 0; }

        .dd-venc-titulo {
            overflow: hidden;
            margin: 0;
            font: 600 12px 'DM Sans', sans-serif;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dd-venc-fecha { margin: 0; color: #94A3B8; font-size: 12px; }
        .dd-punto { flex-shrink: 0; width: 8px; height: 8px; border-radius: 999px; }
        .dd-punto-alta { background: #DC2626; }
        .dd-punto-media { background: #D97706; }
        .dd-punto-baja { background: #059669; }

        .dd-prop { display: block; padding: 16px; color: inherit; text-decoration: none; }
        .dd-prop-cabecera { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 8px; }
        .dd-prop-titulo { margin: 0; font: 600 12px/1.35 'DM Sans', sans-serif; }
        .dd-prop-conv { margin: 0 0 8px; color: #94A3B8; font-size: 12px; }
        .dd-progreso { display: flex; align-items: center; gap: 8px; }
        .dd-barra { flex: 1; height: 6px; overflow: hidden; border-radius: 999px; background: #E8EEF8; }
        .dd-barra > div { height: 100%; border-radius: 999px; background: #2563EB; }
        .dd-barra-completa > div { background: #059669; }
        .dd-pct { color: #94A3B8; font: 400 12px 'JetBrains Mono', ui-monospace, monospace; }

        .dd-actividad { padding: 16px; }
        .dd-actividad-titulo { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; color: #059669; }
        .dd-actividad-titulo span { color: #1A2C4E; font: 700 14px 'DM Sans', sans-serif; }
        .dd-actividad-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .dd-actividad-item { padding: 8px; border-radius: 8px; background: #F8FAFC; text-align: center; }
        .dd-actividad-valor { font: 700 20px 'DM Sans', sans-serif; }
        .dd-actividad-etiqueta { color: #94A3B8; font-size: 12px; }

        .dd-vacio { padding: 32px 16px; color: #64748B; font-size: 14px; text-align: center; }
        .dd-vacio a { color: #2563EB; font-weight: 600; text-decoration: none; }

        /* Variantes por rol del banner (Figma: docente azul, directivo morado, admin verde). */
        .dd-directivo .dd-hero { background: linear-gradient(135deg, #1A0A42 0%, #2D1B69 45%, #5B21B6 100%); }
        .dd-directivo .dd-hero-pre, .dd-directivo .dd-hero-sub { color: #C4B5FD; }
        .dd-directivo .dd-hero-btn-primario { border-color: #7C3AED; background: #7C3AED; }
        .dd-directivo .dd-hero-btn-primario:hover { background: #6D28D9; }
        .dd-directivo .dd-stat:hover { border-color: #7C3AED; box-shadow: 0 4px 12px rgba(124, 58, 237, .12); }

        .dd-admin .dd-hero { background: linear-gradient(135deg, #052E16 0%, #065F46 45%, #059669 100%); }
        .dd-admin .dd-hero-pre, .dd-admin .dd-hero-sub { color: #6EE7B7; }
        .dd-admin .dd-hero-btn-primario { border-color: #059669; background: #059669; }
        .dd-admin .dd-hero-btn-primario:hover { background: #047857; }
        .dd-admin .dd-stat:hover { border-color: #059669; box-shadow: 0 4px 12px rgba(5, 150, 105, .12); }

        .dd-rojo { background: #FEE2E2; color: #DC2626; }

        /* Tablas */
        .dd-tabla-ctn { overflow-x: auto; }
        .dd-tabla { width: 100%; border-collapse: collapse; font-size: 14px; }
        .dd-tabla th {
            padding: 12px 16px;
            border-bottom: 1px solid #DDE3EE;
            background: #F8FAFC;
            color: #64748B;
            font: 600 12px 'DM Sans', sans-serif;
            letter-spacing: .04em;
            text-align: left;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .dd-tabla td { padding: 12px 16px; border-top: 1px solid #F0F4FB; font-size: 12px; vertical-align: middle; }
        .dd-tabla tr:hover td { background: rgba(249, 250, 251, .6); }
        .dd-tabla-titulo { max-width: 280px; margin: 0; font: 600 12px/1.35 'DM Sans', sans-serif; }
        .dd-tabla-sub { margin: 2px 0 0; color: #94A3B8; font-size: 12px; }
        .dd-mono { font-family: 'JetBrains Mono', ui-monospace, monospace; white-space: nowrap; }
        .dd-suave { color: #94A3B8; }
        .dd-auto { color: #7C3AED; font-weight: 600; }
        .dd-manual { color: #059669; font-weight: 600; }
        .dd-btn-chico { flex: none; padding: 6px 12px; }
        .dd-btn-morado { background: #7C3AED; color: #fff; }
        .dd-btn-verde { background: #059669; color: #fff; }
        .dd-enlace-texto { color: #2563EB; font: 600 12px 'DM Sans', sans-serif; text-decoration: none; }

        /* Resumen con barras */
        .dd-panel { padding: 20px; }
        .dd-panel h3 { margin: 0 0 16px; font-size: 14px; font-weight: 700; }
        .dd-barras { display: flex; flex-direction: column; gap: 16px; }
        .dd-barra-fila { display: flex; justify-content: space-between; margin-bottom: 6px; font: 400 12px 'DM Sans', sans-serif; color: #64748B; }
        .dd-barra-fila strong { font-weight: 700; }
        .dd-barra-gruesa { height: 8px; overflow: hidden; border-radius: 999px; background: #F0F4FB; }
        .dd-barra-gruesa > div { height: 100%; border-radius: 999px; }

        /* Accesos rápidos */
        .dd-accesos { display: flex; flex-direction: column; gap: 8px; }
        .dd-acceso {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 10px;
            color: #1A2C4E;
            font: 500 14px 'DM Sans', sans-serif;
            text-decoration: none;
            transition: opacity .15s;
        }
        .dd-acceso:hover { opacity: .8; }

        /* Estado del sistema */
        .dd-estado { display: flex; flex-direction: column; gap: 10px; }
        .dd-estado-fila { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #334155; }
        .dd-estado-fila strong { margin-left: auto; font: 600 12px 'DM Sans', sans-serif; }
        .dd-ok { color: #059669; }
        .dd-mal { color: #DC2626; }
        .dd-aviso { color: #D97706; }

        /* Tarjeta del motor */
        .dd-motor { overflow: hidden; margin-bottom: 24px; }
        .dd-motor-cab {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px;
            background: linear-gradient(135deg, #0F2A57 0%, #1A4B8C 100%);
        }
        .dd-motor-cab.dd-motor-caido { background: linear-gradient(135deg, #451010, #991B1B); }
        .dd-motor-info { display: flex; align-items: center; gap: 16px; }
        .dd-motor-icono { display: flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 16px; background: rgba(255, 255, 255, .15); color: #fff; }
        .dd-motor h3 { margin: 0; color: #fff; font-size: 18px; font-weight: 700; }
        .dd-motor-sub { margin: 4px 0 0; color: #BFDBFE; font-size: 14px; }
        .dd-pastilla { display: inline-flex; align-items: center; gap: 6px; margin-left: 8px; padding: 4px 8px; border-radius: 999px; background: rgba(16, 185, 129, .2); color: #6EE7B7; font: 600 12px 'DM Sans', sans-serif; vertical-align: middle; }
        .dd-pastilla::before { content: ""; width: 8px; height: 8px; border-radius: 999px; background: #10B981; }
        .dd-motor-caido .dd-pastilla { background: rgba(239, 68, 68, .2); color: #FCA5A5; }
        .dd-motor-caido .dd-pastilla::before { background: #EF4444; }
        .dd-motor-cifras { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; padding: 20px; text-align: center; }
        @media (min-width: 768px) { .dd-motor-cifras { grid-template-columns: repeat(6, 1fr); } }
        .dd-motor-cifra { font: 700 20px 'DM Sans', sans-serif; }
        .dd-motor-etq { color: #94A3B8; font: 400 12px 'DM Sans', sans-serif; }
        .dd-motor-btns { display: flex; gap: 8px; }
        .dd-motor-btns a { flex: none; }
    </style>
