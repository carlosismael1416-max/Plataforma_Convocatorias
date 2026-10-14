@props(['titulo', 'subtitulo' => null])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }} | Convocatorias ITSVA</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f1f5f9;
            color: #1e293b;
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .login {
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 36px;
            border-radius: 16px;
            box-shadow: 0 12px 36px rgba(15, 23, 42, .09);
        }

        h1 { margin: 0 0 8px; text-align: center; }
        .subtitulo {
            text-align: center;
            color: #64748b;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        label {
            display: block;
            font-weight: bold;
            margin: 18px 0 8px;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 16px;
        }

        input:focus {
            outline: 2px solid #d97706;
            border-color: transparent;
        }

        button {
            width: 100%;
            padding: 14px;
            margin-top: 25px;
            border: 0;
            border-radius: 8px;
            background: #b45309;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover { background: #92400e; }

        .error {
            margin-top: 12px;
            padding: 12px;
            border-radius: 8px;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 14px;
        }

        .pie {
            margin-top: 25px;
            color: #64748b;
            text-align: center;
            font-size: 13px;
        }

        .exito {
            margin-top: 12px;
            padding: 12px;
            border-radius: 8px;
            background: #f0fdf4;
            color: #166534;
            font-size: 14px;
        }

        .enlace {
            display: block;
            margin-top: 16px;
            text-align: center;
            color: #b45309;
            font-size: 14px;
        }

        .ayuda {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 13px;
        }

    </style>
</head>
<body>
    <main class="login">
        <h1>Convocatorias ITSVA</h1>

        <p class="subtitulo">
            Plataforma de Gestión de Convocatorias
            @if ($subtitulo)
                <br>
                {{ $subtitulo }}
            @endif
        </p>

        @if (session('status'))
            <div class="exito" role="status">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}

        <p class="pie">
            Instituto Tecnológico Superior de Valladolid
        </p>
    </main>
</body>
</html>
