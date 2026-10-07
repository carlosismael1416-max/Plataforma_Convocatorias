<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        Propuesta {{ $propuesta->id }}
    </title>

    <style>
        @page {
            margin: 28px 34px;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            color: #1F2937;
            font-size: 10px;
            line-height: 1.5;
        }

        h1 {
            margin: 8px 0 5px;
            color: #1A4B8C;
            font-size: 20px;
        }

        h2 {
            margin: 0 0 10px;
            color: #1A4B8C;
            font-size: 13px;
        }

        p {
            margin: 0;
        }

        .institution {
            color: #64748B;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header {
            padding-bottom: 16px;
            border-bottom: 2px solid #1A4B8C;
        }

        .meta {
            margin-top: 10px;
            color: #64748B;
            font-size: 9px;
        }

        .meta span {
            margin-right: 14px;
        }

        .section {
            margin-top: 18px;
        }

        .box {
            margin-bottom: 7px;
            padding: 8px;
            background: #F8FAFC;
            border: 1px solid #E5E7EB;
        }

        .label {
            color: #64748B;
            font-size: 8px;
        }

        .value {
            margin-top: 2px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th,
        td {
            padding: 5px;
            border: 1px solid #DDE3EE;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #F1F5F9;
            color: #475569;
            font-size: 8px;
        }

        td {
            font-size: 8px;
        }

        .item {
            margin-bottom: 6px;
            padding: 7px;
            border: 1px solid #E5E7EB;
        }

        .small {
            color: #64748B;
            font-size: 8px;
        }

        .total {
            margin-top: 7px;
            text-align: right;
            font-weight: bold;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

<div class="header">

    <div class="institution">
        Instituto Tecnológico Superior de Valladolid
    </div>

    <h1>
        {{ $propuesta->titulo }}
    </h1>

    <div class="meta">

        <span>
            Propuesta #{{ $propuesta->id }}
        </span>

        <span>
            Versión {{ $propuesta->version }}
        </span>

        <span>
            Estado:
            {{
                str_replace(
                    '_',
                    ' ',
                    $propuesta->estado
                )
            }}
        </span>

    </div>

    <div class="meta">
        Responsable: {{ $responsable }}
    </div>

    <div class="meta">
        Convocatoria:
        {{
            $propuesta
                ->convocatoria
                ?->titulo
            ?? 'No especificada'
        }}
    </div>

</div>

<div class="section">
    <h2>1. Datos generales</h2>

    <div class="box">
        <div class="label">
            Organismo
        </div>

        <div class="value">
            {{
                $propuesta
                    ->convocatoria
                    ?->organismo
                    ?->nombre
                ?? 'No especificado'
            }}
        </div>
    </div>

    <div class="box">
        <div class="label">
            Área temática
        </div>

        <div class="value">
            {{
                $propuesta
                    ->convocatoria
                    ?->categoria
                    ?->nombre
                ?? 'No especificada'
            }}
        </div>
    </div>

    <div class="box">
        <div class="label">
            Periodo estimado
        </div>

        <div class="value">
            {{
                $inicioProyecto
                    ?->format('d/m/Y')
                ?? 'Sin definir'
            }}
            —
            {{
                $finProyecto
                    ?->format('d/m/Y')
                ?? 'Sin definir'
            }}
        </div>
    </div>
</div>

<div class="section">
    <h2>2. Resumen ejecutivo</h2>

    <p>
        {{
            $propuesta->resumen
            ?: 'Sin información registrada.'
        }}
    </p>
</div>

<div class="section">
    <h2>3. Justificación</h2>

    <p>
        {{
            $propuesta->justificacion
            ?: 'Sin información registrada.'
        }}
    </p>
</div>

<div class="section">
    <h2>4. Metodología</h2>

    <p>
        {{
            $propuesta->metodologia
            ?: 'Sin información registrada.'
        }}
    </p>
</div>

<div class="section">
    <h2>5. Impacto esperado</h2>

    <p>
        {{
            $propuesta->impacto_esperado
            ?: 'Sin información registrada.'
        }}
    </p>
</div>

<div class="section">
    <h2>6. Objetivos</h2>

    <div class="item">
        <strong>Objetivo general</strong>

        <br>

        {{
            $objetivoGeneral
                ?->descripcion
            ?? 'No registrado.'
        }}
    </div>

    @foreach (
        $objetivosEspecificos
        as $indice => $objetivo
    )
        <div class="item">
            <strong>
                Objetivo específico
                {{ $indice + 1 }}
            </strong>

            <br>

            {{ $objetivo->descripcion }}
        </div>
    @endforeach
</div>

<div class="section">
    <h2>7. Requisitos</h2>

    @forelse ($requisitos as $requisito)

        <div class="item">

            <strong>
                {{
                    $requisito
                        ->requisitoConvocatoria
                        ?->titulo
                    ?? 'Requisito'
                }}
            </strong>

            <div class="small">
                Estado:
                {{
                    $requisito->cumplido
                        ? 'Cumplido'
                        : 'Pendiente'
                }}
            </div>

            @if ($requisito->observaciones)
                <div>
                    {{
                        $requisito
                            ->observaciones
                    }}
                </div>
            @endif

        </div>

    @empty

        <div class="small">
            No hay requisitos registrados.
        </div>

    @endforelse
</div>

<div class="section">
    <h2>8. Presupuesto</h2>

    @if ($presupuesto->isNotEmpty())

        <table>
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Categoría</th>
                    <th>Cantidad</th>
                    <th>P. unitario</th>
                    <th>Subtotal</th>
                    <th>Moneda</th>
                </tr>
            </thead>

            <tbody>

                @foreach ($presupuesto as $item)

                    <tr>
                        <td>
                            {{ $item->concepto }}
                        </td>

                        <td>
                            {{
                                $item
                                    ->categoria_gasto
                                ?? '—'
                            }}
                        </td>

                        <td>
                            {{
                                number_format(
                                    (float)
                                    $item->cantidad,
                                    2
                                )
                            }}
                        </td>

                        <td>
                            $
                            {{
                                number_format(
                                    (float)
                                    $item
                                        ->precio_unitario,
                                    2
                                )
                            }}
                        </td>

                        <td>
                            $
                            {{
                                number_format(
                                    (float)
                                    $item->cantidad
                                    *
                                    (float)
                                    $item
                                        ->precio_unitario,
                                    2
                                )
                            }}
                        </td>

                        <td>
                            {{
                                $item->moneda
                                ?: 'MXN'
                            }}
                        </td>
                    </tr>

                @endforeach

            </tbody>
        </table>

        @foreach (
            $totalesPorMoneda
            as $moneda => $total
        )

            <div class="total">
                Total:
                $
                {{
                    number_format(
                        $total,
                        2
                    )
                }}
                {{ $moneda }}
            </div>

        @endforeach

    @else

        <div class="small">
            No existe presupuesto registrado.
        </div>

    @endif
</div>

<div class="section">
    <h2>9. Cotizaciones</h2>

    @if ($cotizaciones->isNotEmpty())

        <table>
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Proveedor</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                    <th>Documento</th>
                </tr>
            </thead>

            <tbody>

                @foreach ($cotizaciones as $cotizacion)

                    <tr>
                        <td>
                            {{
                                $cotizacion
                                    ->concepto
                                ?: '—'
                            }}
                        </td>

                        <td>
                            {{
                                $cotizacion
                                    ->proveedor
                                ?: '—'
                            }}
                        </td>

                        <td>
                            $
                            {{
                                number_format(
                                    (float)
                                    $cotizacion
                                        ->monto,
                                    2
                                )
                            }}
                            {{
                                $cotizacion
                                    ->moneda
                            }}
                        </td>

                        <td>
                            {{
                                $cotizacion
                                    ->fecha_cotizacion
                                    ?->format(
                                        'd/m/Y'
                                    )
                                ?? '—'
                            }}
                        </td>

                        <td>
                            {{
                                filled(
                                    $cotizacion
                                        ->archivo_url
                                )
                                    ? 'Disponible'
                                    : 'Pendiente'
                            }}
                        </td>
                    </tr>

                @endforeach

            </tbody>
        </table>

    @else

        <div class="small">
            No se registraron cotizaciones.
        </div>

    @endif
</div>

<div class="section">
    <h2>10. Cronograma</h2>

    @forelse ($actividades as $actividad)

        <div class="item">

            <strong>
                {{ $actividad->actividad }}
            </strong>

            <div class="small">
                {{
                    $actividad
                        ->fecha_inicio
                        ?->format(
                            'd/m/Y'
                        )
                }}
                —
                {{
                    $actividad
                        ->fecha_fin
                        ?->format(
                            'd/m/Y'
                        )
                }}

                ·
                {{
                    $actividad
                        ->responsable
                    ?: 'Sin responsable'
                }}

                ·
                {{
                    str_replace(
                        '_',
                        ' ',
                        $actividad->estado
                    )
                }}

                ·
                {{
                    number_format(
                        (float)
                        $actividad
                            ->porcentaje_avance,
                        0
                    )
                }}%
            </div>

            @if ($actividad->descripcion)
                <div>
                    {{ $actividad->descripcion }}
                </div>
            @endif

        </div>

    @empty

        <div class="small">
            No hay actividades registradas.
        </div>

    @endforelse
</div>

<div class="section">
    <h2>11. Entregables</h2>

    @forelse ($entregables as $entregable)

        @php
            $archivosEntregable =
                collect(
                    $evidenciasPorEntregable
                        ->get(
                            $entregable->id,
                            []
                        )
                );
        @endphp

        <div class="item">

            <strong>
                {{ $entregable->nombre }}
            </strong>

            <div class="small">
                Estado:
                {{
                    str_replace(
                        '_',
                        ' ',
                        $entregable->estado
                    )
                }}

                · Fecha límite:
                {{
                    $entregable
                        ->fecha_limite
                        ?->format(
                            'd/m/Y'
                        )
                    ?? 'Sin definir'
                }}
            </div>

            @if ($entregable->descripcion)
                <div>
                    {{ $entregable->descripcion }}
                </div>
            @endif

            @if ($archivosEntregable->isNotEmpty())

                <div class="small">
                    Evidencias:

                    @foreach (
                        $archivosEntregable
                        as $evidencia
                    )
                        {{ $evidencia->nombre }}@if (! $loop->last), @endif
                    @endforeach
                </div>

            @endif

        </div>

    @empty

        <div class="small">
            No hay entregables registrados.
        </div>

    @endforelse
</div>

<div class="section">
    <h2>12. Evidencias</h2>

    @forelse ($evidencias as $evidencia)

        <div class="item">

            <strong>
                {{ $evidencia->nombre }}
            </strong>

            @if ($evidencia->descripcion)
                <div>
                    {{ $evidencia->descripcion }}
                </div>
            @endif

            <div class="small">
                {{
                    filled(
                        $evidencia
                            ->ruta_archivo
                    )
                    || filled(
                        $evidencia
                            ->url_archivo
                    )
                        ? 'Archivo disponible'
                        : 'Sin archivo'
                }}
            </div>

        </div>

    @empty

        <div class="small">
            No hay evidencias registradas.
        </div>

    @endforelse
</div>

</body>
</html>
