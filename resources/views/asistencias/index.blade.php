@extends('layouts.app')

@section('title', 'Asistencias')

@section('content')

<div class="attendance-page">

    {{-- ENCABEZADO --}}
    <div class="page-header">
        <div>
            <h1>Asistencias</h1>
            <p>Consulta y supervisa las jornadas registradas por los vendedores.</p>
        </div>
    </div>


    {{-- FILTROS --}}
    <div class="filters-card">

        <form method="GET" action="{{ route('asistencias.index') }}">

            <div class="filters-grid">

                <div class="form-group">
                    <label for="fecha_desde">Desde</label>

                    <input
                        type="date"
                        name="fecha_desde"
                        id="fecha_desde"
                        value="{{ $fechaDesde }}"
                    >
                </div>


                <div class="form-group">
                    <label for="fecha_hasta">Hasta</label>

                    <input
                        type="date"
                        name="fecha_hasta"
                        id="fecha_hasta"
                        value="{{ $fechaHasta }}"
                    >
                </div>


                <div class="form-group">
                    <label for="id_vendedor">Vendedor</label>

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
                                    - {{ $vendedor->usuario->nombre }}
                                    {{ $vendedor->usuario->apellido }}
                                @endif

                            </option>

                        @endforeach

                    </select>
                </div>


                <div class="form-group">
                    <label for="id_punto_venta">Punto de venta</label>

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


                <div class="form-group">
                    <label for="id_estado_asistencia">
                        Estado
                    </label>

                    <select
                        name="id_estado_asistencia"
                        id="id_estado_asistencia"
                    >

                        <option value="">
                            Todos
                        </option>

                        @foreach($estados as $estado)

                            <option
                                value="{{ $estado->id_estado_asistencia }}"
                                {{ (string)$idEstado === (string)$estado->id_estado_asistencia ? 'selected' : '' }}
                            >
                                {{ str_replace('_', ' ', $estado->nombre) }}
                            </option>

                        @endforeach

                    </select>
                </div>

            </div>


            <div class="filter-actions">

                <button
                    type="submit"
                    class="btn-filter"
                >
                    Filtrar
                </button>


                <a
                    href="{{ route('asistencias.index') }}"
                    class="btn-clear"
                >
                    Limpiar
                </a>

            </div>

        </form>

    </div>


    {{-- RESULTADOS --}}
    <div class="table-card">

        <div class="table-header">

            <div>
                <h2>Registro de asistencias</h2>

                <p>
                    {{ $asistencias->total() }}
                    {{ $asistencias->total() === 1 ? 'registro encontrado' : 'registros encontrados' }}
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
                            <th>Acciones</th>
                        </tr>

                    </thead>


                    <tbody>

                        @foreach($asistencias as $asistencia)

                            @php

                                $asignacion = $asistencia->asignacion;

                                $horario = $asignacion?->horario;

                                $punto = $asignacion?->puntoVenta;

                                $vendedor = $asignacion?->vendedor;

                                $usuario = $vendedor?->usuario;


                                /*
                                 * Detectar salida anticipada.
                                 */

                                $salidaAnticipada = false;

                                if (
                                    $asistencia->hora_salida &&
                                    $horario
                                ) {

                                    $horaProgramadaSalida =
                                        \Carbon\Carbon::parse(
                                            $asistencia->fecha->format('Y-m-d')
                                            . ' '
                                            . $horario->hora_salida
                                        );

                                    $salidaAnticipada =
                                        $asistencia->hora_salida
                                            ->lt($horaProgramadaSalida);

                                }


                                /*
                                 * Detectar salida fuera del punto.
                                 */

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

                                {{-- FECHA --}}
                                <td data-label="Fecha">

                                    <strong class="date-text">
                                        {{ $asistencia->fecha->format('d/m/Y') }}
                                    </strong>

                                </td>


                                {{-- VENDEDOR --}}
                                <td data-label="Vendedor">

                                    <div class="seller-cell">

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

                                                @if($usuario)

                                                    {{ $usuario->nombre }}
                                                    {{ $usuario->apellido }}

                                                @else

                                                    Vendedor

                                                @endif

                                            </strong>


                                            <small>
                                                {{ $vendedor?->codigo_empleado ?? '-' }}
                                            </small>

                                        </div>

                                    </div>

                                </td>


                                {{-- PUNTO --}}
                                <td data-label="Punto">

                                    <strong>
                                        {{ $punto?->nombre ?? '-' }}
                                    </strong>

                                </td>


                                {{-- HORARIO --}}
                                <td data-label="Horario">

                                    @if($horario)

                                        <span class="schedule">

                                            {{ \Carbon\Carbon::parse($horario->hora_entrada)->format('H:i') }}

                                            –

                                            {{ \Carbon\Carbon::parse($horario->hora_salida)->format('H:i') }}

                                        </span>

                                    @else

                                        -

                                    @endif

                                </td>


                                {{-- ENTRADA --}}
                                <td data-label="Entrada">

                                    @if($asistencia->hora_llegada)

                                        <strong class="time-value">
                                            {{ $asistencia->hora_llegada->format('H:i') }}
                                        </strong>

                                        @if($asistencia->distancia_llegada_metros !== null)

                                            <small class="distance">
                                                {{ number_format($asistencia->distancia_llegada_metros, 0) }} m
                                            </small>

                                        @endif

                                    @else

                                        <span class="muted">
                                            -
                                        </span>

                                    @endif

                                </td>


                                {{-- SALIDA --}}
                                <td data-label="Salida">

                                    @if($asistencia->hora_salida)

                                        <strong class="time-value">
                                            {{ $asistencia->hora_salida->format('H:i') }}
                                        </strong>


                                        @if($asistencia->distancia_salida_metros !== null)

                                            <small class="distance">
                                                {{ number_format($asistencia->distancia_salida_metros, 0) }} m
                                            </small>

                                        @endif

                                    @else

                                        <span class="working-badge">
                                            En jornada
                                        </span>

                                    @endif

                                </td>


                                {{-- ESTADO --}}
                                <td data-label="Estado">

                                    @if($asistencia->estado)

                                        <span
                                            class="
                                                status-badge

                                                {{ $asistencia->estado->nombre === 'PRESENTE'
                                                    ? 'present'
                                                    : '' }}

                                                {{ $asistencia->estado->nombre === 'TARDE'
                                                    ? 'late'
                                                    : '' }}

                                                {{ $asistencia->estado->nombre === 'FUERA_DEL_PUNTO'
                                                    ? 'outside'
                                                    : '' }}
                                            "
                                        >

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


                                {{-- INCIDENCIAS --}}
                                <td data-label="Incidencias">

                                    <div class="incidents">

                                        @if($salidaAnticipada)

                                            <span
                                                class="incident early"
                                                title="El vendedor registró su salida antes de la hora programada."
                                            >
                                                Salida anticipada
                                            </span>

                                        @endif


                                        @if($salidaFuera)

                                            <span
                                                class="incident outside"
                                                title="La salida fue registrada fuera del radio permitido."
                                            >
                                                Fuera del punto
                                            </span>

                                        @endif


                                        @if(
                                            !$salidaAnticipada &&
                                            !$salidaFuera &&
                                            $asistencia->hora_salida
                                        )

                                            <span class="incident normal">
                                                Normal
                                            </span>

                                        @endif


                                        @if(!$asistencia->hora_salida)

                                            <span class="incident pending">
                                                Jornada abierta
                                            </span>

                                        @endif

                                    </div>

                                </td>

                                <td data-label="Acciones">

                                    <a
                                        href="{{ route('asistencias.show', $asistencia->id_asistencia) }}"
                                        class="btn-view"
                                    >
                                        Ver detalle
                                    </a>

                                </td>

                            </tr>


                            {{-- OBSERVACIONES --}}

                            @if($asistencia->observaciones)

                                <tr class="observation-row">

                                    <td colspan="9">

                                        <div class="observation">

                                            <strong>
                                                Observaciones:
                                            </strong>

                                            {{ $asistencia->observaciones }}

                                        </div>

                                    </td>

                                </tr>

                            @endif

                        @endforeach

                    </tbody>

                </table>

            </div>


            <div class="pagination-wrapper">

                {{ $asistencias->links() }}

            </div>

        @else

            <div class="empty-results">

                <div class="empty-results-icon">
                    ✓
                </div>

                <h3>No hay asistencias</h3>

                <p>
                    No se encontraron registros con los filtros seleccionados.
                </p>

            </div>

        @endif

    </div>

</div>

@endsection



@push('styles')

<style>

    /* =========================================================
       GENERAL
    ========================================================= */

    .attendance-page {
        width: 100%;
    }


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
        font-size: 14px;
    }


    /* =========================================================
       FILTROS
    ========================================================= */

    .filters-card {
        margin-bottom: 20px;
        padding: 20px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }


    .filters-grid {
        display: grid;
        grid-template-columns:
            repeat(5, minmax(150px, 1fr));

        gap: 14px;
    }


    .form-group label {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
    }


    .form-group input,
    .form-group select {
        width: 100%;
        height: 42px;

        padding: 0 11px;

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

        box-shadow:
            0 0 0 3px rgba(37, 99, 235, .08);
    }


    .filter-actions {
        display: flex;
        gap: 9px;
        margin-top: 16px;
    }


    .btn-filter,
    .btn-clear {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;

        padding: 0 18px;

        border-radius: 8px;

        font-size: 13px;
        font-weight: 600;

        text-decoration: none;
    }


    .btn-filter {
        border: 0;
        background: #0b2b50;
        color: #ffffff;
        cursor: pointer;
    }


    .btn-filter:hover {
        background: #123b69;
    }


    .btn-clear {
        border: 1px solid #d8dee8;
        background: #ffffff;
        color: #475569;
    }


    .btn-clear:hover {
        background: #f8fafc;
    }


    /* =========================================================
       TABLA
    ========================================================= */

    .table-card {
        overflow: hidden;

        border: 1px solid #e5e7eb;
        border-radius: 12px;

        background: #ffffff;
    }


    .table-header {
        padding: 18px 20px;

        border-bottom: 1px solid #e5e7eb;
    }


    .table-header h2 {
        margin: 0;
        color: #172033;
        font-size: 17px;
    }


    .table-header p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
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
        padding: 12px 14px;

        border-bottom: 1px solid #e5e7eb;

        color: #64748b;

        font-size: 11px;
        font-weight: 700;

        text-align: left;
        text-transform: uppercase;

        white-space: nowrap;
    }


    td {
        padding: 14px;

        border-bottom: 1px solid #eef2f6;

        color: #334155;

        font-size: 13px;

        vertical-align: middle;
    }


    tbody tr:hover {
        background: #fbfdff;
    }


    .date-text {
        white-space: nowrap;
    }


    /* =========================================================
       VENDEDOR
    ========================================================= */

    .seller-cell {
        min-width: 170px;

        display: flex;
        align-items: center;
        gap: 9px;
    }


    .seller-avatar {
        width: 34px;
        height: 34px;

        flex: 0 0 34px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: #e8efff;

        color: #2454a6;

        font-size: 13px;
        font-weight: 700;
    }


    .seller-cell strong {
        display: block;
        color: #172033;
        font-size: 13px;
    }


    .seller-cell small {
        display: block;
        margin-top: 2px;

        color: #94a3b8;
        font-size: 10px;
    }


    /* =========================================================
       HORAS
    ========================================================= */

    .schedule {
        white-space: nowrap;
        color: #475569;
    }


    .time-value {
        display: block;
        color: #172033;
    }


    .distance {
        display: block;
        margin-top: 3px;

        color: #94a3b8;
        font-size: 10px;
    }


    .muted {
        color: #94a3b8;
    }


    /* =========================================================
       ESTADOS
    ========================================================= */

    .status-badge {
        display: inline-flex;

        padding: 5px 9px;

        border-radius: 20px;

        background: #f1f5f9;

        color: #475569;

        font-size: 10px;
        font-weight: 700;

        white-space: nowrap;
    }


    .status-badge.present {
        background: #dcfce7;
        color: #166534;
    }


    .status-badge.late {
        background: #ffedd5;
        color: #9a3412;
    }


    .status-badge.outside {
        background: #fee2e2;
        color: #991b1b;
    }


    .working-badge {
        display: inline-flex;

        padding: 5px 9px;

        border-radius: 20px;

        background: #dbeafe;
        color: #1d4ed8;

        font-size: 10px;
        font-weight: 700;
    }


    /* =========================================================
       INCIDENCIAS
    ========================================================= */

    .incidents {
        min-width: 130px;

        display: flex;
        flex-direction: column;
        align-items: flex-start;

        gap: 5px;
    }


    .incident {
        display: inline-flex;

        padding: 4px 8px;

        border-radius: 20px;

        font-size: 9px;
        font-weight: 700;

        white-space: nowrap;
    }


    .incident.normal {
        background: #dcfce7;
        color: #166534;
    }


    .incident.early {
        background: #ffedd5;
        color: #9a3412;
    }


    .incident.outside {
        background: #fee2e2;
        color: #991b1b;
    }


    .incident.pending {
        background: #dbeafe;
        color: #1d4ed8;
    }


    /* =========================================================
       OBSERVACIONES
    ========================================================= */

    .observation-row:hover {
        background: transparent;
    }


    .observation-row td {
        padding: 0 14px 13px;
        background: #ffffff;
    }


    .observation {
        padding: 9px 11px;

        border-radius: 7px;

        background: #f8fafc;

        color: #64748b;

        font-size: 11px;
        line-height: 1.45;
    }


    .observation strong {
        color: #334155;
    }


    /* =========================================================
       PAGINACIÓN
    ========================================================= */

    .pagination-wrapper {
        padding: 16px 20px;
    }


    /* =========================================================
       VACÍO
    ========================================================= */

    .empty-results {
        padding: 55px 20px;
        text-align: center;
    }


    .empty-results-icon {
        width: 55px;
        height: 55px;

        display: flex;
        align-items: center;
        justify-content: center;

        margin: 0 auto 12px;

        border-radius: 50%;

        background: #eef2ff;

        color: #2563eb;

        font-size: 24px;
    }


    .empty-results h3 {
        margin: 0 0 5px;
    }


    .empty-results p {
        margin: 0;
        color: #64748b;
        font-size: 13px;
    }


    /* =========================================================
       TABLET
    ========================================================= */

    @media(max-width: 1100px) {

        .filters-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

    }


    /* =========================================================
       MÓVIL
    ========================================================= */

    @media(max-width: 700px) {

        .page-header h1 {
            font-size: 24px;
        }


        .filters-card {
            padding: 15px;
        }


        .filters-grid {
            grid-template-columns: 1fr;
        }


        .filter-actions {
            flex-direction: column;
        }


        .btn-filter,
        .btn-clear {
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


        tbody tr:not(.observation-row) {
            margin-bottom: 12px;

            overflow: hidden;

            border: 1px solid #e5e7eb;
            border-radius: 11px;

            background: #ffffff;
        }


        tbody tr:not(.observation-row) td {
            display: flex;
            justify-content: space-between;
            align-items: center;

            gap: 15px;

            padding: 11px 13px;

            border-bottom: 1px solid #eef2f6;

            text-align: right;
        }


        tbody tr:not(.observation-row) td:last-child {
            border-bottom: 0;
        }


        tbody tr:not(.observation-row) td::before {
            content: attr(data-label);

            color: #64748b;

            font-size: 10px;
            font-weight: 700;

            text-align: left;
            text-transform: uppercase;
        }


        .seller-cell {
            min-width: 0;
            justify-content: flex-end;
        }


        .seller-cell > div:last-child {
            text-align: right;
        }


        .incidents {
            min-width: 0;
            align-items: flex-end;
        }


        .observation-row {
            display: block;

            margin-top: -12px;
            margin-bottom: 12px;
        }


        .observation-row td {
            display: block;

            padding: 10px 12px;

            border: 1px solid #e5e7eb;
            border-top: 0;

            border-radius: 0 0 11px 11px;

            background: #ffffff;
        }

    }

    .btn-view {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 6px 10px;

    border: 1px solid #dbe4ef;
    border-radius: 7px;

    background: #ffffff;
    color: #2454a6;

    font-size: 11px;
    font-weight: 600;

    text-decoration: none;
    white-space: nowrap;
}

.btn-view:hover {
    background: #f1f5f9;
}

</style>

@endpush
