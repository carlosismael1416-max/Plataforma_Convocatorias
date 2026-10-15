@props(['titulo', 'subtitulo' => null])

{{-- Pantallas de acceso según el diseño de Figma (Login): panel
     institucional a la izquierda y formulario a la derecha. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }} | Convocatorias ITSVA</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|inter:400,500,600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            background: #F4F6FA;
            color: #1A2C4E;
            font-family: 'Inter', sans-serif;
        }

        h1, h2, .display { font-family: 'DM Sans', sans-serif; }

        /* ---------- Panel institucional ---------- */

        .panel {
            position: relative;
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 50%;
            padding: 48px;
            overflow: hidden;
            background: linear-gradient(135deg, #0F2A57 0%, #1A4B8C 60%, #2563EB 100%);
            color: #fff;
        }

        .panel::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: .1;
            background-image: radial-gradient(circle at 20px 20px, #fff 1px, transparent 0);
            background-size: 40px 40px;
        }

        .panel > * { position: relative; }

        .marca {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .marca-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .2);
            font: 700 18px 'DM Sans', sans-serif;
        }

        .marca-nombre { font: 700 20px 'DM Sans', sans-serif; }
        .marca-inst { color: #BFDBFE; font-size: 12px; }

        .centro {
            display: flex;
            flex: 1;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .funciones {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            width: 100%;
            max-width: 340px;
            margin-bottom: 40px;
        }

        .funcion {
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, .15);
            border-radius: 16px;
            background: rgba(255, 255, 255, .1);
            text-align: left;
            backdrop-filter: blur(10px);
        }

        .funcion-icono {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            margin-bottom: 12px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .15);
        }

        .funcion-icono svg { width: 18px; height: 18px; }

        .funcion-titulo {
            margin: 0;
            font: 600 14px/1.25 'DM Sans', sans-serif;
        }

        .funcion-sub {
            margin: 2px 0 0;
            color: #BFDBFE;
            font-size: 12px;
        }

        .panel h2 {
            margin: 0 0 12px;
            font-size: 30px;
            line-height: 1.2;
        }

        .panel-texto {
            max-width: 384px;
            margin: 0;
            color: #BFDBFE;
            font-size: 14px;
            line-height: 1.6;
        }

        .panel-pie {
            display: flex;
            gap: 24px;
            color: #BFDBFE;
            font-size: 12px;
        }

        /* ---------- Formulario ---------- */

        .lado-form {
            display: flex;
            flex: 1;
            align-items: center;
            justify-content: center;
            padding: 32px;
        }

        .form-ctn {
            width: 100%;
            max-width: 448px;
        }

        .marca-movil {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
        }

        .marca-movil .marca-logo {
            background: #1A4B8C;
            color: #fff;
        }

        .marca-movil .marca-nombre { color: #1A2C4E; }

        .encabezado { margin-bottom: 32px; }

        .encabezado h1 {
            margin: 0 0 8px;
            font-size: 24px;
            color: #1A2C4E;
        }

        .encabezado p {
            margin: 0;
            color: #64748B;
            font-size: 14px;
            line-height: 1.5;
        }

        form { display: flex; flex-direction: column; gap: 20px; }

        .campo { display: flex; flex-direction: column; gap: 6px; }

        label {
            color: #334155;
            font: 500 14px 'DM Sans', sans-serif;
        }

        input[type=email],
        input[type=password],
        input[type=text] {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #DDE3EE;
            border-radius: 12px;
            background: #fff;
            color: #1A2C4E;
            font: 400 14px 'Inter', sans-serif;
            outline: none;
        }

        input:focus {
            border-color: #2563EB;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .15);
        }

        .con-ojo { position: relative; }
        .con-ojo input { padding-right: 48px; }

        .ojo {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            padding: 4px;
            border: 0;
            background: none;
            color: #94A3B8;
            cursor: pointer;
        }

        .ojo svg { display: block; width: 17px; height: 17px; }

        .fila {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .recordar {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748B;
            font: 400 14px 'Inter', sans-serif;
            cursor: pointer;
        }

        .enlace {
            color: #2563EB;
            font: 500 14px 'DM Sans', sans-serif;
            text-decoration: none;
        }

        .enlace:hover { text-decoration: underline; }

        .boton {
            width: 100%;
            padding: 12px;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(135deg, #1A4B8C, #2563EB);
            box-shadow: 0 4px 14px rgba(26, 75, 140, .3);
            color: #fff;
            font: 700 14px 'DM Sans', sans-serif;
            cursor: pointer;
        }

        .boton:hover { opacity: .92; }

        .ayuda {
            margin: 0;
            color: #64748B;
            font-size: 13px;
        }

        .error,
        .exito {
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
        }

        .error { background: #FEF2F2; color: #B91C1C; }
        .exito { margin-bottom: 20px; background: #F0FDF4; color: #166534; }

        .volver {
            display: block;
            margin-top: 20px;
            text-align: center;
        }

        .nota {
            margin: 24px 0 0;
            color: #94A3B8;
            font-size: 12px;
            text-align: center;
        }

        @media (min-width: 1024px) {
            .panel { display: flex; }
            .marca-movil { display: none; }
        }
    </style>
</head>
<body>
    <aside class="panel">
        <div class="marca">
            <span class="marca-logo">IT</span>
            <div>
                <div class="marca-nombre">ITSVA</div>
                <div class="marca-inst">Instituto Tecnológico Superior de Valladolid</div>
            </div>
        </div>

        <div class="centro">
            {{-- Funciones reales de la plataforma. --}}
            <div class="funciones">
                <div class="funcion">
                    <div class="funcion-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v3"/><circle cx="5" cy="14" r="3"/><path d="m9 18-1.5-1.5"/></svg></div>
                    <p class="funcion-titulo">Motor de extracción</p>
                    <p class="funcion-sub">Fuentes oficiales</p>
                </div>
                <div class="funcion">
                    <div class="funcion-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg></div>
                    <p class="funcion-titulo">Validación</p>
                    <p class="funcion-sub">Solo vigentes y elegibles</p>
                </div>
                <div class="funcion">
                    <div class="funcion-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg></div>
                    <p class="funcion-titulo">Convocatorias</p>
                    <p class="funcion-sub">Financiamiento académico</p>
                </div>
                <div class="funcion">
                    <div class="funcion-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/></svg></div>
                    <p class="funcion-titulo">Propuestas</p>
                    <p class="funcion-sub">De la convocatoria a tu proyecto</p>
                </div>
            </div>

            <h2>Gestión de Convocatorias<br>y Motor de Extracción</h2>
            <p class="panel-texto">
                Descubre, gestiona y desarrolla propuestas para oportunidades de financiamiento académico.
            </p>
        </div>

        <div class="panel-pie">
            <span>© {{ now()->year }} ITSVA</span>
            <span>•</span>
            <span>Plataforma institucional</span>
        </div>
    </aside>

    <main class="lado-form">
        <div class="form-ctn">
            <div class="marca-movil">
                <span class="marca-logo">IT</span>
                <span class="marca-nombre display">ITSVA</span>
            </div>

            <div class="encabezado">
                <h1>{{ $titulo }}</h1>
                @if ($subtitulo)
                    <p>{{ $subtitulo }}</p>
                @endif
            </div>

            @if (session('status'))
                <div class="exito" role="status">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}

            <p class="nota">Plataforma de uso exclusivo para personal del ITSVA</p>
        </div>
    </main>

    <script>
        // Mostrar u ocultar la contraseña.
        document.querySelectorAll('[data-ojo]').forEach((boton) => {
            boton.addEventListener('click', () => {
                const campo = document.getElementById(boton.dataset.ojo);
                const visible = campo.type === 'text';
                campo.type = visible ? 'password' : 'text';
                boton.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
            });
        });
    </script>
</body>
</html>
