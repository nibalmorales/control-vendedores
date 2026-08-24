@extends('layouts.app')

@section('title', 'Mi asistencia')

@section('content')

@php
    $esSupervisor = strtoupper(auth()->user()?->rol?->nombre ?? '') === 'SUPERVISOR';
    $puntoAsignado = $asignacion?->puntoVenta;
@endphp

<div class="asistencia-wrapper">

    {{-- =====================================================
         ENCABEZADO
    ===================================================== --}}
    <div class="page-header">
        <div>
            <h1>Mi asistencia</h1>
            <p>Consulta el estado y los registros de tu jornada de hoy.</p>
        </div>
    </div>


    {{-- =====================================================
         SIN ASIGNACIÓN
    ===================================================== --}}
    @if(!$asignacion)

        <div class="empty-card">

            <div class="empty-icon">
                📅
            </div>

            <h2>No tienes una jornada asignada para hoy</h2>

            <p>
                No encontramos una asignación activa correspondiente
                al día de hoy.
            </p>

        </div>

    @else

        {{-- =====================================================
             INFORMACIÓN DE ASIGNACIÓN
        ===================================================== --}}
        <div class="assignment-card">

            <div class="assignment-header">

                <div>

                    @if($esSupervisor)

                        <div class="small-label">
                            Modalidad
                        </div>

                        <h2>
                            Supervisor móvil
                        </h2>

                        <p>
                            Puedes registrar tu jornada desde los puntos
                            de venta bajo tu supervisión.
                        </p>

                    @else

                        <div class="small-label">
                            Punto asignado
                        </div>

                        <h2>
                            {{ $puntoAsignado?->nombre ?? 'Sin punto asignado' }}
                        </h2>

                        <p>
                            {{ $puntoAsignado?->direccion ?? 'Sin dirección registrada' }}
                        </p>

                    @endif

                </div>


                @if($asistencia)

                    @php
                        $nombreEstado = $asistencia->estado?->nombre ?? 'PENDIENTE';
                    @endphp

                    <span class="
                        status-badge
                        {{ $nombreEstado === 'PRESENTE' ? 'present' : '' }}
                        {{ $nombreEstado === 'TARDE' ? 'late' : '' }}
                        {{ $nombreEstado === 'FUERA_DEL_PUNTO' ? 'outside' : '' }}
                    ">
                        {{ str_replace('_', ' ', $nombreEstado) }}
                    </span>

                @else

                    <span class="status-badge pending">
                        PENDIENTE
                    </span>

                @endif

            </div>


            <div class="info-grid {{ $esSupervisor ? 'supervisor-grid' : '' }}">

                <div class="info-box">

                    <div class="info-icon">
                        🕒
                    </div>

                    <div>
                        <span>Horario</span>

                        <strong>
                            {{ \Carbon\Carbon::parse($asignacion->horario->hora_entrada)->format('H:i') }}
                            –
                            {{ \Carbon\Carbon::parse($asignacion->horario->hora_salida)->format('H:i') }}
                        </strong>
                    </div>

                </div>


                <div class="info-box">

                    <div class="info-icon">
                        ⏱
                    </div>

                    <div>
                        <span>Tolerancia</span>

                        <strong>
                            {{ $asignacion->horario->tolerancia_minutos }}
                            minutos
                        </strong>
                    </div>

                </div>


                @if(!$esSupervisor)

                    <div class="info-box">

                        <div class="info-icon">
                            📍
                        </div>

                        <div>
                            <span>Radio permitido</span>

                            <strong>
                                @if($puntoAsignado?->radio_permitido_metros !== null)

                                    {{ number_format(
                                        $puntoAsignado->radio_permitido_metros,
                                        0
                                    ) }}
                                    metros

                                @else
                                    —
                                @endif
                            </strong>
                        </div>

                    </div>

                @else

                    <div class="info-box">

                        <div class="info-icon">
                            📍
                        </div>

                        <div>
                            <span>Ubicación</span>

                            <strong>
                                Puntos supervisados
                            </strong>
                        </div>

                    </div>

                @endif

            </div>

        </div>


        {{-- =====================================================
             SIN REGISTRO
        ===================================================== --}}
        @if(!$asistencia)

            <div class="pending-card">

                <div class="pending-icon">
                    ⏱
                </div>

                <h2>Asistencia pendiente</h2>

                <p>
                    Todavía no has registrado tu llegada para la jornada de hoy.
                </p>

                <a
                    href="{{ route('vendedor.jornada') }}"
                    class="btn-primary"
                >
                    Ir a Mi jornada
                </a>

            </div>

        @else

            @php

                /*
                |--------------------------------------------------------------------------
                | HORARIO PROGRAMADO
                |--------------------------------------------------------------------------
                */

                $horaEntradaProgramada = \Carbon\Carbon::parse(
                    $asistencia->fecha->format('Y-m-d') . ' ' .
                    $asignacion->horario->hora_entrada
                );

                $horaSalidaProgramada = \Carbon\Carbon::parse(
                    $asistencia->fecha->format('Y-m-d') . ' ' .
                    $asignacion->horario->hora_salida
                );


                /*
                |--------------------------------------------------------------------------
                | SALIDA ANTICIPADA
                |--------------------------------------------------------------------------
                */

                $esSalidaAnticipada =
                    $asistencia->hora_salida &&
                    $asistencia->hora_salida->lt(
                        $horaSalidaProgramada
                    );


                /*
                |--------------------------------------------------------------------------
                | SALIDA FUERA DEL PUNTO
                |--------------------------------------------------------------------------
                |
                | Para vendedor utilizamos el punto fijo.
                |
                | Para supervisor móvil NO podemos comparar contra
                | $asignacion->puntoVenta porque no tiene punto fijo.
                |
                */

                $esSalidaFuera = false;

                if (
                    !$esSupervisor &&
                    $puntoAsignado &&
                    $asistencia->hora_salida &&
                    $asistencia->distancia_salida_metros !== null &&
                    $puntoAsignado->radio_permitido_metros !== null
                ) {

                    $esSalidaFuera =
                        $asistencia->distancia_salida_metros >
                        $puntoAsignado->radio_permitido_metros;
                }


                /*
                |--------------------------------------------------------------------------
                | JORNADA FINALIZADA
                |--------------------------------------------------------------------------
                */

                $jornadaFinalizada =
                    !is_null($asistencia->hora_salida);


                /*
                |--------------------------------------------------------------------------
                | DURACIÓN
                |--------------------------------------------------------------------------
                */

                $duracion = null;

                if (
                    $asistencia->hora_llegada &&
                    $asistencia->hora_salida
                ) {

                    $minutosTrabajados =
                        $asistencia->hora_llegada
                            ->diffInMinutes(
                                $asistencia->hora_salida
                            );

                    $horas =
                        intdiv($minutosTrabajados, 60);

                    $minutos =
                        $minutosTrabajados % 60;

                    $duracion =
                        $horas . ' h ' .
                        $minutos . ' min';
                }

            @endphp


            {{-- =====================================================
                 RESUMEN
            ===================================================== --}}
            <div class="attendance-card">

                <div class="attendance-title">

                    <div class="
                        attendance-main-icon
                        {{ $jornadaFinalizada ? 'completed' : '' }}
                    ">
                        ✓
                    </div>

                    <div>

                        <h2>
                            {{ $jornadaFinalizada
                                ? 'Jornada finalizada'
                                : 'Jornada en curso'
                            }}
                        </h2>

                        <p>
                            {{ \Carbon\Carbon::parse($asistencia->fecha)
                                ->translatedFormat('l d \d\e F \d\e Y') }}
                        </p>

                    </div>

                </div>


                {{-- =================================================
                     ENTRADA
                ================================================= --}}

                <div class="section-title">
                    Entrada
                </div>


                <div class="attendance-grid">

                    <div class="detail-box">

                        <span>Hora programada</span>

                        <strong>
                            {{ $horaEntradaProgramada->format('H:i') }}
                        </strong>

                    </div>


                    <div class="detail-box">

                        <span>Hora registrada</span>

                        <strong>
                            {{ $asistencia->hora_llegada
                                ? $asistencia->hora_llegada->format('H:i:s')
                                : '—'
                            }}
                        </strong>

                    </div>


                    <div class="detail-box">

                        <span>
                            {{ $esSupervisor
                                ? 'Distancia al punto registrado'
                                : 'Distancia al punto'
                            }}
                        </span>

                        <strong>

                            @if($asistencia->distancia_llegada_metros !== null)

                                {{ number_format(
                                    $asistencia->distancia_llegada_metros,
                                    0
                                ) }} m

                            @else
                                —
                            @endif

                        </strong>

                    </div>


                    <div class="detail-box">

                        <span>Precisión GPS</span>

                        <strong>

                            @if($asistencia->precision_llegada_metros !== null)

                                ±{{ number_format(
                                    $asistencia->precision_llegada_metros,
                                    0
                                ) }} m

                            @else
                                —
                            @endif

                        </strong>

                    </div>

                </div>


                {{-- =================================================
                     SALIDA
                ================================================= --}}

                <div class="section-title section-separator">
                    Salida
                </div>


                @if($asistencia->hora_salida)

                    <div class="attendance-grid">

                        <div class="detail-box">

                            <span>Hora programada</span>

                            <strong>
                                {{ $horaSalidaProgramada->format('H:i') }}
                            </strong>

                        </div>


                        <div class="detail-box">

                            <span>Hora registrada</span>

                            <strong>
                                {{ $asistencia->hora_salida->format('H:i:s') }}
                            </strong>

                        </div>


                        <div class="detail-box">

                            <span>
                                {{ $esSupervisor
                                    ? 'Distancia al punto registrado'
                                    : 'Distancia al punto'
                                }}
                            </span>

                            <strong>

                                @if($asistencia->distancia_salida_metros !== null)

                                    {{ number_format(
                                        $asistencia->distancia_salida_metros,
                                        0
                                    ) }} m

                                @else
                                    —
                                @endif

                            </strong>

                        </div>


                        <div class="detail-box">

                            <span>Precisión GPS</span>

                            <strong>

                                @if($asistencia->precision_salida_metros !== null)

                                    ±{{ number_format(
                                        $asistencia->precision_salida_metros,
                                        0
                                    ) }} m

                                @else
                                    —
                                @endif

                            </strong>

                        </div>

                    </div>


                    {{-- =================================================
                         DURACIÓN
                    ================================================= --}}

                    <div class="duration-card">

                        <div class="duration-icon">
                            ◷
                        </div>

                        <div>

                            <span>
                                Tiempo registrado
                            </span>

                            <strong>
                                {{ $duracion ?? '—' }}
                            </strong>

                        </div>

                    </div>


                    {{-- =================================================
                         SALIDA ANTICIPADA
                    ================================================= --}}

                    @if($esSalidaAnticipada)

                        <div class="early-exit-box">

                            <strong>
                                Salida anticipada
                            </strong>

                            <p>
                                La jornada estaba programada hasta las
                                {{ $horaSalidaProgramada->format('H:i') }}
                                y la salida fue registrada a las
                                {{ $asistencia->hora_salida->format('H:i') }}.
                            </p>

                        </div>

                    @endif


                    {{-- =================================================
                         SALIDA FUERA DEL PUNTO
                    ================================================= --}}

                    @if($esSalidaFuera)

                        <div class="warning-box">

                            <strong>
                                Salida registrada fuera del punto
                            </strong>

                            <p>
                                Distancia registrada:
                                {{ number_format(
                                    $asistencia->distancia_salida_metros,
                                    0
                                ) }} m.

                                Radio permitido:
                                {{ number_format(
                                    $puntoAsignado->radio_permitido_metros,
                                    0
                                ) }} m.
                            </p>

                        </div>

                    @endif


                    {{-- =================================================
                         OBSERVACIONES
                    ================================================= --}}

                    @if($asistencia->observaciones)

                        <div class="observation-box">

                            <span>
                                Observaciones
                            </span>

                            <p>
                                {{ $asistencia->observaciones }}
                            </p>

                        </div>

                    @endif


                @else

                    {{-- =================================================
                         SALIDA PENDIENTE
                    ================================================= --}}

                    <div class="waiting-checkout">

                        <div class="waiting-icon">
                            ◷
                        </div>

                        <div>

                            <strong>
                                Salida pendiente
                            </strong>

                            <p>
                                Tu jornada continúa activa.
                                Cuando finalices, registra tu salida
                                desde Mi jornada.
                            </p>

                        </div>


                        <a
                            href="{{ route('vendedor.jornada') }}"
                            class="btn-secondary"
                        >
                            Registrar salida
                        </a>

                    </div>

                @endif

            </div>

        @endif

    @endif

</div>

@endsection



@push('styles')

<style>

    /* =========================================================
       GENERAL
    ========================================================= */

    .asistencia-wrapper {
        max-width: 900px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 22px;
    }

    .page-header h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
    }

    .page-header p {
        margin: 6px 0 0;
        color: #64748b;
    }


    /* =========================================================
       SIN JORNADA
    ========================================================= */

    .empty-card,
    .pending-card {
        padding: 45px 25px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
        text-align: center;
    }

    .empty-icon,
    .pending-icon {
        width: 68px;
        height: 68px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        border-radius: 50%;
        background: #f1f5f9;
        font-size: 30px;
    }

    .pending-icon {
        background: #fef3c7;
    }

    .empty-card h2,
    .pending-card h2 {
        margin: 0 0 8px;
    }

    .empty-card p,
    .pending-card p {
        margin: 0 auto 20px;
        color: #64748b;
    }


    /* =========================================================
       ASIGNACIÓN
    ========================================================= */

    .assignment-card {
        padding: 24px;
        margin-bottom: 18px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
    }

    .assignment-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 22px;
    }

    .small-label {
        margin-bottom: 4px;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .assignment-header h2 {
        margin: 0;
        font-size: 23px;
    }

    .assignment-header p {
        margin: 6px 0 0;
        color: #64748b;
    }


    /* =========================================================
       BADGES
    ========================================================= */

    .status-badge {
        display: inline-flex;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-badge.pending {
        background: #fef3c7;
        color: #92400e;
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
       INFORMACIÓN
    ========================================================= */

    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .info-box {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px;
        border-radius: 10px;
        background: #f8fafc;
    }

    .info-icon {
        font-size: 20px;
    }

    .info-box span,
    .detail-box span {
        display: block;
        margin-bottom: 3px;
        color: #64748b;
        font-size: 11px;
    }

    .info-box strong {
        font-size: 14px;
    }


    /* =========================================================
       ASISTENCIA
    ========================================================= */

    .attendance-card {
        padding: 28px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
    }

    .attendance-title {
        display: flex;
        align-items: center;
        gap: 13px;
        margin-bottom: 26px;
    }

    .attendance-main-icon {
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 50%;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 23px;
        font-weight: 700;
    }

    .attendance-main-icon.completed {
        background: #dcfce7;
        color: #166534;
    }

    .attendance-title h2 {
        margin: 0;
        font-size: 21px;
    }

    .attendance-title p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 13px;
    }


    /* =========================================================
       SECCIONES
    ========================================================= */

    .section-title {
        margin-bottom: 12px;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .section-separator {
        margin-top: 26px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }

    .attendance-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    .detail-box {
        padding: 15px;
        border-radius: 10px;
        background: #f8fafc;
    }

    .detail-box strong {
        font-size: 15px;
    }


    /* =========================================================
       DURACIÓN
    ========================================================= */

    .duration-card {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 18px;
        padding: 16px;
        border: 1px solid #dbeafe;
        border-radius: 10px;
        background: #eff6ff;
    }

    .duration-icon {
        font-size: 25px;
        color: #2563eb;
    }

    .duration-card span {
        display: block;
        margin-bottom: 3px;
        color: #64748b;
        font-size: 11px;
    }

    .duration-card strong {
        color: #0f172a;
        font-size: 17px;
    }


    /* =========================================================
       ALERTAS
    ========================================================= */

    .early-exit-box,
    .warning-box {
        margin-top: 14px;
        padding: 14px;
        border-radius: 9px;
        font-size: 13px;
    }

    .early-exit-box {
        border: 1px solid #fed7aa;
        background: #fff7ed;
        color: #9a3412;
    }

    .warning-box {
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #991b1b;
    }

    .early-exit-box strong,
    .warning-box strong {
        display: block;
        margin-bottom: 5px;
    }

    .early-exit-box p,
    .warning-box p {
        margin: 0;
        line-height: 1.5;
    }


    /* =========================================================
       OBSERVACIONES
    ========================================================= */

    .observation-box {
        margin-top: 14px;
        padding: 15px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #f8fafc;
    }

    .observation-box span {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .observation-box p {
        margin: 0;
        color: #475569;
        font-size: 13px;
        line-height: 1.55;
    }


    /* =========================================================
       SALIDA PENDIENTE
    ========================================================= */

    .waiting-checkout {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 17px;
        border: 1px solid #dbeafe;
        border-radius: 10px;
        background: #f8fbff;
    }

    .waiting-icon {
        font-size: 25px;
        color: #2563eb;
    }

    .waiting-checkout > div:nth-child(2) {
        flex: 1;
    }

    .waiting-checkout strong {
        font-size: 14px;
    }

    .waiting-checkout p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.4;
    }


    /* =========================================================
       BOTONES
    ========================================================= */

    .btn-primary,
    .btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }

    .btn-primary {
        background: #2563eb;
        color: #ffffff;
    }

    .btn-primary:hover {
        background: #1d4ed8;
    }

    .btn-secondary {
        background: #071f3d;
        color: #ffffff;
        white-space: nowrap;
    }

    .btn-secondary:hover {
        background: #0d2b52;
    }


    /* =========================================================
       MÓVIL
    ========================================================= */

    @media(max-width: 768px) {

        .asistencia-wrapper {
            max-width: none;
        }

        .page-header h1 {
            font-size: 25px;
        }

        .assignment-card,
        .attendance-card {
            padding: 18px 14px;
        }

        .assignment-header {
            flex-direction: column;
            gap: 12px;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .attendance-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .waiting-checkout {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-secondary {
            width: 100%;
        }

    }


    @media(max-width: 480px) {

        .attendance-grid {
            grid-template-columns: 1fr;
        }

        .attendance-title h2 {
            font-size: 19px;
        }

    }

</style>

@endpush
