@extends('layouts.app')

@section('title', 'Mi historial')

@section('content')

<div class="historial-wrapper">

    {{-- =====================================================
         ENCABEZADO
    ===================================================== --}}
    <div class="page-header">
        <div>
            <h1>Mi historial</h1>
            <p>Consulta tus jornadas y registros de asistencia anteriores.</p>
        </div>
    </div>


    {{-- =====================================================
         FILTROS
    ===================================================== --}}
    <div class="filter-card">

        <form
            method="GET"
            action="{{ route('vendedor.historial') }}"
            class="filter-form"
        >

            <div class="filter-group">
                <label for="desde">
                    Desde
                </label>

                <input
                    type="date"
                    name="desde"
                    id="desde"
                    value="{{ request('desde') }}"
                >
            </div>


            <div class="filter-group">
                <label for="hasta">
                    Hasta
                </label>

                <input
                    type="date"
                    name="hasta"
                    id="hasta"
                    value="{{ request('hasta') }}"
                >
            </div>


            <div class="filter-actions">

                <button
                    type="submit"
                    class="btn-filter"
                >
                    Filtrar
                </button>

                @if(request('desde') || request('hasta'))

                    <a
                        href="{{ route('vendedor.historial') }}"
                        class="btn-clear"
                    >
                        Limpiar
                    </a>

                @endif

            </div>

        </form>

    </div>


    {{-- =====================================================
         SIN REGISTROS
    ===================================================== --}}
    @if($asistencias->count() === 0)

        <div class="empty-card">

            <div class="empty-icon">
                🕘
            </div>

            <h2>No hay registros disponibles</h2>

            <p>
                No encontramos jornadas registradas para el período seleccionado.
            </p>

        </div>


    @else

        {{-- =====================================================
             RESUMEN
        ===================================================== --}}
        <div class="summary-card">

            <div>
                <span>Registros encontrados</span>

                <strong>
                    {{ $asistencias->total() }}
                </strong>
            </div>

            <div>
                <span>Página actual</span>

                <strong>
                    {{ $asistencias->currentPage() }}
                    de
                    {{ $asistencias->lastPage() }}
                </strong>
            </div>

        </div>


        {{-- =====================================================
             HISTORIAL
        ===================================================== --}}
        <div class="history-list">

            @foreach($asistencias as $asistencia)

                @php

                    $asignacionActual =
                        $asignaciones->get(
                            $asistencia->id_asignacion
                        );


                    $horaEntradaProgramada = null;
                    $horaSalidaProgramada = null;

                    $salidaAnticipada = false;
                    $salidaFuera = false;
                    $duracion = null;


                    if (
                        $asignacionActual &&
                        $asignacionActual->horario
                    ) {

                        $fechaBase =
                            \Carbon\Carbon::parse(
                                $asistencia->fecha
                            )->format('Y-m-d');


                        $horaEntradaProgramada =
                            \Carbon\Carbon::parse(
                                $fechaBase . ' ' .
                                $asignacionActual
                                    ->horario
                                    ->hora_entrada
                            );


                        $horaSalidaProgramada =
                            \Carbon\Carbon::parse(
                                $fechaBase . ' ' .
                                $asignacionActual
                                    ->horario
                                    ->hora_salida
                            );


                        if (
                            $asistencia->hora_salida &&
                            $horaSalidaProgramada
                        ) {

                            $salidaAnticipada =
                                $asistencia
                                    ->hora_salida
                                    ->lt(
                                        $horaSalidaProgramada
                                    );

                        }


                        if (
                            $asistencia->hora_salida &&
                            $asistencia->distancia_salida_metros !== null &&
                            $asignacionActual->puntoVenta
                        ) {

                            $salidaFuera =
                                $asistencia
                                    ->distancia_salida_metros
                                >
                                $asignacionActual
                                    ->puntoVenta
                                    ->radio_permitido_metros;

                        }

                    }


                    if (
                        $asistencia->hora_llegada &&
                        $asistencia->hora_salida
                    ) {

                        $minutosTrabajados =
                            $asistencia
                                ->hora_llegada
                                ->diffInMinutes(
                                    $asistencia->hora_salida
                                );


                        $horas =
                            intdiv(
                                $minutosTrabajados,
                                60
                            );


                        $minutos =
                            $minutosTrabajados % 60;


                        $duracion =
                            $horas .
                            ' h ' .
                            $minutos .
                            ' min';

                    }

                @endphp


                <div class="history-card">

                    {{-- =================================================
                         CABECERA
                    ================================================= --}}
                    <div class="history-header">

                        <div>

                            <div class="history-date">

                                {{ \Carbon\Carbon::parse(
                                    $asistencia->fecha
                                )->translatedFormat(
                                    'l d \d\e F \d\e Y'
                                ) }}

                            </div>


                            @if(
                                $asignacionActual &&
                                $asignacionActual->puntoVenta
                            )

                                <h2>
                                    {{ $asignacionActual
                                        ->puntoVenta
                                        ->nombre
                                    }}
                                </h2>

                                <p>
                                    {{ $asignacionActual
                                        ->puntoVenta
                                        ->direccion
                                    }}
                                </p>

                            @else

                                <h2>
                                    Punto no disponible
                                </h2>

                            @endif

                        </div>


                        @if($asistencia->estado)

                            <span class="
                                status-badge
                                {{ $asistencia->estado->nombre === 'PRESENTE'
                                    ? 'present'
                                    : ''
                                }}
                                {{ $asistencia->estado->nombre === 'TARDE'
                                    ? 'late'
                                    : ''
                                }}
                            ">

                                {{ str_replace(
                                    '_',
                                    ' ',
                                    $asistencia
                                        ->estado
                                        ->nombre
                                ) }}

                            </span>

                        @endif

                    </div>


                    {{-- =================================================
                         HORARIO
                    ================================================= --}}
                    @if(
                        $horaEntradaProgramada &&
                        $horaSalidaProgramada
                    )

                        <div class="schedule-box">

                            <span>
                                Horario programado
                            </span>

                            <strong>

                                {{ $horaEntradaProgramada
                                    ->format('H:i')
                                }}

                                –

                                {{ $horaSalidaProgramada
                                    ->format('H:i')
                                }}

                            </strong>

                        </div>

                    @endif


                    {{-- =================================================
                         REGISTROS
                    ================================================= --}}
                    <div class="history-grid">

                        <div class="history-detail">

                            <span>
                                Entrada
                            </span>

                            <strong>

                                {{ $asistencia->hora_llegada
                                    ? $asistencia
                                        ->hora_llegada
                                        ->format('H:i:s')
                                    : '—'
                                }}

                            </strong>

                        </div>


                        <div class="history-detail">

                            <span>
                                Salida
                            </span>

                            <strong>

                                {{ $asistencia->hora_salida
                                    ? $asistencia
                                        ->hora_salida
                                        ->format('H:i:s')
                                    : '—'
                                }}

                            </strong>

                        </div>


                        <div class="history-detail">

                            <span>
                                Tiempo registrado
                            </span>

                            <strong>
                                {{ $duracion ?? '—' }}
                            </strong>

                        </div>


                        <div class="history-detail">

                            <span>
                                Distancia entrada
                            </span>

                            <strong>

                                @if(
                                    $asistencia
                                        ->distancia_llegada_metros
                                    !== null
                                )

                                    {{ number_format(
                                        $asistencia
                                            ->distancia_llegada_metros,
                                        0
                                    ) }}
                                    m

                                @else
                                    —
                                @endif

                            </strong>

                        </div>


                        <div class="history-detail">

                            <span>
                                Distancia salida
                            </span>

                            <strong>

                                @if(
                                    $asistencia
                                        ->distancia_salida_metros
                                    !== null
                                )

                                    {{ number_format(
                                        $asistencia
                                            ->distancia_salida_metros,
                                        0
                                    ) }}
                                    m

                                @else
                                    —
                                @endif

                            </strong>

                        </div>


                        <div class="history-detail">

                            <span>
                                Precisión GPS entrada
                            </span>

                            <strong>

                                @if(
                                    $asistencia
                                        ->precision_llegada_metros
                                    !== null
                                )

                                    ±{{ number_format(
                                        $asistencia
                                            ->precision_llegada_metros,
                                        0
                                    ) }}
                                    m

                                @else
                                    —
                                @endif

                            </strong>

                        </div>

                    </div>


                    {{-- =================================================
                         ALERTAS
                    ================================================= --}}

                    @if($salidaAnticipada)

                        <div class="early-box">

                            <strong>
                                Salida anticipada
                            </strong>

                            <p>

                                La salida estaba programada
                                para las

                                {{ $horaSalidaProgramada
                                    ->format('H:i')
                                }}

                                y fue registrada a las

                                {{ $asistencia
                                    ->hora_salida
                                    ->format('H:i')
                                }}.

                            </p>

                        </div>

                    @endif


                    @if($salidaFuera)

                        <div class="warning-box">

                            <strong>
                                Salida fuera del punto
                            </strong>

                            <p>

                                Distancia registrada:

                                {{ number_format(
                                    $asistencia
                                        ->distancia_salida_metros,
                                    0
                                ) }}
                                m.

                                @if(
                                    $asignacionActual &&
                                    $asignacionActual->puntoVenta
                                )

                                    Radio permitido:

                                    {{ number_format(
                                        $asignacionActual
                                            ->puntoVenta
                                            ->radio_permitido_metros,
                                        0
                                    ) }}
                                    m.

                                @endif

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

                </div>

            @endforeach

        </div>


        {{-- =====================================================
             PAGINACIÓN
        ===================================================== --}}
        @if($asistencias->hasPages())

            <div class="pagination-wrapper">
                {{ $asistencias->links() }}
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

    .historial-wrapper {
        max-width: 1000px;
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
       FILTROS
    ========================================================= */

    .filter-card {
        padding: 18px;
        margin-bottom: 18px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
    }

    .filter-form {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .filter-group {
        flex: 1;
        min-width: 180px;
    }

    .filter-group label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
    }

    .filter-group input {
        width: 100%;
        min-height: 42px;
        padding: 8px 10px;
        border: 1px solid #d5dbe5;
        border-radius: 8px;
        background: #ffffff;
        color: #172033;
        font-size: 13px;
        outline: none;
    }

    .filter-group input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
    }

    .filter-actions {
        display: flex;
        gap: 8px;
    }

    .btn-filter,
    .btn-clear {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }

    .btn-filter {
        border: 0;
        background: #2563eb;
        color: #ffffff;
        cursor: pointer;
    }

    .btn-filter:hover {
        background: #1d4ed8;
    }

    .btn-clear {
        border: 1px solid #d5dbe5;
        background: #ffffff;
        color: #475569;
    }


    /* =========================================================
       RESUMEN
    ========================================================= */

    .summary-card {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 18px;
    }

    .summary-card > div {
        padding: 15px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #ffffff;
    }

    .summary-card span {
        display: block;
        margin-bottom: 4px;
        color: #64748b;
        font-size: 11px;
    }

    .summary-card strong {
        font-size: 18px;
    }


    /* =========================================================
       HISTORIAL
    ========================================================= */

    .history-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .history-card {
        padding: 22px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
    }

    .history-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 17px;
    }

    .history-date {
        margin-bottom: 5px;
        color: #2563eb;
        font-size: 12px;
        font-weight: 700;
        text-transform: capitalize;
    }

    .history-header h2 {
        margin: 0;
        font-size: 20px;
    }

    .history-header p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
    }


    /* =========================================================
       ESTADOS
    ========================================================= */

    .status-badge {
        display: inline-flex;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 11px;
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


    /* =========================================================
       HORARIO
    ========================================================= */

    .schedule-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        margin-bottom: 12px;
        border-radius: 9px;
        background: #f8fafc;
    }

    .schedule-box span {
        color: #64748b;
        font-size: 12px;
    }

    .schedule-box strong {
        font-size: 14px;
    }


    /* =========================================================
       DETALLE
    ========================================================= */

    .history-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .history-detail {
        padding: 14px;
        border-radius: 9px;
        background: #f8fafc;
    }

    .history-detail span {
        display: block;
        margin-bottom: 4px;
        color: #64748b;
        font-size: 11px;
    }

    .history-detail strong {
        font-size: 14px;
    }


    /* =========================================================
       SALIDA ANTICIPADA
    ========================================================= */

    .early-box {
        margin-top: 13px;
        padding: 13px;
        border: 1px solid #fed7aa;
        border-radius: 9px;
        background: #fff7ed;
        color: #9a3412;
        font-size: 13px;
    }

    .early-box strong {
        display: block;
        margin-bottom: 4px;
    }

    .early-box p {
        margin: 0;
        line-height: 1.5;
    }


    /* =========================================================
       SALIDA FUERA DEL PUNTO
    ========================================================= */

    .warning-box {
        margin-top: 13px;
        padding: 13px;
        border: 1px solid #fecaca;
        border-radius: 9px;
        background: #fef2f2;
        color: #991b1b;
        font-size: 13px;
    }

    .warning-box strong {
        display: block;
        margin-bottom: 4px;
    }

    .warning-box p {
        margin: 0;
        line-height: 1.5;
    }


    /* =========================================================
       OBSERVACIONES
    ========================================================= */

    .observation-box {
        margin-top: 13px;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #f8fafc;
    }

    .observation-box span {
        display: block;
        margin-bottom: 5px;
        color: #334155;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .observation-box p {
        margin: 0;
        color: #475569;
        font-size: 13px;
        line-height: 1.5;
    }


    /* =========================================================
       SIN REGISTROS
    ========================================================= */

    .empty-card {
        padding: 45px 25px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
        text-align: center;
    }

    .empty-icon {
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

    .empty-card h2 {
        margin: 0 0 8px;
    }

    .empty-card p {
        margin: 0;
        color: #64748b;
    }


    /* =========================================================
       PAGINACIÓN
    ========================================================= */

    .pagination-wrapper {
        margin-top: 20px;
    }


    /* =========================================================
       MÓVIL
    ========================================================= */

    @media(max-width: 768px) {

        .historial-wrapper {
            max-width: none;
        }

        .page-header h1 {
            font-size: 25px;
        }

        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-group {
            width: 100%;
        }

        .filter-actions {
            width: 100%;
        }

        .btn-filter,
        .btn-clear {
            flex: 1;
        }

        .history-card {
            padding: 18px 14px;
        }

        .history-header {
            flex-direction: column;
            gap: 10px;
        }

        .history-grid {
            grid-template-columns: repeat(2, 1fr);
        }

    }


    @media(max-width: 480px) {

        .summary-card {
            grid-template-columns: 1fr;
        }

        .history-grid {
            grid-template-columns: 1fr;
        }

        .schedule-box {
            align-items: flex-start;
            flex-direction: column;
        }

    }

</style>

@endpush
