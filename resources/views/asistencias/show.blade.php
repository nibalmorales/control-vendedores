@extends('layouts.app')

@section('title', 'Detalle de asistencia')

@section('content')

@php

    $asignacion = $asistencia->asignacion;
    $vendedor   = $asignacion?->vendedor;
    $usuario    = $vendedor?->usuario;
    $punto      = $asignacion?->puntoVenta;
    $horario    = $asignacion?->horario;

    /*
    |--------------------------------------------------------------------------
    | DETECTAR SALIDA ANTICIPADA
    |--------------------------------------------------------------------------
    */

    $salidaAnticipada = false;
    $horaSalidaProgramada = null;

    if ($asistencia->hora_salida && $horario) {

        $horaSalidaProgramada = \Carbon\Carbon::parse(
            $asistencia->fecha->format('Y-m-d') . ' ' .
            $horario->hora_salida
        );

        $salidaAnticipada =
            $asistencia->hora_salida->lt($horaSalidaProgramada);
    }


    /*
    |--------------------------------------------------------------------------
    | DETECTAR SALIDA FUERA DEL PUNTO
    |--------------------------------------------------------------------------
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


<div class="attendance-detail">


    {{-- =========================================================
         ENCABEZADO
    ========================================================== --}}

    <div class="page-top">

        <div>

            <a
                href="{{ route('asistencias.index') }}"
                class="back-link"
            >
                ← Volver a asistencias
            </a>

            <h1>Detalle de asistencia</h1>

            <p>
                Información completa de la jornada registrada.
            </p>

        </div>


        @if($asistencia->estado)

            <span class="
                status-badge
                {{ $asistencia->estado->nombre === 'PRESENTE' ? 'present' : '' }}
                {{ $asistencia->estado->nombre === 'TARDE' ? 'late' : '' }}
                {{ $asistencia->estado->nombre === 'FUERA_DEL_PUNTO' ? 'outside' : '' }}
            ">

                {{ str_replace(
                    '_',
                    ' ',
                    $asistencia->estado->nombre
                ) }}

            </span>

        @endif

    </div>



    {{-- =========================================================
         VENDEDOR
    ========================================================== --}}

    <div class="card">

        <div class="card-title">
            Vendedor
        </div>


        <div class="seller">

            <div class="avatar">

                {{ strtoupper(
                    substr(
                        $usuario?->nombre ?? 'V',
                        0,
                        1
                    )
                ) }}

            </div>


            <div>

                <h2>
                    {{ $usuario?->nombre ?? 'Vendedor' }}
                    {{ $usuario?->apellido ?? '' }}
                </h2>

                <p>
                    Código:
                    {{ $vendedor?->codigo_empleado ?? '-' }}
                </p>

            </div>

        </div>

    </div>



    {{-- =========================================================
         INFORMACIÓN GENERAL
    ========================================================== --}}

    <div class="card">

        <div class="card-title">
            Información de la jornada
        </div>


        <div class="info-grid">

            <div class="info-item">

                <span>Fecha</span>

                <strong>
                    {{ $asistencia->fecha->format('d/m/Y') }}
                </strong>

            </div>


            <div class="info-item">

                <span>Punto de venta</span>

                <strong>
                    {{ $punto?->nombre ?? '-' }}
                </strong>

            </div>


            <div class="info-item">

                <span>Horario programado</span>

                <strong>

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

                </strong>

            </div>


            <div class="info-item">

                <span>Radio permitido</span>

                <strong>

                    @if($punto)

                        {{ number_format(
                            $punto->radio_permitido_metros,
                            0
                        ) }} m

                    @else

                        -

                    @endif

                </strong>

            </div>

        </div>


        @if($punto?->direccion)

            <div class="address-box">

                <span>Dirección del punto</span>

                <strong>
                    {{ $punto->direccion }}
                </strong>

            </div>

        @endif

    </div>



    {{-- =========================================================
         ENTRADA
    ========================================================== --}}

    <div class="card">

        <div class="section-heading">

            <div>
                <span class="step-number arrival">
                    1
                </span>
            </div>


            <div>

                <h2>Entrada</h2>

                <p>
                    Registro de llegada del vendedor.
                </p>

            </div>

        </div>


        <div class="info-grid">

            <div class="info-item">

                <span>Hora registrada</span>

                <strong>
                    {{ $asistencia->hora_llegada?->format('H:i:s') ?? '-' }}
                </strong>

            </div>


            <div class="info-item">

                <span>Distancia al punto</span>

                <strong>

                    @if($asistencia->distancia_llegada_metros !== null)

                        {{ number_format(
                            $asistencia->distancia_llegada_metros,
                            0
                        ) }} m

                    @else

                        -

                    @endif

                </strong>

            </div>


            <div class="info-item">

                <span>Precisión GPS</span>

                <strong>

                    @if($asistencia->precision_llegada_metros !== null)

                        ±{{ number_format(
                            $asistencia->precision_llegada_metros,
                            0
                        ) }} m

                    @else

                        -

                    @endif

                </strong>

            </div>


            <div class="info-item">

                <span>Resultado</span>

                <strong>

                    {{ str_replace(
                        '_',
                        ' ',
                        $asistencia->estado?->nombre ?? '-'
                    ) }}

                </strong>

            </div>

        </div>


        {{-- COORDENADAS DE ENTRADA --}}

        @if(
            $asistencia->latitud_llegada !== null &&
            $asistencia->longitud_llegada !== null
        )

            <div class="location-block">

                <div class="coordinates">

                    <span>
                        Coordenadas registradas
                    </span>

                    <code>
                        {{ $asistencia->latitud_llegada }},
                        {{ $asistencia->longitud_llegada }}
                    </code>

                </div>


                <a
                    href="https://www.google.com/maps?q={{ $asistencia->latitud_llegada }},{{ $asistencia->longitud_llegada }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn-map"
                >
                    Ver entrada en Google Maps
                </a>

            </div>

        @endif

    </div>



    {{-- =========================================================
         SALIDA
    ========================================================== --}}

    <div class="card">

        <div class="section-heading">

            <div>

                <span class="step-number exit">
                    2
                </span>

            </div>


            <div>

                <h2>Salida</h2>

                <p>
                    Registro de finalización de jornada.
                </p>

            </div>

        </div>


        @if($asistencia->hora_salida)


            <div class="info-grid">

                <div class="info-item">

                    <span>Hora registrada</span>

                    <strong>
                        {{ $asistencia->hora_salida->format('H:i:s') }}
                    </strong>

                </div>


                <div class="info-item">

                    <span>Distancia al punto</span>

                    <strong>

                        @if($asistencia->distancia_salida_metros !== null)

                            {{ number_format(
                                $asistencia->distancia_salida_metros,
                                0
                            ) }} m

                        @else

                            -

                        @endif

                    </strong>

                </div>


                <div class="info-item">

                    <span>Precisión GPS</span>

                    <strong>

                        @if($asistencia->precision_salida_metros !== null)

                            ±{{ number_format(
                                $asistencia->precision_salida_metros,
                                0
                            ) }} m

                        @else

                            -

                        @endif

                    </strong>

                </div>


                <div class="info-item">

                    <span>Situación</span>

                    <strong>

                        @if(!$salidaAnticipada && !$salidaFuera)

                            Normal

                        @elseif($salidaAnticipada && $salidaFuera)

                            Anticipada / Fuera del punto

                        @elseif($salidaAnticipada)

                            Salida anticipada

                        @else

                            Fuera del punto

                        @endif

                    </strong>

                </div>

            </div>



            {{-- COORDENADAS DE SALIDA --}}

            @if(
                $asistencia->latitud_salida !== null &&
                $asistencia->longitud_salida !== null
            )

                <div class="location-block">

                    <div class="coordinates">

                        <span>
                            Coordenadas registradas
                        </span>

                        <code>
                            {{ $asistencia->latitud_salida }},
                            {{ $asistencia->longitud_salida }}
                        </code>

                    </div>


                    <a
                        href="https://www.google.com/maps?q={{ $asistencia->latitud_salida }},{{ $asistencia->longitud_salida }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn-map"
                    >
                        Ver salida en Google Maps
                    </a>

                </div>

            @endif



            {{-- SALIDA ANTICIPADA --}}

            @if(
                $salidaAnticipada &&
                $horaSalidaProgramada
            )

                <div class="alert early">

                    <strong>
                        Salida anticipada
                    </strong>

                    <p>
                        El horario programado terminaba a las
                        {{ $horaSalidaProgramada->format('H:i') }}
                        y la salida fue registrada a las
                        {{ $asistencia->hora_salida->format('H:i') }}.
                    </p>

                </div>

            @endif



            {{-- SALIDA FUERA DEL PUNTO --}}

            @if($salidaFuera && $punto)

                <div class="alert outside">

                    <strong>
                        Salida fuera del punto
                    </strong>

                    <p>

                        La salida fue registrada a

                        {{ number_format(
                            $asistencia->distancia_salida_metros,
                            0
                        ) }}

                        metros del punto de venta.

                        El radio permitido es de

                        {{ number_format(
                            $punto->radio_permitido_metros,
                            0
                        ) }}

                        metros.

                    </p>

                </div>

            @endif


        @else


            {{-- JORNADA TODAVÍA ABIERTA --}}

            <div class="open-shift">

                <span class="pulse"></span>


                <div>

                    <strong>
                        Jornada abierta
                    </strong>

                    <p>
                        El vendedor todavía no ha registrado su salida.
                    </p>

                </div>

            </div>

        @endif

    </div>



    {{-- =========================================================
         OBSERVACIONES
    ========================================================== --}}

    @if($asistencia->observaciones)

        <div class="card">

            <div class="card-title">
                Observaciones
            </div>


            <div class="observation-box">

                {{ $asistencia->observaciones }}

            </div>

        </div>

    @endif



    {{-- =========================================================
         RESUMEN DE INCIDENCIAS
    ========================================================== --}}

    @if($asistencia->hora_salida)

        <div class="card">

            <div class="card-title">
                Resumen de jornada
            </div>


            <div class="summary-status">

                @if(!$salidaAnticipada && !$salidaFuera)

                    <div class="summary-normal">

                        <div class="summary-icon">
                            ✓
                        </div>

                        <div>

                            <strong>
                                Jornada finalizada normalmente
                            </strong>

                            <p>
                                No se detectaron incidencias en el registro de salida.
                            </p>

                        </div>

                    </div>


                @else


                    @if($salidaAnticipada)

                        <div class="summary-warning">

                            <span>
                                Salida anticipada
                            </span>

                        </div>

                    @endif


                    @if($salidaFuera)

                        <div class="summary-danger">

                            <span>
                                Salida fuera del punto
                            </span>

                        </div>

                    @endif

                @endif

            </div>

        </div>

    @endif

</div>

@endsection



@push('styles')

<style>

    /* =========================================================
       GENERAL
    ========================================================= */

    .attendance-detail {
        max-width: 1000px;
        margin: 0 auto;
    }


    .page-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;

        gap: 20px;

        margin-bottom: 20px;
    }


    .page-top h1 {
        margin: 8px 0 4px;

        color: #172033;

        font-size: 28px;
        font-weight: 700;
    }


    .page-top p {
        margin: 0;

        color: #64748b;

        font-size: 13px;
    }


    .back-link {
        color: #2563eb;

        font-size: 12px;
        font-weight: 600;

        text-decoration: none;
    }


    .back-link:hover {
        text-decoration: underline;
    }



    /* =========================================================
       CARDS
    ========================================================= */

    .card {
        margin-bottom: 16px;

        padding: 22px;

        border: 1px solid #e5e7eb;
        border-radius: 12px;

        background: #ffffff;
    }


    .card-title {
        margin-bottom: 16px;

        color: #64748b;

        font-size: 11px;
        font-weight: 700;

        text-transform: uppercase;
        letter-spacing: .3px;
    }



    /* =========================================================
       VENDEDOR
    ========================================================= */

    .seller {
        display: flex;
        align-items: center;

        gap: 13px;
    }


    .avatar {
        width: 48px;
        height: 48px;

        flex: 0 0 48px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: #e8efff;
        color: #2454a6;

        font-size: 18px;
        font-weight: 700;
    }


    .seller h2 {
        margin: 0;

        color: #172033;

        font-size: 18px;
    }


    .seller p {
        margin: 4px 0 0;

        color: #64748b;

        font-size: 12px;
    }



    /* =========================================================
       GRID
    ========================================================= */

    .info-grid {
        display: grid;

        grid-template-columns:
            repeat(4, minmax(0, 1fr));

        gap: 10px;
    }


    .info-item {
        min-width: 0;

        padding: 14px;

        border-radius: 9px;

        background: #f8fafc;
    }


    .info-item span {
        display: block;

        margin-bottom: 5px;

        color: #64748b;

        font-size: 10px;
    }


    .info-item strong {
        display: block;

        color: #172033;

        font-size: 14px;

        overflow-wrap: anywhere;
    }



    /* =========================================================
       DIRECCIÓN
    ========================================================= */

    .address-box {
        margin-top: 12px;

        padding: 12px 14px;

        border: 1px solid #e2e8f0;
        border-radius: 8px;

        background: #ffffff;
    }


    .address-box span {
        display: block;

        margin-bottom: 4px;

        color: #64748b;

        font-size: 10px;
    }


    .address-box strong {
        color: #334155;

        font-size: 12px;
    }



    /* =========================================================
       SECCIONES ENTRADA / SALIDA
    ========================================================= */

    .section-heading {
        display: flex;
        align-items: center;

        gap: 11px;

        margin-bottom: 18px;
    }


    .section-heading h2 {
        margin: 0;

        color: #172033;

        font-size: 18px;
    }


    .section-heading p {
        margin: 3px 0 0;

        color: #64748b;

        font-size: 11px;
    }


    .step-number {
        width: 38px;
        height: 38px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        font-weight: 700;
    }


    .step-number.arrival {
        background: #dcfce7;
        color: #166534;
    }


    .step-number.exit {
        background: #dbeafe;
        color: #1d4ed8;
    }



    /* =========================================================
       ESTADO
    ========================================================= */

    .status-badge {
        display: inline-flex;

        padding: 6px 11px;

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



    /* =========================================================
       UBICACIÓN
    ========================================================= */

    .location-block {
        margin-top: 12px;
    }


    .coordinates {
        padding: 11px 13px;

        border: 1px solid #e2e8f0;
        border-radius: 8px;

        background: #ffffff;
    }


    .coordinates span {
        display: block;

        margin-bottom: 5px;

        color: #64748b;

        font-size: 10px;
    }


    .coordinates code {
        color: #334155;

        font-size: 12px;

        overflow-wrap: anywhere;
    }



    /* =========================================================
       GOOGLE MAPS
    ========================================================= */

    .btn-map {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        min-height: 38px;

        margin-top: 10px;

        padding: 0 13px;

        border: 1px solid #bfdbfe;
        border-radius: 7px;

        background: #eff6ff;
        color: #1d4ed8;

        font-size: 12px;
        font-weight: 600;

        text-decoration: none;

        transition:
            background .18s ease,
            border-color .18s ease,
            transform .18s ease;
    }


    .btn-map:hover {
        border-color: #93c5fd;

        background: #dbeafe;

        transform: translateY(-1px);
    }



    /* =========================================================
       ALERTAS
    ========================================================= */

    .alert {
        margin-top: 12px;

        padding: 13px;

        border-radius: 8px;

        font-size: 12px;
    }


    .alert strong {
        display: block;

        margin-bottom: 4px;
    }


    .alert p {
        margin: 0;

        line-height: 1.5;
    }


    .alert.early {
        border: 1px solid #fed7aa;

        background: #fff7ed;
        color: #9a3412;
    }


    .alert.outside {
        border: 1px solid #fecaca;

        background: #fef2f2;
        color: #991b1b;
    }



    /* =========================================================
       JORNADA ABIERTA
    ========================================================= */

    .open-shift {
        display: flex;
        align-items: center;

        gap: 12px;

        padding: 16px;

        border: 1px solid #bfdbfe;
        border-radius: 9px;

        background: #eff6ff;
        color: #1d4ed8;
    }


    .open-shift strong {
        display: block;
    }


    .open-shift p {
        margin: 3px 0 0;

        font-size: 11px;
    }


    .pulse {
        width: 10px;
        height: 10px;

        flex: 0 0 10px;

        border-radius: 50%;

        background: #2563eb;

        animation: pulseAnimation 1.8s infinite;
    }


    @keyframes pulseAnimation {

        0% {
            box-shadow:
                0 0 0 0 rgba(37, 99, 235, .35);
        }

        70% {
            box-shadow:
                0 0 0 8px rgba(37, 99, 235, 0);
        }

        100% {
            box-shadow:
                0 0 0 0 rgba(37, 99, 235, 0);
        }

    }



    /* =========================================================
       OBSERVACIONES
    ========================================================= */

    .observation-box {
        padding: 14px;

        border: 1px solid #e2e8f0;
        border-radius: 8px;

        background: #f8fafc;

        color: #475569;

        font-size: 13px;
        line-height: 1.6;

        white-space: pre-line;
    }



    /* =========================================================
       RESUMEN
    ========================================================= */

    .summary-normal {
        display: flex;
        align-items: center;

        gap: 11px;

        padding: 14px;

        border: 1px solid #bbf7d0;
        border-radius: 9px;

        background: #f0fdf4;

        color: #166534;
    }


    .summary-icon {
        width: 34px;
        height: 34px;

        flex: 0 0 34px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: #dcfce7;

        font-size: 16px;
        font-weight: 700;
    }


    .summary-normal strong {
        display: block;
    }


    .summary-normal p {
        margin: 3px 0 0;

        font-size: 11px;
    }


    .summary-warning,
    .summary-danger {
        display: inline-flex;

        margin-right: 7px;
        margin-bottom: 7px;

        padding: 7px 11px;

        border-radius: 20px;

        font-size: 11px;
        font-weight: 700;
    }


    .summary-warning {
        background: #ffedd5;
        color: #9a3412;
    }


    .summary-danger {
        background: #fee2e2;
        color: #991b1b;
    }



    /* =========================================================
       TABLET
    ========================================================= */

    @media(max-width: 850px) {

        .info-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

    }



    /* =========================================================
       MÓVIL
    ========================================================= */

    @media(max-width: 600px) {

        .attendance-detail {
            width: 100%;
        }


        .page-top {
            flex-direction: column;

            gap: 12px;
        }


        .page-top h1 {
            font-size: 24px;
        }


        .card {
            padding: 17px 14px;

            border-radius: 10px;
        }


        .info-grid {
            grid-template-columns: 1fr;
        }


        .seller {
            align-items: flex-start;
        }


        .btn-map {
            width: 100%;
        }


        .coordinates code {
            font-size: 11px;
        }

    }

</style>

@endpush
