<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        {{ $reporte['titulo'] }}
    </title>

    <style>
        @page {
            margin: 28px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1E293B;
            font-size: 9px;
        }

        h1 {
            margin: 0 0 5px;
            font-size: 18px;
            color: #5B21B6;
        }

        .institution {
            margin-bottom: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .meta {
            margin: 10px 0 16px;
            padding: 8px;
            background: #F5F3FF;
            border: 1px solid #DDD6FE;
        }

        .metrics {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .metrics td {
            width: 25%;
            padding: 8px;
            border: 1px solid #E2E8F0;
        }

        .metric-label {
            color: #64748B;
            font-size: 8px;
        }

        .metric-value {
            margin-top: 3px;
            font-size: 13px;
            font-weight: bold;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th {
            padding: 6px;
            background: #7C3AED;
            color: white;
            text-align: left;
            font-size: 8px;
        }

        table.data td {
            padding: 6px;
            border: 1px solid #E2E8F0;
            vertical-align: top;
            font-size: 7px;
        }

        table.data tr:nth-child(even) td {
            background: #F8FAFC;
        }

        .footer {
            margin-top: 15px;
            color: #64748B;
            font-size: 7px;
        }
    </style>
</head>

<body>

    <div class="institution">
        Instituto Tecnológico Superior de Valladolid
    </div>

    <h1>
        {{ $reporte['titulo'] }}
    </h1>

    <div>
        {{ $reporte['descripcion'] }}
    </div>

    <div class="meta">
        <strong>Tipo:</strong>
        {{ $tipo }}

        &nbsp; | &nbsp;

        <strong>Periodo:</strong>
        {{ $fechaInicio }}
        —
        {{ $fechaFinal }}

        &nbsp; | &nbsp;

        <strong>Generado:</strong>
        {{ $generadoEn->format('d/m/Y H:i') }}
    </div>

    <table class="metrics">
        <tr>

            @foreach (
                $reporte['metricas']
                as $metrica
            )

                <td>
                    <div class="metric-label">
                        {{ $metrica['label'] }}
                    </div>

                    <div class="metric-value">
                        {{ $metrica['valor'] }}
                    </div>
                </td>

            @endforeach

        </tr>
    </table>

    <table class="data">

        <thead>
            <tr>

                @foreach (
                    $reporte['columnas']
                    as $columna
                )

                    <th>
                        {{ $columna['label'] }}
                    </th>

                @endforeach

            </tr>
        </thead>

        <tbody>

            @forelse (
                $reporte['filas']
                as $fila
            )

                <tr>

                    @foreach (
                        $reporte['columnas']
                        as $columna
                    )

                        <td>
                            {{
                                $fila[
                                    $columna['key']
                                ]
                                ?? ''
                            }}
                        </td>

                    @endforeach

                </tr>

            @empty

                <tr>
                    <td
                        colspan="{{
                            count(
                                $reporte['columnas']
                            )
                        }}"
                    >
                        Sin registros.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

    <div class="footer">
        Reporte generado automáticamente por
        Convocatorias ITSVA.
    </div>

</body>
</html>
