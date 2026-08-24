@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="dashboard-page">

    {{-- =========================================================
         ENCABEZADO
    ========================================================== --}}
    <div class="page-header">
        <div>
            <h1>Dashboard</h1>

            <p>
                Resumen operativo de Control de Campo para hoy,
                {{ now()->format('d/m/Y') }}.
            </p>
        </div>
    </div>


    {{-- =========================================================
         KPI
    ========================================================== --}}
    <div class="kpi-grid">

        <div class="kpi-card">
            <div class="kpi-icon sellers">👥</div>

            <div>
                <span>Vendedores activos</span>
                <strong>{{ number_format($vendedoresActivos) }}</strong>
            </div>
        </div>


        <div class="kpi-card">
            <div class="kpi-icon points">📍</div>

            <div>
                <span>Puntos activos</span>
                <strong>{{ number_format($puntosActivos) }}</strong>
            </div>
        </div>


        <div class="kpi-card">
            <div class="kpi-icon attendance">✅</div>

            <div>
                <span>Asistencias hoy</span>
                <strong>{{ number_format($totalAsistenciasHoy) }}</strong>
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
            <div class="kpi-icon late">🕒</div>

            <div>
                <span>Llegadas tarde</span>
                <strong>{{ number_format($tardes) }}</strong>
            </div>
        </div>


        <div class="kpi-card">
            <div class="kpi-icon open">🔵</div>

            <div>
                <span>Jornadas abiertas</span>
                <strong>{{ number_format($jornadasAbiertas) }}</strong>
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
            <div class="kpi-icon outside">⚠</div>

            <div>
                <span>Salidas fuera del punto</span>
                <strong>{{ number_format($salidasFuera) }}</strong>
            </div>
        </div>

    </div>


    {{-- =========================================================
         COBERTURA DE HOY
    ========================================================== --}}
    <div class="coverage-card">

        <div class="coverage-header">

            <div>
                <h2>Cobertura de hoy</h2>

                <p>
                    Vendedores que deben presentarse según sus asignaciones.
                </p>
            </div>

            <div class="coverage-percent">
                {{ $porcentajeCobertura }}%
            </div>

        </div>


        <div class="coverage-summary">

            <div class="coverage-kpi">
                <span>Asignados</span>
                <strong>{{ $totalAsignadosHoy }}</strong>
            </div>


            <div class="coverage-kpi success">
                <span>Registrados</span>
                <strong>{{ $totalRegistradosHoy }}</strong>
            </div>


            <div class="coverage-kpi pending">
                <span>Pendientes</span>
                <strong>{{ $totalPendientesHoy }}</strong>
            </div>


            <div class="coverage-progress">

                <div class="progress-info">
                    <span>Cobertura</span>
                    <strong>{{ $porcentajeCobertura }}%</strong>
                </div>

                <div class="progress-track">
                    <div
                        class="progress-bar"
                        style="width: {{ min($porcentajeCobertura, 100) }}%;"
                    ></div>
                </div>

            </div>

        </div>


        @if($coberturaHoy->count())

            <div class="coverage-table">

                <table>

                    <thead>
                        <tr>
                            <th>Vendedor</th>
                            <th>Punto</th>
                            <th>Horario</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($coberturaHoy as $item)

                            @php
                                $asignacion = $item['asignacion'];
                                $asistencia = $item['asistencia'];

                                $usuario =
                                    $asignacion->vendedor?->usuario;

                                $punto =
                                    $asignacion->puntoVenta;

                                $horario =
                                    $asignacion->horario;
                            @endphp

                            <tr>

                                <td data-label="Vendedor">

                                    <strong>
                                        {{ $usuario?->nombre ?? 'Vendedor' }}
                                        {{ $usuario?->apellido ?? '' }}
                                    </strong>

                                    <small class="coverage-code">
                                        {{ $asignacion->vendedor?->codigo_empleado ?? '-' }}
                                    </small>

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


                                <td data-label="Estado">

                                    @if($asistencia)

                                        @if(
                                            $asistencia->estado?->nombre === 'PRESENTE'
                                        )

                                            <span class="coverage-status present">
                                                PRESENTE
                                            </span>

                                        @elseif(
                                            $asistencia->estado?->nombre === 'TARDE'
                                        )

                                            <span class="coverage-status late">
                                                TARDE
                                            </span>

                                        @else

                                            <span class="coverage-status registered">

                                                {{ str_replace(
                                                    '_',
                                                    ' ',
                                                    $asistencia->estado?->nombre ?? 'REGISTRADO'
                                                ) }}

                                            </span>

                                        @endif

                                    @else

                                        <span class="coverage-status pending">
                                            PENDIENTE
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="coverage-empty">
                No hay vendedores programados para trabajar hoy.
            </div>

        @endif

    </div>


    {{-- =========================================================
         MAPA OPERATIVO DESPLEGABLE
    ========================================================== --}}
    <div class="map-card">

        <div class="map-header">

            <div>
                <h2>Mapa operativo</h2>

                <p>
                    Vendedores, ubicación actual de supervisores y puntos supervisados hoy.
                </p>
            </div>


            <div class="map-actions">

                <div class="map-total">

                    {{ $mapaVendedores->count() + $mapaSupervisores->count() }}

                    ubicaciones activas

                </div>


                <button
                    type="button"
                    class="btn-toggle-map"
                    id="btnToggleMapa"
                    aria-expanded="false"
                    aria-controls="mapaContainer"
                >
                    Ver mapa
                </button>

            </div>

        </div>


        <div
            class="map-collapsible"
            id="mapaContainer"
        >

            <div class="map-legend">

                <span>
                    <i class="legend-dot working"></i>
                    En jornada
                </span>

                <span>
                    <i class="legend-dot late"></i>
                    Llegada tarde
                </span>

                <span>
                    <i class="legend-dot finished"></i>
                    Jornada finalizada
                </span>

                <span>
                    <i class="legend-dot pending"></i>
                    Punto asignado
                </span>

                <span>
                    <i class="legend-dot supervisor"></i>
                    Supervisor actual
                </span>

                <span>
                    <i class="legend-dot visited"></i>
                    Punto supervisado
                </span>

                <span>
                    <i class="legend-dot current-visit"></i>
                    Visita en curso
                </span>

            </div>


            @if($mapaVendedores->count() || $mapaSupervisores->count() || $mapaVisitasSupervisor->count())

                <div id="mapaVendedores"></div>

            @else

                <div class="map-empty">

                    No existen ubicaciones disponibles para mostrar en el mapa de hoy.

                </div>

            @endif

        </div>

    </div>


    {{-- =========================================================
         ACTIVIDAD DE HOY
    ========================================================== --}}
    <div class="activity-card">

        <div class="activity-header">

            <div>
                <h2>Actividad de hoy</h2>

                <p>
                    Últimos registros de asistencia.
                </p>
            </div>


            <a
                href="{{ route('asistencias.index') }}"
                class="btn-view-all"
            >
                Ver asistencias
            </a>

        </div>


        @if($actividadHoy->count())

            <div class="table-responsive">

                <table>

                    <thead>

                        <tr>
                            <th>Vendedor</th>
                            <th>Punto</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Estado</th>
                            <th>Incidencias</th>
                            <th></th>
                        </tr>

                    </thead>


                    <tbody>

                        @foreach($actividadHoy as $asistencia)

                            @php

                                $asignacion =
                                    $asistencia->asignacion;

                                $vendedor =
                                    $asignacion?->vendedor;

                                $usuario =
                                    $vendedor?->usuario;

                                $punto =
                                    $asignacion?->puntoVenta;

                                $horario =
                                    $asignacion?->horario;


                                $salidaAnticipada = false;


                                if (
                                    $asistencia->hora_salida &&
                                    $horario
                                ) {

                                    $horaProgramada =
                                        \Carbon\Carbon::parse(
                                            $asistencia->fecha->format('Y-m-d')
                                            . ' '
                                            . $horario->hora_salida
                                        );


                                    $salidaAnticipada =
                                        $asistencia
                                            ->hora_salida
                                            ->lt($horaProgramada);

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


                                <td data-label="Entrada">

                                    @if($asistencia->hora_llegada)

                                        <strong>

                                            {{ $asistencia->hora_llegada->format('H:i') }}

                                        </strong>


                                        <small class="distance">

                                            {{ number_format(
                                                $asistencia->distancia_llegada_metros ?? 0,
                                                0
                                            ) }}
                                            m

                                        </small>

                                    @else

                                        -

                                    @endif

                                </td>


                                <td data-label="Salida">

                                    @if($asistencia->hora_salida)

                                        <strong>

                                            {{ $asistencia->hora_salida->format('H:i') }}

                                        </strong>


                                        <small class="distance">

                                            {{ number_format(
                                                $asistencia->distancia_salida_metros ?? 0,
                                                0
                                            ) }}
                                            m

                                        </small>

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
                                        class="btn-detail"
                                    >
                                        Ver
                                    </a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="empty-state">

                <div class="empty-icon">
                    ✓
                </div>

                <h3>
                    Sin actividad registrada
                </h3>

                <p>
                    Todavía no existen asistencias registradas para hoy.
                </p>

            </div>

        @endif

    </div>

</div>

@endsection



@push('styles')

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>


<style>

    /* =========================================================
       DASHBOARD
    ========================================================= */

    .dashboard-page {
        width: 100%;
    }


    /* =========================================================
       HEADER
    ========================================================= */

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


    /* =========================================================
       KPI
    ========================================================= */

    .kpi-grid {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }


    .kpi-card {
        min-height: 95px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }


    .kpi-icon {
        width: 52px;
        height: 52px;
        flex: 0 0 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        font-size: 22px;
        font-weight: 700;
    }


    .kpi-icon.sellers {
        background: #ede9fe;
    }

    .kpi-icon.points {
        background: #fce7f3;
    }

    .kpi-icon.attendance {
        background: #dbeafe;
    }

    .kpi-icon.present {
        background: #dcfce7;
        color: #166534;
    }

    .kpi-icon.late {
        background: #ffedd5;
    }

    .kpi-icon.open {
        background: #dbeafe;
    }

    .kpi-icon.early {
        background: #fef3c7;
    }

    .kpi-icon.outside {
        background: #fee2e2;
        color: #991b1b;
    }


    .kpi-card span {
        display: block;
        margin-bottom: 6px;
        color: #64748b;
        font-size: 14px;
        font-weight: 500;
    }


    .kpi-card strong {
        display: block;
        color: #172033;
        font-size: 30px;
        font-weight: 700;
        line-height: 1;
    }


    /* =========================================================
       COBERTURA
    ========================================================= */

    .coverage-card {
        margin-bottom: 20px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }


    .coverage-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 18px 20px;
        border-bottom: 1px solid #e5e7eb;
    }


    .coverage-header h2 {
        margin: 0;
        color: #172033;
        font-size: 16px;
    }


    .coverage-header p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 11px;
    }


    .coverage-percent {
        padding: 7px 12px;
        border-radius: 20px;
        background: #e0ecff;
        color: #1d4ed8;
        font-size: 14px;
        font-weight: 700;
    }


    .coverage-summary {
        display: grid;
        grid-template-columns:
            140px
            140px
            140px
            minmax(250px, 1fr);
        gap: 12px;
        padding: 16px 20px;
        background: #f8fafc;
    }


    .coverage-kpi {
        padding: 12px;
        border-radius: 9px;
        background: #ffffff;
    }


    .coverage-kpi span {
        display: block;
        margin-bottom: 4px;
        color: #64748b;
        font-size: 10px;
    }


    .coverage-kpi strong {
        color: #172033;
        font-size: 20px;
    }


    .coverage-kpi.success strong {
        color: #166534;
    }


    .coverage-kpi.pending strong {
        color: #b45309;
    }


    .coverage-progress {
        display: flex;
        justify-content: center;
        flex-direction: column;
        padding: 0 10px;
    }


    .progress-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 7px;
        color: #475569;
        font-size: 11px;
    }


    .progress-track {
        height: 8px;
        overflow: hidden;
        border-radius: 20px;
        background: #e2e8f0;
    }


    .progress-bar {
        height: 100%;
        border-radius: 20px;
        background: #2563eb;
        transition: width .4s ease;
    }


    .coverage-table {
        width: 100%;
        overflow-x: auto;
    }


    .coverage-table table {
        width: 100%;
        border-collapse: collapse;
    }


    .coverage-table th {
        padding: 11px 14px;
        background: #ffffff;
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
        text-align: left;
        text-transform: uppercase;
    }


    .coverage-table td {
        padding: 13px 14px;
        border-top: 1px solid #eef2f6;
        color: #334155;
        font-size: 12px;
    }


    .coverage-code {
        display: block;
        margin-top: 2px;
        color: #94a3b8;
        font-size: 9px;
    }


    .coverage-status {
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 9px;
        font-weight: 700;
    }


    .coverage-status.present {
        background: #dcfce7;
        color: #166534;
    }


    .coverage-status.late {
        background: #ffedd5;
        color: #9a3412;
    }


    .coverage-status.pending {
        background: #fef3c7;
        color: #92400e;
    }


    .coverage-status.registered {
        background: #dbeafe;
        color: #1d4ed8;
    }


    .coverage-empty {
        padding: 28px;
        color: #64748b;
        font-size: 12px;
        text-align: center;
    }


    /* =========================================================
       MAPA
    ========================================================= */

    .map-card {
        margin-bottom: 20px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }


    .map-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 18px 20px;
    }


    .map-header h2 {
        margin: 0;
        color: #172033;
        font-size: 16px;
    }


    .map-header p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 11px;
    }


    .map-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }


    .map-total {
        padding: 7px 12px;
        border-radius: 20px;
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }


    .btn-toggle-map {
        min-height: 36px;
        padding: 0 14px;
        border: 1px solid #dbe4ef;
        border-radius: 8px;
        background: #ffffff;
        color: #2454a6;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition:
            background .2s ease,
            border-color .2s ease;
    }


    .btn-toggle-map:hover {
        border-color: #bfdbfe;
        background: #eff6ff;
    }


    /*
    | El mapa inicia oculto.
    */

    .map-collapsible {
        display: none;
        border-top: 1px solid #e5e7eb;
    }


    .map-collapsible.open {
        display: block;
    }


    .map-legend {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        padding: 11px 20px;
        border-bottom: 1px solid #eef2f6;
        background: #f8fafc;
    }


    .map-legend span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #64748b;
        font-size: 10px;
        font-weight: 600;
    }


    .legend-dot {
        width: 9px;
        height: 9px;
        display: inline-block;
        border-radius: 50%;
    }


    .legend-dot.working {
        background: #16a34a;
    }


    .legend-dot.late {
        background: #f97316;
    }


    .legend-dot.finished {
        background: #2563eb;
    }


    .legend-dot.pending {
        background: #94a3b8;
    }


    .legend-dot.supervisor {
        background: #7c3aed;
    }

    .legend-dot.visited {
        background: #0f766e;
    }

    .legend-dot.current-visit {
        background: #dc2626;
    }


    #mapaVendedores {
        width: 100%;
        height: 480px;
        z-index: 1;
    }


    .map-empty {
        padding: 50px 20px;
        color: #64748b;
        font-size: 12px;
        text-align: center;
    }


    /* =========================================================
       POPUP MAPA
    ========================================================= */

    .seller-map-popup {
        min-width: 210px;
    }


    .seller-map-popup h3 {
        margin: 0 0 4px;
        color: #172033;
        font-size: 14px;
    }


    .seller-map-code {
        display: block;
        margin-bottom: 10px;
        color: #94a3b8;
        font-size: 9px;
    }


    .seller-map-row {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        padding: 5px 0;
        border-bottom: 1px solid #eef2f6;
        font-size: 10px;
    }


    .seller-map-row:last-child {
        border-bottom: 0;
    }


    .seller-map-row span {
        color: #64748b;
    }


    .seller-map-row strong {
        color: #172033;
        text-align: right;
    }


    .location-warning {
        margin-top: 8px;
        padding: 7px;
        border-radius: 6px;
        background: #fef3c7;
        color: #92400e;
        font-size: 9px;
        line-height: 1.4;
    }


    /* =========================================================
       ACTIVIDAD
    ========================================================= */

    .activity-card {
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }


    .activity-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        padding: 17px 19px;
        border-bottom: 1px solid #e5e7eb;
    }


    .activity-header h2 {
        margin: 0;
        color: #172033;
        font-size: 16px;
    }


    .activity-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 11px;
    }


    .btn-view-all {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 13px;
        border: 1px solid #dbe4ef;
        border-radius: 7px;
        background: #ffffff;
        color: #2454a6;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
    }


    .btn-view-all:hover {
        background: #f8fafc;
    }


    /* =========================================================
       TABLA
    ========================================================= */

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


    tbody tr:last-child td {
        border-bottom: 0;
    }


    /* =========================================================
       VENDEDOR
    ========================================================= */

    .seller {
        min-width: 150px;
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


    .distance {
        display: block;
        margin-top: 2px;
        color: #94a3b8;
        font-size: 9px;
    }


    /* =========================================================
       BADGES
    ========================================================= */

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
        display: flex;
        flex-direction: column;
        gap: 4px;
        align-items: flex-start;
    }


    /* =========================================================
       DETALLE
    ========================================================= */

    .btn-detail {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border: 1px solid #dbe4ef;
        border-radius: 7px;
        background: #ffffff;
        color: #2454a6;
        font-size: 10px;
        font-weight: 600;
        text-decoration: none;
    }


    .btn-detail:hover {
        background: #f1f5f9;
    }


    /* =========================================================
       VACÍO
    ========================================================= */

    .empty-state {
        padding: 55px 20px;
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
        background: #dcfce7;
        color: #166534;
        font-size: 21px;
    }


    .empty-state h3 {
        margin: 0 0 5px;
    }


    .empty-state p {
        margin: 0;
        color: #64748b;
        font-size: 12px;
    }


    /* =========================================================
       TABLET
    ========================================================= */

    @media(max-width: 1100px) {

        .kpi-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }


        .coverage-summary {
            grid-template-columns:
                repeat(3, 1fr);
        }


        .coverage-progress {
            grid-column: 1 / -1;
            padding: 8px 0;
        }

    }


    /* =========================================================
       MOBILE
    ========================================================= */

    @media(max-width: 700px) {

        .page-header h1 {
            font-size: 24px;
        }


        .kpi-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 8px;
        }


        .kpi-card {
            min-height: 110px;
            gap: 16px;
            padding: 20px;
        }


        .kpi-icon {
            width: 36px;
            height: 36px;
            flex-basis: 36px;
            font-size: 14px;
        }


        .kpi-card span {
            font-size: 11px;
        }


        .kpi-card strong {
            font-size: 22px;
        }


        .coverage-header {
            align-items: flex-start;
        }


        .coverage-summary {
            grid-template-columns:
                repeat(3, 1fr);
            padding: 12px;
        }


        .coverage-kpi {
            padding: 10px 7px;
            text-align: center;
        }


        .coverage-kpi span {
            font-size: 8px;
        }


        .coverage-kpi strong {
            font-size: 18px;
        }


        .coverage-table {
            overflow: visible;
        }


        .coverage-table table,
        .coverage-table tbody,
        .coverage-table tr,
        .coverage-table td {
            display: block;
            width: 100%;
        }


        .coverage-table thead {
            display: none;
        }


        .coverage-table tr {
            padding: 8px 0;
            border-top: 1px solid #eef2f6;
        }


        .coverage-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 7px 13px;
            border: 0;
            text-align: right;
        }


        .coverage-table td::before {
            content: attr(data-label);
            color: #64748b;
            font-size: 9px;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
        }


        .map-header {
            align-items: flex-start;
            flex-direction: column;
        }


        .map-actions {
            width: 100%;
            justify-content: space-between;
        }


        .btn-toggle-map {
            flex: 1;
        }


        .map-legend {
            gap: 10px;
        }


        #mapaVendedores {
            height: 390px;
        }


        .activity-header {
            align-items: stretch;
            flex-direction: column;
        }


        .btn-view-all {
            width: 100%;
        }


        .activity-card {
            overflow: visible;
            border: 0;
            background: transparent;
        }


        .activity-header {
            margin-bottom: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #ffffff;
        }


        .table-responsive {
            overflow: visible;
        }


        .table-responsive table,
        .table-responsive tbody,
        .table-responsive tr,
        .table-responsive td {
            display: block;
            width: 100%;
        }


        .table-responsive thead {
            display: none;
        }


        .table-responsive tbody tr {
            margin-bottom: 10px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #ffffff;
        }


        .table-responsive tbody tr td {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 10px 12px;
            border-bottom: 1px solid #eef2f6;
            text-align: right;
        }


        .table-responsive tbody tr td:last-child {
            border-bottom: 0;
        }


        .table-responsive tbody tr td::before {
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
            align-items: flex-end;
        }

    }


    @media(max-width: 430px) {

        .kpi-grid {
            grid-template-columns: 1fr;
        }

    }

</style>

@endpush



@push('scripts')

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
></script>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | ELEMENTOS
        |--------------------------------------------------------------------------
        */

        const botonMapa =
            document.getElementById(
                'btnToggleMapa'
            );


        const contenedorMapa =
            document.getElementById(
                'mapaContainer'
            );


        const elementoMapa =
            document.getElementById(
                'mapaVendedores'
            );


        if (
            !botonMapa ||
            !contenedorMapa
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | DATOS
        |--------------------------------------------------------------------------
        */

        const vendedores =
            @json($mapaVendedores);

        const supervisores =
            @json($mapaSupervisores);

        const visitasSupervisor =
            @json($mapaVisitasSupervisor);


        /*
        |--------------------------------------------------------------------------
        | VARIABLES DEL MAPA
        |--------------------------------------------------------------------------
        */

        let mapa = null;

        let mapaInicializado = false;


        /*
        |--------------------------------------------------------------------------
        | ESCAPAR HTML
        |--------------------------------------------------------------------------
        */

        function escaparHtml(valor)
        {
            if (
                valor === null ||
                valor === undefined
            ) {
                return '';
            }


            return String(valor)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }


        /*
        |--------------------------------------------------------------------------
        | INICIALIZAR MAPA
        |--------------------------------------------------------------------------
        */

        function inicializarMapa()
        {

            if (
                mapaInicializado ||
                !elementoMapa
            ) {
                return;
            }


            if (
                (!Array.isArray(vendedores) || vendedores.length === 0) &&
                (!Array.isArray(supervisores) || supervisores.length === 0) &&
                (!Array.isArray(visitasSupervisor) || visitasSupervisor.length === 0)
            ) {

                mapaInicializado = true;

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | CREAR LEAFLET
            |--------------------------------------------------------------------------
            */

            mapa =
                L.map(
                    'mapaVendedores',
                    {
                        zoomControl: true
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | OPENSTREETMAP
            |--------------------------------------------------------------------------
            */

            L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                {
                    maxZoom: 19,

                    attribution:
                        '&copy; OpenStreetMap contributors'
                }
            ).addTo(mapa);


            const limites = [];


            /*
            |--------------------------------------------------------------------------
            | VENDEDORES
            |--------------------------------------------------------------------------
            */

            vendedores.forEach(
                function (vendedor) {

                    const lat =
                        parseFloat(
                            vendedor.latitud
                        );


                    const lng =
                        parseFloat(
                            vendedor.longitud
                        );


                    if (
                        Number.isNaN(lat) ||
                        Number.isNaN(lng)
                    ) {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | COLOR
                    |--------------------------------------------------------------------------
                    */

                    let color =
                        '#94a3b8';


                    if (
                        vendedor.estado_jornada ===
                        'FINALIZADA'
                    ) {

                        color =
                            '#2563eb';

                    } else if (
                        vendedor.estado ===
                        'TARDE'
                    ) {

                        color =
                            '#f97316';

                    } else if (
                        vendedor.estado_jornada ===
                        'EN_JORNADA'
                    ) {

                        color =
                            '#16a34a';

                    }


                    const nombre =
                        escaparHtml(
                            vendedor.vendedor
                        );


                    const inicial =
                        nombre
                            ? nombre
                                .charAt(0)
                                .toUpperCase()
                            : 'V';


                    /*
                    |--------------------------------------------------------------------------
                    | ICONO
                    |--------------------------------------------------------------------------
                    */

                    const icono =
                        L.divIcon({

                            className:
                                'fieldcontrol-map-marker',

                            html: `
                                <div
                                    style="
                                        width:36px;
                                        height:36px;
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                        border:3px solid #ffffff;
                                        border-radius:50%;
                                        background:${color};
                                        color:#ffffff;
                                        box-shadow:0 2px 8px rgba(15,23,42,.28);
                                        font-size:15px;
                                        font-weight:700;
                                    "
                                >
                                    ${inicial}
                                </div>
                            `,

                            iconSize:
                                [36, 36],

                            iconAnchor:
                                [18, 18],

                            popupAnchor:
                                [0, -20]

                        });


                    /*
                    |--------------------------------------------------------------------------
                    | MARCADOR
                    |--------------------------------------------------------------------------
                    */

                    const marcador =
                        L.marker(
                            [lat, lng],
                            {
                                icon: icono
                            }
                        )
                        .addTo(mapa);


                    /*
                    |--------------------------------------------------------------------------
                    | TIPO DE UBICACIÓN
                    |--------------------------------------------------------------------------
                    */

                    let ubicacionTexto =
                        'Punto asignado';


                    if (
                        vendedor.tipo_ubicacion ===
                        'LLEGADA'
                    ) {

                        ubicacionTexto =
                            'Registro de llegada';

                    } else if (
                        vendedor.tipo_ubicacion ===
                        'SALIDA'
                    ) {

                        ubicacionTexto =
                            'Registro de salida';

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | POPUP
                    |--------------------------------------------------------------------------
                    */

                    let popup = `

                        <div class="seller-map-popup">

                            <h3>
                                ${nombre}
                            </h3>

                            <span class="seller-map-code">
                                ${escaparHtml(vendedor.codigo)}
                            </span>


                            <div class="seller-map-row">

                                <span>
                                    Punto
                                </span>

                                <strong>
                                    ${escaparHtml(vendedor.punto)}
                                </strong>

                            </div>


                            <div class="seller-map-row">

                                <span>
                                    Estado
                                </span>

                                <strong>
                                    ${escaparHtml(
                                        vendedor.estado_jornada
                                            .replaceAll('_', ' ')
                                    )}
                                </strong>

                            </div>


                            <div class="seller-map-row">

                                <span>
                                    Asistencia
                                </span>

                                <strong>
                                    ${escaparHtml(
                                        vendedor.estado
                                            .replaceAll('_', ' ')
                                    )}
                                </strong>

                            </div>


                            <div class="seller-map-row">

                                <span>
                                    Ubicación
                                </span>

                                <strong>
                                    ${ubicacionTexto}
                                </strong>

                            </div>

                    `;


                    /*
                    |--------------------------------------------------------------------------
                    | ENTRADA
                    |--------------------------------------------------------------------------
                    */

                    if (
                        vendedor.hora_llegada
                    ) {

                        popup += `

                            <div class="seller-map-row">

                                <span>
                                    Entrada
                                </span>

                                <strong>
                                    ${escaparHtml(
                                        vendedor.hora_llegada
                                    )}
                                </strong>

                            </div>

                        `;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SALIDA
                    |--------------------------------------------------------------------------
                    */

                    if (
                        vendedor.hora_salida
                    ) {

                        popup += `

                            <div class="seller-map-row">

                                <span>
                                    Salida
                                </span>

                                <strong>
                                    ${escaparHtml(
                                        vendedor.hora_salida
                                    )}
                                </strong>

                            </div>

                        `;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | DISTANCIA
                    |--------------------------------------------------------------------------
                    */

                    if (
                        vendedor.distancia !== null &&
                        vendedor.distancia !== undefined
                    ) {

                        popup += `

                            <div class="seller-map-row">

                                <span>
                                    Distancia
                                </span>

                                <strong>
                                    ${escaparHtml(
                                        vendedor.distancia
                                    )} m
                                </strong>

                            </div>

                        `;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PUNTO ASIGNADO, NO UBICACIÓN REAL
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !vendedor.ubicacion_real
                    ) {

                        popup += `

                            <div class="location-warning">

                                Este vendedor todavía no ha
                                registrado una ubicación hoy.

                                El marcador corresponde únicamente
                                al punto de venta asignado.

                            </div>

                        `;

                    }


                    popup += `
                        </div>
                    `;


                    marcador.bindPopup(
                        popup,
                        {
                            maxWidth: 310
                        }
                    );


                    limites.push(
                        [lat, lng]
                    );

                }
            );



            /*
            |--------------------------------------------------------------------------
            | PUNTOS VISITADOS POR SUPERVISORES
            |--------------------------------------------------------------------------
            */

            visitasSupervisor.forEach(
                function (visita) {

                    const lat = parseFloat(visita.latitud);
                    const lng = parseFloat(visita.longitud);

                    if (Number.isNaN(lat) || Number.isNaN(lng)) {
                        return;
                    }

                    const enCurso =
                        visita.estado === 'EN_VISITA';

                    const color =
                        enCurso ? '#dc2626' : '#0f766e';

                    const icono =
                        L.divIcon({
                            className: 'fieldcontrol-visit-marker',
                            html: `
                                <div
                                    style="
                                        width:26px;
                                        height:26px;
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                        border:3px solid #ffffff;
                                        border-radius:50%;
                                        background:${color};
                                        color:#ffffff;
                                        box-shadow:0 2px 7px rgba(15,23,42,.25);
                                        font-size:12px;
                                        font-weight:800;
                                    "
                                >
                                    ${enCurso ? '●' : '✓'}
                                </div>
                            `,
                            iconSize: [26, 26],
                            iconAnchor: [13, 13],
                            popupAnchor: [0, -15]
                        });

                    const marcador =
                        L.marker([lat, lng], { icon: icono })
                            .addTo(mapa);

                    marcador.bindPopup(`
                        <div class="seller-map-popup">
                            <h3>${escaparHtml(visita.punto)}</h3>

                            <span class="seller-map-code">
                                ${enCurso ? 'VISITA EN CURSO' : 'PUNTO SUPERVISADO'}
                            </span>

                            <div class="seller-map-row">
                                <span>Supervisor</span>
                                <strong>${escaparHtml(visita.supervisor)}</strong>
                            </div>

                            <div class="seller-map-row">
                                <span>Llegada</span>
                                <strong>${escaparHtml(visita.hora_llegada || '-')}</strong>
                            </div>

                            <div class="seller-map-row">
                                <span>Salida</span>
                                <strong>${escaparHtml(visita.hora_salida || 'En curso')}</strong>
                            </div>
                        </div>
                    `);

                    limites.push([lat, lng]);
                }
            );


            /*
            |--------------------------------------------------------------------------
            | UBICACIÓN ACTUAL DE SUPERVISORES
            |--------------------------------------------------------------------------
            */

            supervisores.forEach(
                function (supervisor) {

                    const lat = parseFloat(supervisor.latitud);
                    const lng = parseFloat(supervisor.longitud);

                    if (Number.isNaN(lat) || Number.isNaN(lng)) {
                        return;
                    }

                    const nombre =
                        escaparHtml(supervisor.supervisor);

                    const inicial =
                        nombre
                            ? nombre.charAt(0).toUpperCase()
                            : 'S';

                    const icono =
                        L.divIcon({
                            className: 'fieldcontrol-supervisor-marker',
                            html: `
                                <div
                                    style="
                                        width:42px;
                                        height:42px;
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                        border:4px solid #ffffff;
                                        border-radius:50%;
                                        background:#7c3aed;
                                        color:#ffffff;
                                        box-shadow:0 3px 10px rgba(15,23,42,.35);
                                        font-size:16px;
                                        font-weight:800;
                                    "
                                >
                                    ${inicial}
                                </div>
                            `,
                            iconSize: [42, 42],
                            iconAnchor: [21, 21],
                            popupAnchor: [0, -23]
                        });

                    const marcador =
                        L.marker([lat, lng], { icon: icono })
                            .addTo(mapa);

                    let popup = `
                        <div class="seller-map-popup">

                            <h3>${nombre}</h3>

                            <span class="seller-map-code">
                                SUPERVISOR
                            </span>

                            <div class="seller-map-row">
                                <span>Última ubicación</span>
                                <strong>
                                    ${escaparHtml(supervisor.fecha_hora || '-')}
                                </strong>
                            </div>

                            <div class="seller-map-row">
                                <span>Precisión</span>
                                <strong>
                                    ${supervisor.precision !== null
                                        ? '±' + escaparHtml(supervisor.precision) + ' m'
                                        : '-'}
                                </strong>
                            </div>

                            <div class="seller-map-row">
                                <span>Visitas completadas</span>
                                <strong>
                                    ${Array.isArray(supervisor.visitas)
                                        ? supervisor.visitas.length
                                        : 0}
                                </strong>
                            </div>
                    `;

                    if (supervisor.visita_actual) {

                        popup += `
                            <div class="seller-map-row">
                                <span>Actualmente</span>
                                <strong>
                                    ${escaparHtml(supervisor.visita_actual.punto)}
                                </strong>
                            </div>

                            <div class="seller-map-row">
                                <span>Desde</span>
                                <strong>
                                    ${escaparHtml(supervisor.visita_actual.hora_llegada || '-')}
                                </strong>
                            </div>
                        `;

                    } else {

                        popup += `
                            <div class="seller-map-row">
                                <span>Visita actual</span>
                                <strong>Sin visita abierta</strong>
                            </div>
                        `;
                    }

                    if (!supervisor.ubicacion_hoy) {

                        popup += `
                            <div class="location-warning">
                                La última ubicación disponible no corresponde al día de hoy.
                            </div>
                        `;
                    }

                    if (
                        Array.isArray(supervisor.visitas) &&
                        supervisor.visitas.length > 0
                    ) {

                        popup += `
                            <div style="
                                margin-top:10px;
                                color:#172033;
                                font-size:10px;
                                font-weight:700;
                            ">
                                Puntos supervisados hoy
                            </div>
                        `;

                        supervisor.visitas.forEach(
                            function (visita) {

                                popup += `
                                    <div class="seller-map-row">
                                        <span>
                                            ${escaparHtml(visita.punto)}
                                        </span>

                                        <strong>
                                            ${escaparHtml(visita.hora_llegada || '-')}
                                            -
                                            ${escaparHtml(visita.hora_salida || '-')}
                                        </strong>
                                    </div>
                                `;
                            }
                        );
                    }

                    popup += `</div>`;

                    marcador.bindPopup(
                        popup,
                        {
                            maxWidth: 340
                        }
                    );

                    limites.push([lat, lng]);
                }
            );


            /*
            |--------------------------------------------------------------------------
            | RECORRIDO ENTRE PUNTOS VISITADOS
            |--------------------------------------------------------------------------
            */

            const recorridos = {};

            visitasSupervisor.forEach(
                function (visita) {

                    const lat = parseFloat(visita.latitud);
                    const lng = parseFloat(visita.longitud);

                    if (
                        Number.isNaN(lat) ||
                        Number.isNaN(lng)
                    ) {
                        return;
                    }

                    if (!recorridos[visita.id_supervisor]) {
                        recorridos[visita.id_supervisor] = [];
                    }

                    recorridos[visita.id_supervisor]
                        .push([lat, lng]);
                }
            );

            supervisores.forEach(
                function (supervisor) {

                    const recorrido =
                        recorridos[supervisor.id_supervisor] || [];

                    const lat =
                        parseFloat(supervisor.latitud);

                    const lng =
                        parseFloat(supervisor.longitud);

                    if (
                        !Number.isNaN(lat) &&
                        !Number.isNaN(lng)
                    ) {
                        recorrido.push([lat, lng]);
                    }

                    if (recorrido.length > 1) {

                        L.polyline(
                            recorrido,
                            {
                                weight: 3,
                                opacity: 0.55,
                                dashArray: '7, 7'
                            }
                        ).addTo(mapa);
                    }
                }
            );


            /*
            |--------------------------------------------------------------------------
            | CENTRAR
            |--------------------------------------------------------------------------
            */

            if (
                limites.length === 1
            ) {

                mapa.setView(
                    limites[0],
                    16
                );

            } else if (
                limites.length > 1
            ) {

                mapa.fitBounds(
                    limites,
                    {
                        padding:
                            [40, 40],

                        maxZoom:
                            16
                    }
                );

            }


            mapaInicializado = true;


            /*
            |--------------------------------------------------------------------------
            | CORREGIR DIMENSIONES
            |--------------------------------------------------------------------------
            */

            setTimeout(
                function () {

                    if (mapa) {
                        mapa.invalidateSize();
                    }

                },
                200
            );

        }


        /*
        |--------------------------------------------------------------------------
        | MOSTRAR / OCULTAR
        |--------------------------------------------------------------------------
        */

        botonMapa.addEventListener(
            'click',
            function () {

                const abierto =
                    contenedorMapa
                        .classList
                        .toggle('open');


                /*
                | Actualizar texto del botón.
                */

                botonMapa.textContent =
                    abierto
                        ? 'Ocultar mapa'
                        : 'Ver mapa';


                /*
                | Accesibilidad.
                */

                botonMapa.setAttribute(
                    'aria-expanded',
                    abierto
                        ? 'true'
                        : 'false'
                );


                /*
                | Crear mapa únicamente la primera vez.
                */

                if (
                    abierto &&
                    !mapaInicializado
                ) {

                    inicializarMapa();

                }


                /*
                | Leaflet necesita recalcular dimensiones
                | después de pasar de display:none a block.
                */

                if (
                    abierto &&
                    mapa
                ) {

                    setTimeout(
                        function () {

                            mapa.invalidateSize();

                        },
                        200
                    );

                }

            }
        );

    }
);

</script>

@endpush
