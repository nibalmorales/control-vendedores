@extends('layouts.app')

@section('title', 'Reporte de asistencias')

@section('content')

<div class="report-page">

    <div class="page-header">
        <div>
            <h1>Reporte de asistencias</h1>
            <p>Resumen general del cumplimiento de jornadas de los vendedores.</p>
        </div>
    </div>


    {{-- KPI --}}
    <div class="kpi-grid">

        <div class="kpi-card">
            <div class="kpi-icon total">▦</div>
            <div>
                <span>Total jornadas</span>
                <strong>{{ number_format($total) }}</strong>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon present">✓</div>
            <div>
                <span>Presentes</span>
                <strong>{{ number_format($presentes) }}</strong>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon late">◷</div>
            <div>
                <span>Llegadas tarde</span>
                <strong>{{ number_format($tardes) }}</strong>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon early">↙</div>
            <div>
                <span>Salidas anticipadas</span>
                <strong>{{ number_format($salidasAnticipadas) }}</strong>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon outside">⌖</div>
            <div>
                <span>Salidas fuera del punto</span>
                <strong>{{ number_format($salidasFuera) }}</strong>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon open">●</div>
            <div>
                <span>Jornadas abiertas</span>
                <strong>{{ number_format($jornadasAbiertas) }}</strong>
            </div>
        </div>

    </div>


    {{-- FILTROS --}}
    <div class="filter-card">

        <form
            method="GET"
            action="{{ route('reportes.asistencias') }}"
        >

            <div class="filter-grid">

                <div class="form-group">

                    <label for="fecha_desde">
                        Desde
                    </label>

                    <input
                        type="date"
                        name="fecha_desde"
                        id="fecha_desde"
                        value="{{ $fechaDesde }}"
                    >

                </div>


                <div class="form-group">

                    <label for="fecha_hasta">
                        Hasta
                    </label>

                    <input
                        type="date"
                        name="fecha_hasta"
                        id="fecha_hasta"
                        value="{{ $fechaHasta }}"
                    >

                </div>


                <div class="form-group">

                    <label for="id_vendedor">
                        Vendedor
                    </label>

                    <select
                        name="id_vendedor"
                        id="id_vendedor"
                    >

                        <option value="">
                            Todos
                        </option>

                        @foreach($vendedores as $vendedor)

                            <option
                                value="{{ $vendedor->id_vendedor }}"
                                {{ (string)$idVendedor === (string)$vendedor->id_vendedor ? 'selected' : '' }}
                            >

                                {{ $vendedor->codigo_empleado }}

                                @if($vendedor->usuario)
                                    -
                                    {{ $vendedor->usuario->nombre }}
                                    {{ $vendedor->usuario->apellido }}
                                @endif

                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="form-group">

                    <label for="id_punto_venta">
                        Punto de venta
                    </label>

                    <select
                        name="id_punto_venta"
                        id="id_punto_venta"
                    >

                        <option value="">
                            Todos
                        </option>

                        @foreach($puntos as $punto)

                            <option
                                value="{{ $punto->id_punto_venta }}"
                                {{ (string)$idPuntoVenta === (string)$punto->id_punto_venta ? 'selected' : '' }}
                            >
                                {{ $punto->nombre }}
                            </option>

                        @endforeach

                    </select>

                </div>

            </div>


            <div class="filter-actions">

                <button
                    type="submit"
                    class="btn-primary"
                >
                    Aplicar filtros
                </button>

                <a
                    href="{{ route('reportes.asistencias') }}"
                    class="btn-secondary"
                >
                    Limpiar
                </a>

                <a
                    href="{{ route('reportes.asistencias.exportar', request()->query()) }}"
                    class="btn-excel"
                >
                    Exportar Excel
                </a>

            </div>

        </form>

    </div>


    {{-- TABLA --}}
    <div class="table-card">

        <div class="table-header">

            <div>
                <h2>Detalle de jornadas</h2>

                <p>
                    {{ number_format($asistencias->total()) }}
                    {{ $asistencias->total() === 1 ? 'registro' : 'registros' }}
                </p>
            </div>

        </div>


        @if($asistencias->count())

            <div class="table-responsive">

                <table>

                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Vendedor</th>
                            <th>Punto</th>
                            <th>Horario</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Estado</th>
                            <th>Incidencias</th>
                            <th></th>
                        </tr>
                    </thead>


                    <tbody>

                    @foreach($asistencias as $asistencia)

                        @php

                            $asignacion = $asistencia->asignacion;
                            $vendedor = $asignacion?->vendedor;
                            $usuario = $vendedor?->usuario;
                            $punto = $asignacion?->puntoVenta;
                            $horario = $asignacion?->horario;

                            $salidaAnticipada = false;

                            if ($asistencia->hora_salida && $horario) {

                                $horaProgramada = \Carbon\Carbon::parse(
                                    $asistencia->fecha->format('Y-m-d')
                                    . ' '
                                    . $horario->hora_salida
                                );

                                $salidaAnticipada =
                                    $asistencia->hora_salida->lt(
                                        $horaProgramada
                                    );
                            }

                            $salidaFuera = false;

                            if (
                                $asistencia->distancia_salida_metros !== null &&
                                $punto
                            ) {

                                $salidaFuera =
                                    $asistencia->distancia_salida_metros >
                                    $punto->radio_permitido_metros;
                            }

                        @endphp


                        <tr>

                            <td data-label="Fecha">

                                <strong>
                                    {{ $asistencia->fecha->format('d/m/Y') }}
                                </strong>

                            </td>


                            <td data-label="Vendedor">

                                <div class="seller">

                                    <div class="seller-avatar">

                                        {{ strtoupper(
                                            substr(
                                                $usuario?->nombre ?? 'V',
                                                0,
                                                1
                                            )
                                        ) }}

                                    </div>

                                    <div>

                                        <strong>
                                            {{ $usuario?->nombre ?? 'Vendedor' }}
                                            {{ $usuario?->apellido ?? '' }}
                                        </strong>

                                        <small>
                                            {{ $vendedor?->codigo_empleado ?? '-' }}
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <td data-label="Punto">

                                {{ $punto?->nombre ?? '-' }}

                            </td>


                            <td data-label="Horario">

                                @if($horario)

                                    {{ \Carbon\Carbon::parse(
                                        $horario->hora_entrada
                                    )->format('H:i') }}

                                    –

                                    {{ \Carbon\Carbon::parse(
                                        $horario->hora_salida
                                    )->format('H:i') }}

                                @else

                                    -

                                @endif

                            </td>


                            <td data-label="Entrada">

                                @if($asistencia->hora_llegada)

                                    <strong class="time">
                                        {{ $asistencia->hora_llegada->format('H:i') }}
                                    </strong>

                                    @if($asistencia->distancia_llegada_metros !== null)

                                        <small class="distance">
                                            {{ number_format(
                                                $asistencia->distancia_llegada_metros,
                                                0
                                            ) }} m
                                        </small>

                                    @endif

                                @else

                                    -

                                @endif

                            </td>


                            <td data-label="Salida">

                                @if($asistencia->hora_salida)

                                    <strong class="time">
                                        {{ $asistencia->hora_salida->format('H:i') }}
                                    </strong>

                                    @if($asistencia->distancia_salida_metros !== null)

                                        <small class="distance">
                                            {{ number_format(
                                                $asistencia->distancia_salida_metros,
                                                0
                                            ) }} m
                                        </small>

                                    @endif

                                @else

                                    <span class="badge open">
                                        En jornada
                                    </span>

                                @endif

                            </td>


                            <td data-label="Estado">

                                @if($asistencia->estado)

                                    <span class="
                                        badge
                                        {{ $asistencia->estado->nombre === 'PRESENTE' ? 'present' : '' }}
                                        {{ $asistencia->estado->nombre === 'TARDE' ? 'late' : '' }}
                                    ">

                                        {{ str_replace(
                                            '_',
                                            ' ',
                                            $asistencia->estado->nombre
                                        ) }}

                                    </span>

                                @else

                                    -

                                @endif

                            </td>


                            <td data-label="Incidencias">

                                <div class="incidents">

                                    @if($salidaAnticipada)

                                        <span class="badge early">
                                            Salida anticipada
                                        </span>

                                    @endif


                                    @if($salidaFuera)

                                        <span class="badge outside">
                                            Fuera del punto
                                        </span>

                                    @endif


                                    @if(
                                        !$salidaAnticipada &&
                                        !$salidaFuera &&
                                        $asistencia->hora_salida
                                    )

                                        <span class="badge normal">
                                            Normal
                                        </span>

                                    @endif


                                    @if(!$asistencia->hora_salida)

                                        <span class="badge open">
                                            Jornada abierta
                                        </span>

                                    @endif

                                </div>

                            </td>


                            <td data-label="Detalle">

                                <a
                                    href="{{ route(
                                        'asistencias.show',
                                        $asistencia->id_asistencia
                                    ) }}"
                                    class="btn-view"
                                >
                                    Ver
                                </a>

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>


            <div class="pagination-wrapper">
                {{ $asistencias->links() }}
            </div>


        @else

            <div class="empty-state">

                <div class="empty-icon">
                    ▦
                </div>

                <h3>No hay información</h3>

                <p>
                    No existen jornadas para los filtros seleccionados.
                </p>

            </div>

        @endif

    </div>

</div>

@endsection



@push('styles')

<style>

    .report-page {
        width: 100%;
    }


    /* HEADER */

    .page-header {
        margin-bottom: 22px;
    }

    .page-header h1 {
        margin: 0;
        color: #172033;
        font-size: 28px;
        font-weight: 700;
    }

    .page-header p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
    }


    /* KPI */

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .kpi-card {
        display: flex;
        align-items: center;
        gap: 13px;
        min-height: 95px;
        padding: 17px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }

    .kpi-icon {
        width: 45px;
        height: 45px;
        flex: 0 0 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        font-size: 19px;
        font-weight: 700;
    }

    .kpi-icon.total {
        background: #eef2ff;
        color: #4338ca;
    }

    .kpi-icon.present {
        background: #dcfce7;
        color: #166534;
    }

    .kpi-icon.late {
        background: #ffedd5;
        color: #9a3412;
    }

    .kpi-icon.early {
        background: #fef3c7;
        color: #92400e;
    }

    .kpi-icon.outside {
        background: #fee2e2;
        color: #991b1b;
    }

    .kpi-icon.open {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .kpi-card span {
        display: block;
        margin-bottom: 3px;
        color: #64748b;
        font-size: 11px;
    }

    .kpi-card strong {
        display: block;
        color: #172033;
        font-size: 24px;
        line-height: 1;
    }


    /* FILTROS */

    .filter-card {
        margin-bottom: 20px;
        padding: 18px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 13px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: 11px;
        font-weight: 600;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        height: 41px;
        padding: 0 10px;
        border: 1px solid #d8dee8;
        border-radius: 8px;
        background: #ffffff;
        color: #172033;
        font-size: 13px;
        outline: none;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
    }

    .filter-actions {
        display: flex;
        gap: 8px;
        margin-top: 15px;
    }

    .btn-primary,
    .btn-secondary {
        min-height: 39px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 16px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }

    .btn-primary {
        border: 0;
        background: #0b2b50;
        color: #ffffff;
        cursor: pointer;
    }

    .btn-primary:hover {
        background: #123b69;
    }

    .btn-secondary {
        border: 1px solid #d8dee8;
        background: #ffffff;
        color: #475569;
    }

    .btn-secondary:hover {
        background: #f8fafc;
    }


    /* TABLA */

    .table-card {
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }

    .table-header {
        padding: 17px 19px;
        border-bottom: 1px solid #e5e7eb;
    }

    .table-header h2 {
        margin: 0;
        color: #172033;
        font-size: 16px;
    }

    .table-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 11px;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: #f8fafc;
    }

    th {
        padding: 11px 12px;
        border-bottom: 1px solid #e5e7eb;
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    td {
        padding: 13px 12px;
        border-bottom: 1px solid #eef2f6;
        color: #334155;
        font-size: 12px;
        vertical-align: middle;
    }

    tbody tr:hover {
        background: #fbfdff;
    }


    /* VENDEDOR */

    .seller {
        min-width: 155px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .seller-avatar {
        width: 31px;
        height: 31px;
        flex: 0 0 31px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #e8efff;
        color: #2454a6;
        font-size: 11px;
        font-weight: 700;
    }

    .seller strong {
        display: block;
        color: #172033;
        font-size: 12px;
    }

    .seller small {
        display: block;
        margin-top: 2px;
        color: #94a3b8;
        font-size: 9px;
    }


    /* HORAS */

    .time {
        display: block;
    }

    .distance {
        display: block;
        margin-top: 2px;
        color: #94a3b8;
        font-size: 9px;
    }


    /* BADGES */

    .badge {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 20px;
        background: #f1f5f9;
        color: #475569;
        font-size: 9px;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge.present,
    .badge.normal {
        background: #dcfce7;
        color: #166534;
    }

    .badge.late,
    .badge.early {
        background: #ffedd5;
        color: #9a3412;
    }

    .badge.outside {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge.open {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .incidents {
        min-width: 115px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }


    /* VER */

    .btn-view {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border: 1px solid #dbe4ef;
        border-radius: 7px;
        background: #ffffff;
        color: #2454a6;
        font-size: 10px;
        font-weight: 600;
        text-decoration: none;
    }

    .btn-view:hover {
        background: #f1f5f9;
    }


    /* PAGINACIÓN */

    .pagination-wrapper {
        padding: 15px 18px;
    }


    /* VACÍO */

    .empty-state {
        padding: 50px 20px;
        text-align: center;
    }

    .empty-icon {
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 11px;
        border-radius: 50%;
        background: #eef2ff;
        color: #4338ca;
        font-size: 21px;
    }

    .empty-state h3 {
        margin: 0 0 5px;
        color: #172033;
    }

    .empty-state p {
        margin: 0;
        color: #64748b;
        font-size: 12px;
    }


    /* TABLET */

    @media(max-width: 1050px) {

        .kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }

    }


    /* MÓVIL */

    @media(max-width: 700px) {

        .page-header h1 {
            font-size: 24px;
        }

        .kpi-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .kpi-card {
            min-height: 82px;
            padding: 12px;
            gap: 9px;
        }

        .kpi-icon {
            width: 37px;
            height: 37px;
            flex-basis: 37px;
            font-size: 15px;
        }

        .kpi-card span {
            font-size: 9px;
        }

        .kpi-card strong {
            font-size: 20px;
        }

        .filter-grid {
            grid-template-columns: 1fr;
        }

        .filter-actions {
            flex-direction: column;
        }

        .btn-primary,
        .btn-secondary {
            width: 100%;
        }

        .table-card {
            overflow: visible;
            border: 0;
            background: transparent;
        }

        .table-header {
            margin-bottom: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #ffffff;
        }

        .table-responsive {
            overflow: visible;
        }

        table,
        tbody,
        tr,
        td {
            display: block;
            width: 100%;
        }

        thead {
            display: none;
        }

        tbody tr {
            margin-bottom: 10px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #ffffff;
        }

        tbody tr td {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 10px 12px;
            border-bottom: 1px solid #eef2f6;
            text-align: right;
        }

        tbody tr td:last-child {
            border-bottom: 0;
        }

        tbody tr td::before {
            content: attr(data-label);
            color: #64748b;
            font-size: 9px;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
        }

        .seller {
            min-width: 0;
            justify-content: flex-end;
        }

        .seller > div:last-child {
            text-align: right;
        }

        .incidents {
            min-width: 0;
            align-items: flex-end;
        }

    }


    @media(max-width: 420px) {

        .kpi-grid {
            grid-template-columns: 1fr;
        }

    }

    .btn-excel {
    min-height: 39px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 0 16px;

    border: 1px solid #bbf7d0;
    border-radius: 8px;

    background: #f0fdf4;
    color: #166534;

    font-size: 12px;
    font-weight: 600;

    text-decoration: none;
    }

    .btn-excel:hover {
        background: #dcfce7;
    }

    @media(max-width: 700px) {

        .btn-excel {
            width: 100%;
        }

    }

</style>

@endpush
