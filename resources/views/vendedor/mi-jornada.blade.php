@extends('layouts.app')

@section('title', 'Mi jornada')

@section('content')

<div class="jornada-wrapper">

    <div class="page-header">

        <div>
            <h1>Mi jornada</h1>

            <p>
                Consulta tu jornada de hoy y registra tu actividad.
            </p>
        </div>

    </div>


    {{-- =========================================================
         MENSAJES
    ========================================================== --}}

    @if(session('success'))

        <div
            class="alert-success"
            id="successMessage"
        >
            {{ session('success') }}
        </div>

    @endif


    @if($errors->any())

        <div class="alert-error">
            {{ $errors->first() }}
        </div>

    @endif


    {{-- =========================================================
         SIN ASIGNACIÓN
    ========================================================== --}}

    @if(!$asignacion)

        <div class="empty-card">

            <div class="empty-icon">
                📅
            </div>

            <h2>
                No tienes una jornada asignada para hoy
            </h2>

            <p>
                No encontramos una asignación activa correspondiente al día de hoy.
            </p>

        </div>

    @else


        {{-- =====================================================
             INFORMACIÓN DE LA JORNADA
        ====================================================== --}}

        <div class="assignment-card">

            <div class="assignment-header">

                <div>

                    @if($esSupervisor)

                        <div class="small-label">
                            Zona de supervisión
                        </div>

                        <h2>
                            {{ $puntosSupervisor->count() }}
                            puntos de venta
                        </h2>

                        <p>
                            Puedes iniciar y finalizar tu jornada desde cualquiera
                            de tus puntos autorizados.
                        </p>

                    @else

                        <div class="small-label">
                            Punto asignado
                        </div>

                        <h2>
                            {{ $asignacion->puntoVenta?->nombre ?? 'Sin punto' }}
                        </h2>

                        <p>
                            {{ $asignacion->puntoVenta?->direccion ?? '' }}
                        </p>

                    @endif

                </div>


                @if($asistencia)

                    <span class="
                        status-badge
                        {{ $asistencia->estado?->nombre === 'PRESENTE' ? 'present' : '' }}
                        {{ $asistencia->estado?->nombre === 'TARDE' ? 'late' : '' }}
                    ">

                        {{ str_replace(
                            '_',
                            ' ',
                            $asistencia->estado?->nombre ?? 'REGISTRADO'
                        ) }}

                    </span>

                @else

                    <span class="status-badge pending">
                        PENDIENTE
                    </span>

                @endif

            </div>


            <div class="info-grid">

                {{-- HORARIO --}}

                <div class="info-box">

                    <div class="info-icon">
                        🕒
                    </div>

                    <div>

                        <span>
                            Horario
                        </span>

                        <strong>

                            {{ \Carbon\Carbon::parse(
                                $asignacion->horario->hora_entrada
                            )->format('H:i') }}

                            –

                            {{ \Carbon\Carbon::parse(
                                $asignacion->horario->hora_salida
                            )->format('H:i') }}

                        </strong>

                    </div>

                </div>


                {{-- TOLERANCIA --}}

                <div class="info-box">

                    <div class="info-icon">
                        ⏱
                    </div>

                    <div>

                        <span>
                            Tolerancia
                        </span>

                        <strong>
                            {{ $asignacion->horario->tolerancia_minutos }}
                            minutos
                        </strong>

                    </div>

                </div>


                {{-- UBICACIÓN --}}

                <div class="info-box">

                    <div class="info-icon">
                        📍
                    </div>

                    <div>

                        @if($esSupervisor)

                            <span>
                                Puntos autorizados
                            </span>

                            <strong>
                                {{ $puntosSupervisor->count() }}
                            </strong>

                        @else

                            <span>
                                Radio permitido
                            </span>

                            <strong>
                                {{ $asignacion->puntoVenta?->radio_permitido_metros ?? 0 }}
                                metros
                            </strong>

                        @endif

                    </div>

                </div>

            </div>


            {{-- =================================================
                 PUNTOS DEL SUPERVISOR
            ================================================== --}}

            @if($esSupervisor)

                <div class="supervisor-points">

                    <strong>
                        Tus puntos de supervisión
                    </strong>

                    <div class="points-list">

                        @forelse($puntosSupervisor as $punto)

                            <span>
                                📍 {{ $punto->nombre }}
                            </span>

                        @empty

                            <span class="no-points">
                                No tienes puntos activos asignados.
                            </span>

                        @endforelse

                    </div>

                </div>

            @endif

        </div>


        {{-- =====================================================
             SIN ASISTENCIA
        ====================================================== --}}

        @if(!$asistencia)

            <div class="check-card">

                <div class="gps-circle">
                    📍
                </div>

                <h2>
                    Registrar llegada
                </h2>


                @if($esSupervisor)

                    <p>
                        Debes encontrarte dentro del radio permitido de cualquiera
                        de tus puntos de supervisión. FieldControl detectará
                        automáticamente en cuál te encuentras.
                    </p>

                @else

                    <p>
                        Debes encontrarte físicamente dentro del punto de venta asignado.
                        FieldControl verificará tu ubicación antes de registrar la asistencia.
                    </p>

                @endif


                <div
                    class="gps-status"
                    id="gpsLlegadaStatus"
                >
                    Ubicación pendiente
                </div>


                <form
                    method="POST"
                    action="{{ route('vendedor.llegada') }}"
                    id="formLlegada"
                >

                    @csrf


                    <input
                        type="hidden"
                        name="latitud"
                        id="latitudLlegada"
                    >

                    <input
                        type="hidden"
                        name="longitud"
                        id="longitudLlegada"
                    >

                    <input
                        type="hidden"
                        name="precision"
                        id="precisionLlegada"
                    >


                    <button
                        type="button"
                        class="btn-checkin"
                        id="btnRegistrarLlegada"
                    >
                        Registrar llegada
                    </button>

                </form>


                <div class="security-note">

                    La ubicación se utiliza únicamente para validar tu presencia
                    al momento del registro.

                </div>

            </div>


        @else


            {{-- =====================================================
                 ENTRADA REGISTRADA
            ====================================================== --}}

            <div class="registered-card">

                <div class="registered-icon">
                    ✓
                </div>

                <h2>
                    Entrada registrada
                </h2>


                <div class="registered-grid">

                    <div>

                        <span>
                            Hora registrada
                        </span>

                        <strong>
                            {{ $asistencia->hora_llegada->format('H:i:s') }}
                        </strong>

                    </div>


                    <div>

                        <span>
                            Punto de llegada
                        </span>

                        <strong>
                            {{ $asistencia->puntoLlegada?->nombre ?? 'No identificado' }}
                        </strong>

                    </div>


                    <div>

                        <span>
                            Distancia
                        </span>

                        <strong>

                            {{ number_format(
                                $asistencia->distancia_llegada_metros ?? 0,
                                0
                            ) }}
                            m

                        </strong>

                    </div>


                    <div>

                        <span>
                            Precisión GPS
                        </span>

                        <strong>

                            ±{{ number_format(
                                $asistencia->precision_llegada_metros ?? 0,
                                0
                            ) }}
                            m

                        </strong>

                    </div>

                </div>

            </div>



            {{-- =====================================================
                 VISITAS DEL SUPERVISOR
            ====================================================== --}}

            @if($esSupervisor)

                <div class="visits-card">

                    <div class="visits-header">

                        <div>

                            <div class="visits-title">
                                Visitas a puntos de venta
                            </div>

                            <p>
                                Registra los puntos que visitas durante tu jornada.
                            </p>

                        </div>


                        <div class="visits-counter">

                            {{ $visitasHoy->count() }}

                            <span>
                                visitas hoy
                            </span>

                        </div>

                    </div>


                    {{-- =================================================
                         JORNADA YA FINALIZADA
                    ================================================== --}}

                    @if($asistencia->hora_salida)

                        <div class="visits-closed">

                            La jornada ya fue finalizada.
                            No puedes registrar nuevas visitas.

                        </div>


                    @else


                        {{-- =================================================
                             VISITA ABIERTA
                        ================================================== --}}

                        @if($visitaAbierta)

                            <div class="active-visit">

                                <div class="active-visit-top">

                                    <div class="visit-live-badge">
                                        VISITA EN CURSO
                                    </div>


                                    <div class="visit-time-live">

                                        Desde

                                        {{ \Carbon\Carbon::parse(
                                            $visitaAbierta->hora_llegada
                                        )->format('H:i') }}

                                    </div>

                                </div>


                                <div class="active-visit-location">

                                    <div class="active-visit-icon">
                                        📍
                                    </div>


                                    <div>

                                        <span>
                                            Punto actual
                                        </span>

                                        <strong>
                                            {{ $visitaAbierta->puntoVenta?->nombre ?? 'Punto no identificado' }}
                                        </strong>

                                    </div>

                                </div>


                                @if($visitaAbierta->observaciones)

                                    <div class="visit-existing-note">

                                        <span>
                                            Observación de entrada
                                        </span>

                                        <p>
                                            {{ $visitaAbierta->observaciones }}
                                        </p>

                                    </div>

                                @endif


                                <form
                                    method="POST"
                                    action="{{ route('supervisor.visita.finalizar') }}"
                                    id="formFinalizarVisita"
                                >

                                    @csrf


                                    <input
                                        type="hidden"
                                        name="latitud"
                                        id="latitudFinalizarVisita"
                                    >

                                    <input
                                        type="hidden"
                                        name="longitud"
                                        id="longitudFinalizarVisita"
                                    >

                                    <input
                                        type="hidden"
                                        name="precision"
                                        id="precisionFinalizarVisita"
                                    >


                                    <div class="visit-observation">

                                        <label for="observacionesFinalizarVisita">
                                            Observación de salida
                                        </label>

                                        <textarea
                                            name="observaciones"
                                            id="observacionesFinalizarVisita"
                                            maxlength="1000"
                                            rows="2"
                                            placeholder="Opcional"
                                        ></textarea>

                                    </div>


                                    <div
                                        class="gps-status"
                                        id="gpsFinalizarVisitaStatus"
                                    >
                                        Ubicación pendiente
                                    </div>


                                    <button
                                        type="button"
                                        class="btn-finish-visit"
                                        id="btnFinalizarVisita"
                                    >
                                        Finalizar visita
                                    </button>

                                </form>

                            </div>


                        {{-- =================================================
                             NUEVA VISITA
                        ================================================== --}}

                        @else

                            <div class="new-visit">

                                <div class="new-visit-icon">
                                    📍
                                </div>


                                <h3>
                                    Registrar nueva visita
                                </h3>


                                <p>
                                    Cuando llegues a uno de tus puntos, registra la visita.
                                    FieldControl detectará automáticamente dónde te encuentras.
                                </p>


                                <form
                                    method="POST"
                                    action="{{ route('supervisor.visita.iniciar') }}"
                                    id="formIniciarVisita"
                                >

                                    @csrf


                                    <input
                                        type="hidden"
                                        name="latitud"
                                        id="latitudIniciarVisita"
                                    >

                                    <input
                                        type="hidden"
                                        name="longitud"
                                        id="longitudIniciarVisita"
                                    >

                                    <input
                                        type="hidden"
                                        name="precision"
                                        id="precisionIniciarVisita"
                                    >


                                    <div class="visit-observation">

                                        <label for="observacionesIniciarVisita">
                                            Observación
                                        </label>

                                        <textarea
                                            name="observaciones"
                                            id="observacionesIniciarVisita"
                                            maxlength="1000"
                                            rows="2"
                                            placeholder="Ejemplo: revisión de inventario, reunión con encargado..."
                                        ></textarea>

                                        <small>
                                            Opcional.
                                        </small>

                                    </div>


                                    <div
                                        class="gps-status"
                                        id="gpsIniciarVisitaStatus"
                                    >
                                        Ubicación pendiente
                                    </div>


                                    <button
                                        type="button"
                                        class="btn-start-visit"
                                        id="btnIniciarVisita"
                                    >
                                        Registrar visita
                                    </button>

                                </form>

                            </div>

                        @endif

                    @endif



                    {{-- =================================================
                         HISTORIAL DE VISITAS DEL DÍA
                    ================================================== --}}

                    @if($visitasHoy->isNotEmpty())

                        <div class="today-visits">

                            <div class="today-visits-title">

                                <span>
                                    Visitas de hoy
                                </span>

                                <strong>
                                    {{ $visitasHoy->count() }}
                                </strong>

                            </div>


                            <div class="visit-list">

                                @foreach($visitasHoy as $visita)

                                    @php

                                        $horaInicioVisita =
                                            \Carbon\Carbon::parse(
                                                $visita->hora_llegada
                                            );

                                        $horaFinVisita =
                                            $visita->hora_salida
                                                ? \Carbon\Carbon::parse(
                                                    $visita->hora_salida
                                                )
                                                : null;

                                        $duracionVisita = null;

                                        if($horaFinVisita) {

                                            $minutosVisita =
                                                $horaInicioVisita
                                                    ->diffInMinutes(
                                                        $horaFinVisita
                                                    );

                                            $horasVisita =
                                                intdiv(
                                                    $minutosVisita,
                                                    60
                                                );

                                            $minutosRestantes =
                                                $minutosVisita % 60;

                                            if($horasVisita > 0) {

                                                $duracionVisita =
                                                    $horasVisita .
                                                    ' h ' .
                                                    $minutosRestantes .
                                                    ' min';

                                            } else {

                                                $duracionVisita =
                                                    $minutosRestantes .
                                                    ' min';

                                            }

                                        }

                                    @endphp


                                    <div class="
                                        visit-item
                                        {{ !$visita->hora_salida ? 'current' : '' }}
                                    ">

                                        <div class="visit-item-marker">

                                            @if($visita->hora_salida)
                                                ✓
                                            @else
                                                ●
                                            @endif

                                        </div>


                                        <div class="visit-item-content">

                                            <div class="visit-item-header">

                                                <strong>
                                                    {{ $visita->puntoVenta?->nombre ?? 'Punto no identificado' }}
                                                </strong>


                                                @if($visita->hora_salida)

                                                    <span class="visit-finished-badge">
                                                        Finalizada
                                                    </span>

                                                @else

                                                    <span class="visit-active-badge">
                                                        En curso
                                                    </span>

                                                @endif

                                            </div>


                                            <div class="visit-item-data">

                                                <div>

                                                    <span>
                                                        Llegada
                                                    </span>

                                                    <strong>
                                                        {{ $horaInicioVisita->format('H:i') }}
                                                    </strong>

                                                </div>


                                                <div>

                                                    <span>
                                                        Salida
                                                    </span>

                                                    <strong>

                                                        {{ $horaFinVisita
                                                            ? $horaFinVisita->format('H:i')
                                                            : '—'
                                                        }}

                                                    </strong>

                                                </div>


                                                <div>

                                                    <span>
                                                        Duración
                                                    </span>

                                                    <strong>
                                                        {{ $duracionVisita ?? 'En curso' }}
                                                    </strong>

                                                </div>


                                                <div>

                                                    <span>
                                                        Distancia llegada
                                                    </span>

                                                    <strong>

                                                        {{ number_format(
                                                            $visita->distancia_llegada_metros ?? 0,
                                                            0
                                                        ) }}
                                                        m

                                                    </strong>

                                                </div>

                                            </div>


                                            @if($visita->observaciones)

                                                <div class="visit-item-note">

                                                    {{ $visita->observaciones }}

                                                </div>

                                            @endif

                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        </div>

                    @else

                        <div class="no-visits">

                            Todavía no has registrado visitas durante esta jornada.

                        </div>

                    @endif

                </div>

            @endif



            {{-- =====================================================
                 REGISTRAR SALIDA DE JORNADA
            ====================================================== --}}

            @if(!$asistencia->hora_salida)

                <div class="checkout-section">

                    <div class="checkout-icon">
                        🚪
                    </div>


                    <h3>
                        Finalizar jornada
                    </h3>


                    @if($esSupervisor)

                        @if($visitaAbierta)

                            <div class="visit-open-warning">

                                <strong>
                                    Tienes una visita activa
                                </strong>

                                <p>
                                    Finaliza primero la visita en
                                    {{ $visitaAbierta->puntoVenta?->nombre ?? 'el punto actual' }}
                                    antes de terminar tu jornada.
                                </p>

                            </div>

                        @else

                            <p>
                                Puedes finalizar tu jornada desde cualquiera de tus
                                puntos de supervisión. No tiene que ser el mismo
                                punto donde iniciaste.
                            </p>

                        @endif

                    @else

                        <p>
                            Registra tu salida. Si estás fuera del punto asignado,
                            deberás indicar el motivo.
                        </p>

                    @endif


                    <div
                        class="gps-status"
                        id="gpsSalidaStatus"
                    >
                        Ubicación pendiente
                    </div>


                    <form
                        method="POST"
                        action="{{ route('vendedor.salida') }}"
                        id="formSalida"
                    >

                        @csrf


                        <input
                            type="hidden"
                            name="latitud"
                            id="latitudSalida"
                        >

                        <input
                            type="hidden"
                            name="longitud"
                            id="longitudSalida"
                        >

                        <input
                            type="hidden"
                            name="precision"
                            id="precisionSalida"
                        >


                        <div class="motivo-salida">

                            <label for="motivo_salida">
                                Motivo u observación
                            </label>

                            <textarea
                                name="motivo_salida"
                                id="motivo_salida"
                                maxlength="500"
                                rows="3"
                                placeholder="Escribe una observación si es necesario."
                            >{{ old('motivo_salida') }}</textarea>


                            <small>

                                Opcional si estás dentro de un punto autorizado.
                                Obligatorio si finalizas fuera de todos los puntos permitidos.

                            </small>

                        </div>


                        <button
                            type="button"
                            class="btn-checkout"
                            id="btnRegistrarSalida"
                            {{ $esSupervisor && $visitaAbierta ? 'disabled' : '' }}
                        >
                            Registrar salida
                        </button>

                    </form>

                </div>


            {{-- =====================================================
                 JORNADA FINALIZADA
            ====================================================== --}}

            @else

                @php

                    $horaProgramadaSalida =
                        \Carbon\Carbon::parse(
                            $asistencia->fecha->format('Y-m-d')
                            . ' '
                            . $asignacion->horario->hora_salida
                        );

                    $esSalidaAnticipada =
                        $asistencia->hora_salida->lt(
                            $horaProgramadaSalida
                        );

                    $esSalidaFuera =
                        $asistencia->id_punto_salida === null;

                @endphp


                <div class="checkout-finished">

                    <div class="checkout-finished-header">

                        <div class="checkout-finished-icon">
                            ✓
                        </div>

                        <div>

                            <h3>
                                Jornada finalizada
                            </h3>

                            <p>
                                Tu salida fue registrada correctamente.
                            </p>

                        </div>

                    </div>


                    <div class="checkout-complete">

                        <div>

                            <span>
                                Hora de salida
                            </span>

                            <strong>
                                {{ $asistencia->hora_salida->format('H:i:s') }}
                            </strong>

                        </div>


                        <div>

                            <span>
                                Punto de salida
                            </span>

                            <strong>

                                {{ $asistencia->puntoSalida?->nombre
                                    ?? 'Fuera de punto autorizado'
                                }}

                            </strong>

                        </div>


                        <div>

                            <span>
                                Distancia
                            </span>

                            <strong>

                                {{ number_format(
                                    $asistencia->distancia_salida_metros ?? 0,
                                    0
                                ) }}
                                m

                            </strong>

                        </div>


                        <div>

                            <span>
                                Precisión GPS
                            </span>

                            <strong>

                                ±{{ number_format(
                                    $asistencia->precision_salida_metros ?? 0,
                                    0
                                ) }}
                                m

                            </strong>

                        </div>

                    </div>


                    @if($esSalidaAnticipada)

                        <div class="early-exit-box">

                            <strong>
                                Salida anticipada
                            </strong>

                            <div>
                                Horario programado:
                                {{ $horaProgramadaSalida->format('H:i') }}
                            </div>

                            <div>
                                Salida registrada:
                                {{ $asistencia->hora_salida->format('H:i') }}
                            </div>

                        </div>

                    @endif


                    @if($esSalidaFuera)

                        <div class="warning-box">

                            <strong>
                                Salida fuera de un punto autorizado
                            </strong>

                            <div>
                                Se registró la ubicación GPS y la justificación.
                            </div>

                        </div>

                    @endif


                    @if($asistencia->observaciones)

                        <div class="observation-box">

                            <strong>
                                Observaciones
                            </strong>

                            <p>
                                {{ $asistencia->observaciones }}
                            </p>

                        </div>

                    @endif

                </div>

            @endif

        @endif

    @endif

</div>

@endsection



@push('styles')

<style>

    /* =========================================================
       GENERAL
    ========================================================= */

    .jornada-wrapper {
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
       ALERTAS
    ========================================================= */

    .alert-success,
    .alert-error {
        padding: 13px 15px;
        margin-bottom: 18px;
        border-radius: 9px;
        font-size: 14px;
    }

    .alert-success {
        border: 1px solid #bbf7d0;
        background: #ecfdf5;
        color: #166534;
    }

    .alert-error {
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #991b1b;
    }


    /* =========================================================
       SIN JORNADA
    ========================================================= */

    .empty-card {
        padding: 45px 25px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
        text-align: center;
    }

    .empty-icon {
        margin-bottom: 15px;
        font-size: 40px;
    }

    .empty-card h2 {
        margin: 0 0 8px;
    }

    .empty-card p {
        margin: 0;
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

    .info-box span {
        display: block;
        margin-bottom: 3px;
        color: #64748b;
        font-size: 11px;
    }

    .info-box strong {
        font-size: 14px;
    }


    /* =========================================================
       PUNTOS SUPERVISOR
    ========================================================= */

    .supervisor-points {
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px solid #e5e7eb;
    }

    .supervisor-points > strong {
        display: block;
        margin-bottom: 10px;
        font-size: 13px;
    }

    .points-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .points-list span {
        padding: 7px 10px;
        border-radius: 20px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 600;
    }

    .points-list .no-points {
        background: #fef2f2;
        color: #991b1b;
    }


    /* =========================================================
       REGISTRAR LLEGADA
    ========================================================= */

    .check-card {
        padding: 35px 25px;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: #ffffff;
        text-align: center;
    }

    .gps-circle {
        width: 75px;
        height: 75px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        border-radius: 50%;
        background: #e0ecff;
        font-size: 35px;
    }

    .check-card h2 {
        margin: 0 0 8px;
    }

    .check-card > p {
        max-width: 600px;
        margin: 0 auto 20px;
        color: #64748b;
        line-height: 1.5;
    }


    /* =========================================================
       GPS
    ========================================================= */

    .gps-status {
        display: inline-flex;
        margin-bottom: 18px;
        padding: 7px 12px;
        border-radius: 20px;
        background: #f1f5f9;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
    }

    .gps-status.loading {
        background: #e0ecff;
        color: #1d4ed8;
    }

    .gps-status.success {
        background: #dcfce7;
        color: #166534;
    }

    .gps-status.error {
        background: #fee2e2;
        color: #991b1b;
    }


    /* =========================================================
       BOTONES PRINCIPALES
    ========================================================= */

    .btn-checkin,
    .btn-checkout,
    .btn-start-visit,
    .btn-finish-visit {
        width: 100%;
        max-width: 420px;
        min-height: 53px;
        border: 0;
        border-radius: 10px;
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
    }

    .btn-checkin {
        background: #2563eb;
    }

    .btn-checkout {
        background: #071f3d;
    }

    .btn-start-visit {
        background: #2563eb;
    }

    .btn-finish-visit {
        background: #0f766e;
    }

    .btn-checkin:disabled,
    .btn-checkout:disabled,
    .btn-start-visit:disabled,
    .btn-finish-visit:disabled {
        opacity: .55;
        cursor: not-allowed;
    }


    .security-note {
        max-width: 550px;
        margin: 17px auto 0;
        color: #94a3b8;
        font-size: 11px;
        line-height: 1.45;
    }


    /* =========================================================
       ENTRADA REGISTRADA
    ========================================================= */

    .registered-card {
        padding: 30px;
        margin-bottom: 18px;
        border: 1px solid #bbf7d0;
        border-radius: 14px;
        background: #ffffff;
        text-align: center;
    }

    .registered-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        border-radius: 50%;
        background: #dcfce7;
        color: #166534;
        font-size: 28px;
        font-weight: 700;
    }

    .registered-card > h2 {
        margin: 0 0 22px;
    }

    .registered-grid,
    .checkout-complete {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    .registered-grid > div,
    .checkout-complete > div {
        padding: 15px;
        border-radius: 10px;
        background: #f8fafc;
    }

    .registered-grid span,
    .checkout-complete span {
        display: block;
        margin-bottom: 4px;
        color: #64748b;
        font-size: 11px;
    }

    .registered-grid strong,
    .checkout-complete strong {
        font-size: 14px;
    }


    /* =========================================================
       VISITAS
    ========================================================= */

    .visits-card {
        padding: 24px;
        margin-bottom: 18px;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: #ffffff;
    }

    .visits-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 20px;
    }

    .visits-title {
        color: #172033;
        font-size: 20px;
        font-weight: 700;
    }

    .visits-header p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .visits-counter {
        padding: 8px 12px;
        border-radius: 9px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 18px;
        font-weight: 700;
        text-align: center;
    }

    .visits-counter span {
        display: block;
        margin-top: 2px;
        font-size: 9px;
        font-weight: 600;
        text-transform: uppercase;
    }


    /* =========================================================
       NUEVA VISITA
    ========================================================= */

    .new-visit {
        padding: 22px;
        border: 1px dashed #bfdbfe;
        border-radius: 12px;
        background: #f8fbff;
        text-align: center;
    }

    .new-visit-icon {
        margin-bottom: 7px;
        font-size: 30px;
    }

    .new-visit h3 {
        margin: 0 0 7px;
        font-size: 17px;
    }

    .new-visit > p {
        max-width: 600px;
        margin: 0 auto 17px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
    }


    /* =========================================================
       VISITA ACTIVA
    ========================================================= */

    .active-visit {
        padding: 20px;
        border: 1px solid #99f6e4;
        border-radius: 12px;
        background: #f0fdfa;
    }

    .active-visit-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 17px;
    }

    .visit-live-badge {
        padding: 5px 9px;
        border-radius: 20px;
        background: #ccfbf1;
        color: #0f766e;
        font-size: 10px;
        font-weight: 800;
    }

    .visit-time-live {
        color: #475569;
        font-size: 12px;
    }

    .active-visit-location {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 18px;
    }

    .active-visit-icon {
        font-size: 28px;
    }

    .active-visit-location span {
        display: block;
        color: #64748b;
        font-size: 11px;
    }

    .active-visit-location strong {
        display: block;
        margin-top: 3px;
        color: #134e4a;
        font-size: 18px;
    }


    /* =========================================================
       OBSERVACIÓN VISITAS
    ========================================================= */

    .visit-observation {
        max-width: 520px;
        margin: 0 auto 14px;
        text-align: left;
    }

    .visit-observation label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
    }

    .visit-observation textarea {
        width: 100%;
        padding: 10px 11px;
        border: 1px solid #d5dbe5;
        border-radius: 8px;
        font-family: inherit;
        font-size: 13px;
        resize: vertical;
    }

    .visit-observation small {
        display: block;
        margin-top: 4px;
        color: #64748b;
        font-size: 10px;
    }

    .visit-existing-note {
        max-width: 520px;
        margin: 0 auto 16px;
        padding: 11px 12px;
        border-radius: 8px;
        background: #ffffff;
        text-align: left;
    }

    .visit-existing-note span {
        display: block;
        margin-bottom: 4px;
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .visit-existing-note p {
        margin: 0;
        color: #334155;
        font-size: 12px;
    }


    /* =========================================================
       VISITAS DE HOY
    ========================================================= */

    .today-visits {
        margin-top: 22px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .today-visits-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
    }

    .today-visits-title strong {
        min-width: 26px;
        padding: 4px 7px;
        border-radius: 20px;
        background: #f1f5f9;
        text-align: center;
    }

    .visit-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .visit-item {
        display: flex;
        gap: 12px;
        padding: 14px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #ffffff;
    }

    .visit-item.current {
        border-color: #99f6e4;
        background: #f0fdfa;
    }

    .visit-item-marker {
        width: 31px;
        height: 31px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 50%;
        background: #dcfce7;
        color: #166534;
        font-size: 12px;
        font-weight: 700;
    }

    .visit-item.current .visit-item-marker {
        background: #ccfbf1;
        color: #0f766e;
    }

    .visit-item-content {
        flex: 1;
        min-width: 0;
    }

    .visit-item-header {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .visit-item-header strong {
        color: #172033;
        font-size: 14px;
    }

    .visit-finished-badge,
    .visit-active-badge {
        padding: 4px 7px;
        border-radius: 20px;
        font-size: 9px;
        font-weight: 700;
    }

    .visit-finished-badge {
        background: #dcfce7;
        color: #166534;
    }

    .visit-active-badge {
        background: #ccfbf1;
        color: #0f766e;
    }

    .visit-item-data {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
    }

    .visit-item-data > div {
        padding: 8px;
        border-radius: 7px;
        background: #f8fafc;
    }

    .visit-item-data span {
        display: block;
        margin-bottom: 2px;
        color: #64748b;
        font-size: 9px;
    }

    .visit-item-data strong {
        font-size: 11px;
    }

    .visit-item-note {
        margin-top: 9px;
        padding: 8px;
        border-radius: 7px;
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
    }

    .no-visits,
    .visits-closed {
        margin-top: 18px;
        padding: 13px;
        border-radius: 9px;
        background: #f8fafc;
        color: #64748b;
        font-size: 12px;
        text-align: center;
    }


    /* =========================================================
       FINALIZAR JORNADA
    ========================================================= */

    .checkout-section {
        padding: 26px 24px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
        text-align: center;
    }

    .checkout-icon {
        width: 55px;
        height: 55px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        border-radius: 50%;
        background: #eef2ff;
        font-size: 25px;
    }

    .checkout-section h3 {
        margin: 0 0 6px;
        font-size: 20px;
    }

    .checkout-section > p {
        max-width: 620px;
        margin: 0 auto 18px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.5;
    }

    .visit-open-warning {
        max-width: 600px;
        margin: 0 auto 18px;
        padding: 13px;
        border: 1px solid #fed7aa;
        border-radius: 9px;
        background: #fff7ed;
        color: #9a3412;
        text-align: left;
    }

    .visit-open-warning strong {
        display: block;
        margin-bottom: 5px;
    }

    .visit-open-warning p {
        margin: 0;
        font-size: 12px;
    }


    /* =========================================================
       MOTIVO SALIDA
    ========================================================= */

    .motivo-salida {
        width: 100%;
        max-width: 520px;
        margin: 0 auto 18px;
        text-align: left;
    }

    .motivo-salida label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
    }

    .motivo-salida textarea {
        width: 100%;
        padding: 11px 12px;
        border: 1px solid #d5dbe5;
        border-radius: 8px;
        font-family: inherit;
        font-size: 14px;
        resize: vertical;
    }

    .motivo-salida small {
        display: block;
        margin-top: 6px;
        color: #64748b;
        font-size: 11px;
    }


    /* =========================================================
       JORNADA FINALIZADA
    ========================================================= */

    .checkout-finished {
        padding: 25px;
        border: 1px solid #bbf7d0;
        border-radius: 14px;
        background: #ffffff;
    }

    .checkout-finished-header {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 18px;
    }

    .checkout-finished-icon {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #dcfce7;
        color: #166534;
        font-weight: 700;
    }

    .checkout-finished-header h3 {
        margin: 0;
        text-align: left;
    }

    .checkout-finished-header p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 12px;
        text-align: left;
    }


    /* =========================================================
       ALERTAS FINALES
    ========================================================= */

    .early-exit-box,
    .warning-box,
    .observation-box {
        margin-top: 12px;
        padding: 13px;
        border-radius: 9px;
        font-size: 13px;
        text-align: left;
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

    .observation-box {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .early-exit-box strong,
    .warning-box strong,
    .observation-box strong {
        display: block;
        margin-bottom: 6px;
    }

    .observation-box p {
        margin: 0;
        color: #475569;
        line-height: 1.5;
    }


    /* =========================================================
       MOBILE
    ========================================================= */

    @media(max-width: 768px) {

        .jornada-wrapper {
            max-width: none;
        }

        .page-header h1 {
            font-size: 25px;
        }

        .assignment-card,
        .registered-card,
        .visits-card,
        .checkout-section,
        .checkout-finished {
            padding: 18px 14px;
        }

        .assignment-header,
        .visits-header {
            flex-direction: column;
        }

        .info-grid,
        .registered-grid,
        .checkout-complete {
            grid-template-columns: 1fr;
        }

        .visit-item-data {
            grid-template-columns: repeat(2, 1fr);
        }

        .check-card {
            padding: 28px 16px;
        }

    }


    @media(max-width: 480px) {

        .visit-item-data {
            grid-template-columns: 1fr;
        }

        .active-visit-top,
        .visit-item-header {
            align-items: flex-start;
            flex-direction: column;
        }

    }

</style>

@endpush



@push('scripts')

<script>

/*
|--------------------------------------------------------------------------
| OBTENER GPS
|--------------------------------------------------------------------------
*/

function obtenerUbicacionGPS(configuracion)
{
    if (!navigator.geolocation) {

        configuracion.status.className =
            'gps-status error';

        configuracion.status.textContent =
            'Este dispositivo no permite obtener la ubicación.';

        return;
    }


    configuracion.boton.disabled =
        true;


    configuracion.status.className =
        'gps-status loading';


    configuracion.status.textContent =
        'Verificando ubicación GPS...';


    navigator.geolocation.getCurrentPosition(

        function (position) {

            const accuracy =
                position.coords.accuracy;


            /*
            |--------------------------------------------------------------------------
            | PRECISIÓN MÁXIMA ACEPTADA
            |--------------------------------------------------------------------------
            */

            if (accuracy > 300) {

                configuracion.boton.disabled =
                    false;


                configuracion.status.className =
                    'gps-status error';


                configuracion.status.textContent =
                    'La ubicación tiene poca precisión (' +
                    Math.round(accuracy) +
                    ' m). Intenta nuevamente.';

                return;
            }


            configuracion.latitud.value =
                position.coords.latitude;


            configuracion.longitud.value =
                position.coords.longitude;


            configuracion.precision.value =
                accuracy;


            configuracion.status.className =
                'gps-status success';


            configuracion.status.textContent =
                'Ubicación obtenida. Precisión ±' +
                Math.round(accuracy) +
                ' m. Procesando...';


            setTimeout(
                function () {

                    configuracion.form.submit();

                },
                350
            );

        },


        function (error) {

            configuracion.boton.disabled =
                false;


            configuracion.status.className =
                'gps-status error';


            switch (error.code) {

                case error.PERMISSION_DENIED:

                    configuracion.status.textContent =
                        'Debes permitir el acceso a tu ubicación para continuar.';

                    break;


                case error.POSITION_UNAVAILABLE:

                    configuracion.status.textContent =
                        'No se pudo determinar tu ubicación actual.';

                    break;


                case error.TIMEOUT:

                    configuracion.status.textContent =
                        'El GPS tardó demasiado. Intenta nuevamente.';

                    break;


                default:

                    configuracion.status.textContent =
                        'Ocurrió un error al obtener la ubicación.';

            }

        },


        {
            enableHighAccuracy: true,
            timeout: 30000,
            maximumAge: 10000
        }

    );
}


/*
|--------------------------------------------------------------------------
| REGISTRAR LLEGADA
|--------------------------------------------------------------------------
*/

const botonLlegada =
    document.getElementById(
        'btnRegistrarLlegada'
    );


if (botonLlegada) {

    botonLlegada.addEventListener(
        'click',
        function () {

            obtenerUbicacionGPS({

                boton:
                    botonLlegada,

                form:
                    document.getElementById(
                        'formLlegada'
                    ),

                status:
                    document.getElementById(
                        'gpsLlegadaStatus'
                    ),

                latitud:
                    document.getElementById(
                        'latitudLlegada'
                    ),

                longitud:
                    document.getElementById(
                        'longitudLlegada'
                    ),

                precision:
                    document.getElementById(
                        'precisionLlegada'
                    )

            });

        }
    );
}


/*
|--------------------------------------------------------------------------
| INICIAR VISITA
|--------------------------------------------------------------------------
*/

const botonIniciarVisita =
    document.getElementById(
        'btnIniciarVisita'
    );


if (botonIniciarVisita) {

    botonIniciarVisita.addEventListener(
        'click',
        function () {

            obtenerUbicacionGPS({

                boton:
                    botonIniciarVisita,

                form:
                    document.getElementById(
                        'formIniciarVisita'
                    ),

                status:
                    document.getElementById(
                        'gpsIniciarVisitaStatus'
                    ),

                latitud:
                    document.getElementById(
                        'latitudIniciarVisita'
                    ),

                longitud:
                    document.getElementById(
                        'longitudIniciarVisita'
                    ),

                precision:
                    document.getElementById(
                        'precisionIniciarVisita'
                    )

            });

        }
    );
}


/*
|--------------------------------------------------------------------------
| FINALIZAR VISITA
|--------------------------------------------------------------------------
*/

const botonFinalizarVisita =
    document.getElementById(
        'btnFinalizarVisita'
    );


if (botonFinalizarVisita) {

    botonFinalizarVisita.addEventListener(
        'click',
        function () {

            obtenerUbicacionGPS({

                boton:
                    botonFinalizarVisita,

                form:
                    document.getElementById(
                        'formFinalizarVisita'
                    ),

                status:
                    document.getElementById(
                        'gpsFinalizarVisitaStatus'
                    ),

                latitud:
                    document.getElementById(
                        'latitudFinalizarVisita'
                    ),

                longitud:
                    document.getElementById(
                        'longitudFinalizarVisita'
                    ),

                precision:
                    document.getElementById(
                        'precisionFinalizarVisita'
                    )

            });

        }
    );
}


/*
|--------------------------------------------------------------------------
| FINALIZAR JORNADA
|--------------------------------------------------------------------------
*/

const botonSalida =
    document.getElementById(
        'btnRegistrarSalida'
    );


if (
    botonSalida &&
    !botonSalida.disabled
) {

    botonSalida.addEventListener(
        'click',
        function () {

            obtenerUbicacionGPS({

                boton:
                    botonSalida,

                form:
                    document.getElementById(
                        'formSalida'
                    ),

                status:
                    document.getElementById(
                        'gpsSalidaStatus'
                    ),

                latitud:
                    document.getElementById(
                        'latitudSalida'
                    ),

                longitud:
                    document.getElementById(
                        'longitudSalida'
                    ),

                precision:
                    document.getElementById(
                        'precisionSalida'
                    )

            });

        }
    );
}


/*
|--------------------------------------------------------------------------
| OCULTAR MENSAJE SUCCESS
|--------------------------------------------------------------------------
*/

const successMessage =
    document.getElementById(
        'successMessage'
    );


if (successMessage) {

    setTimeout(
        function () {

            successMessage.style.transition =
                'opacity .4s ease';


            successMessage.style.opacity =
                '0';


            setTimeout(
                function () {

                    successMessage.remove();

                },
                400
            );

        },
        3000
    );

}

</script>

@endpush
