@extends('layouts.app')

@section('title', 'Nueva asignación')

@section('content')

<div class="page-header">

    <div>
        <h1>Nueva asignación</h1>
        <p>Define el horario, vigencia y ubicación de trabajo del colaborador.</p>
    </div>

    <a
        href="{{ route('asignaciones.index') }}"
        class="btn-back"
    >
        ← Regresar
    </a>

</div>


@if($errors->any())

    <div class="alert-error">

        <strong>Revisa la información ingresada.</strong>

        <ul>

            @foreach($errors->all() as $error)

                <li>
                    {{ $error }}
                </li>

            @endforeach

        </ul>

    </div>

@endif


<form
    method="POST"
    action="{{ route('asignaciones.store') }}"
    class="form-card"
>

    @csrf


    {{-- =========================================================
         INFORMACIÓN DE ASIGNACIÓN
    ========================================================== --}}

    <div class="section-title">

        <span class="section-icon">
            📋
        </span>

        <div>

            <h2>
                Información de asignación
            </h2>

            <p>
                Selecciona quién trabajará y cuál será su horario.
            </p>

        </div>

    </div>


    <div class="form-grid">


        {{-- =====================================================
             COLABORADOR
        ====================================================== --}}

        <div class="form-group">

            <label for="id_vendedor">
                Colaborador
            </label>

            <select
                id="id_vendedor"
                name="id_vendedor"
                required
            >

                <option value="">
                    Selecciona un colaborador
                </option>


                @foreach($vendedores as $vendedor)

                    <option
                        value="{{ $vendedor->id_vendedor }}"
                        data-rol="{{ strtoupper($vendedor->usuario?->rol?->nombre ?? 'VENDEDOR') }}"
                        {{ old('id_vendedor') == $vendedor->id_vendedor ? 'selected' : '' }}
                    >

                        {{ $vendedor->usuario?->nombre }}
                        {{ $vendedor->usuario?->apellido }}

                        @if($vendedor->codigo_empleado)

                            - {{ $vendedor->codigo_empleado }}

                        @endif

                    </option>

                @endforeach

            </select>

        </div>


        {{-- =====================================================
             PUNTO DE VENTA
        ====================================================== --}}

        <div
            class="form-group"
            id="grupoPuntoVenta"
        >

            <label for="id_punto_venta">
                Punto de venta
            </label>

            <select
                id="id_punto_venta"
                name="id_punto_venta"
            >

                <option value="">
                    Selecciona un punto
                </option>


                @foreach($puntos as $punto)

                    <option
                        value="{{ $punto->id_punto_venta }}"
                        {{ old('id_punto_venta') == $punto->id_punto_venta ? 'selected' : '' }}
                    >

                        {{ $punto->nombre }}

                    </option>

                @endforeach

            </select>


            <small>
                Obligatorio para vendedores.
            </small>

        </div>


        {{-- =====================================================
             SUPERVISOR MÓVIL
        ====================================================== --}}

        <div
            class="form-group supervisor-info"
            id="supervisorInfo"
            style="display:none;"
        >

            <label>
                Modalidad de trabajo
            </label>


            <div class="supervisor-box">

                <strong>
                    Supervisor móvil
                </strong>

                <p>
                    No requiere un punto de venta fijo.
                    Podrá iniciar y finalizar su jornada desde cualquiera
                    de los puntos de venta bajo su supervisión.
                </p>

            </div>

        </div>


        {{-- =====================================================
             HORARIO
        ====================================================== --}}

        <div class="form-group full">

            <label for="id_horario">
                Horario
            </label>

            <select
                id="id_horario"
                name="id_horario"
                required
            >

                <option value="">
                    Selecciona un horario
                </option>


                @foreach($horarios as $horario)

                    <option
                        value="{{ $horario->id_horario }}"
                        {{ old('id_horario') == $horario->id_horario ? 'selected' : '' }}
                    >

                        {{ $horario->nombre }}

                        —

                        {{ \Carbon\Carbon::parse(
                            $horario->hora_entrada
                        )->format('H:i') }}

                        a

                        {{ \Carbon\Carbon::parse(
                            $horario->hora_salida
                        )->format('H:i') }}

                    </option>

                @endforeach

            </select>

        </div>

    </div>


    <div class="divider"></div>


    {{-- =========================================================
         VIGENCIA
    ========================================================== --}}

    <div class="section-title">

        <span class="section-icon">
            📅
        </span>

        <div>

            <h2>
                Vigencia
            </h2>

            <p>
                Define desde cuándo será válida la asignación.
            </p>

        </div>

    </div>


    <div class="form-grid">

        <div class="form-group">

            <label for="fecha_inicio">
                Fecha de inicio
            </label>

            <input
                type="date"
                id="fecha_inicio"
                name="fecha_inicio"
                value="{{ old('fecha_inicio') }}"
                required
            >

        </div>


        <div class="form-group">

            <label for="fecha_fin">
                Fecha de finalización
            </label>

            <input
                type="date"
                id="fecha_fin"
                name="fecha_fin"
                value="{{ old('fecha_fin') }}"
            >

            <small>
                Déjala vacía si la asignación no tiene fecha final.
            </small>

        </div>

    </div>


    <div class="divider"></div>


    {{-- =========================================================
         DÍAS DE TRABAJO
    ========================================================== --}}

    <div class="section-title">

        <span class="section-icon">
            🗓️
        </span>

        <div>

            <h2>
                Días de trabajo
            </h2>

            <p id="textoDiasTrabajo">
                Selecciona los días en que el colaborador debe presentarse.
            </p>

        </div>

    </div>


    <div class="days-grid">

        @php

            $dias = [
                'lunes' => 'Lunes',
                'martes' => 'Martes',
                'miercoles' => 'Miércoles',
                'jueves' => 'Jueves',
                'viernes' => 'Viernes',
                'sabado' => 'Sábado',
                'domingo' => 'Domingo',
            ];

        @endphp


        @foreach($dias as $campo => $nombre)

            <label class="day-option">

                <input
                    type="checkbox"
                    name="{{ $campo }}"
                    value="1"
                    {{ old($campo) ? 'checked' : '' }}
                >

                <span>
                    {{ $nombre }}
                </span>

            </label>

        @endforeach

    </div>


    <div class="quick-days">

        <button
            type="button"
            id="btnSemana"
        >
            Lunes a viernes
        </button>

        <button
            type="button"
            id="btnTodos"
        >
            Todos los días
        </button>

        <button
            type="button"
            id="btnLimpiar"
        >
            Limpiar
        </button>

    </div>


    <div class="divider"></div>


    {{-- =========================================================
         ESTADO
    ========================================================== --}}

    <div class="status-row">

        <div>

            <h3>
                Asignación activa
            </h3>

            <p>
                Solo las asignaciones activas serán utilizadas
                para controlar asistencia.
            </p>

        </div>


        <label class="switch">

            <input
                type="checkbox"
                name="activo"
                value="1"
                {{ old('activo', 1) ? 'checked' : '' }}
            >

            <span class="slider"></span>

        </label>

    </div>


    {{-- =========================================================
         ACCIONES
    ========================================================== --}}

    <div class="form-actions">

        <a
            href="{{ route('asignaciones.index') }}"
            class="btn-cancel"
        >
            Cancelar
        </a>


        <button
            type="submit"
            class="btn-save"
        >
            Guardar asignación
        </button>

    </div>

</form>

@endsection



@push('styles')

<style>

    /* =========================================================
       HEADER
    ========================================================= */

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 24px;
    }

    .page-header h1 {
        margin: 0;
        font-size: 28px;
    }

    .page-header p {
        margin: 6px 0 0;
        color: #64748b;
    }

    .btn-back {
        padding: 10px 15px;
        border: 1px solid #dbe1ea;
        border-radius: 8px;
        background: white;
        color: #334155;
        font-weight: 600;
        text-decoration: none;
    }


    /* =========================================================
       FORMULARIO
    ========================================================= */

    .form-card {
        max-width: 950px;
        padding: 28px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: white;
    }


    /* =========================================================
       TÍTULOS
    ========================================================= */

    .section-title {
        display: flex;
        align-items: center;
        gap: 13px;
        margin-bottom: 22px;
    }

    .section-title h2 {
        margin: 0;
        font-size: 18px;
    }

    .section-title p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .section-icon {
        width: 42px;
        height: 42px;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-shrink: 0;
        border-radius: 11px;
        background: #e0ecff;
        font-size: 20px;
    }


    /* =========================================================
       GRID
    ========================================================= */

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-group label {
        color: #334155;
        font-size: 13px;
        font-weight: 700;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 11px 12px;
        border: 1px solid #d5dbe5;
        border-radius: 8px;
        background: white;
        color: #172033;
        font-size: 14px;
        outline: none;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
    }

    .form-group small {
        color: #64748b;
        font-size: 12px;
    }


    /* =========================================================
       SUPERVISOR
    ========================================================= */

    .supervisor-info {
        display: flex;
        flex-direction: column;
    }

    .supervisor-box {
        min-height: 69px;
        padding: 12px 14px;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        background: #eff6ff;
    }

    .supervisor-box strong {
        display: block;
        margin-bottom: 5px;
        color: #1e40af;
        font-size: 14px;
    }

    .supervisor-box p {
        margin: 0;
        color: #475569;
        font-size: 12px;
        line-height: 1.5;
    }


    /* =========================================================
       DIVISOR
    ========================================================= */

    .divider {
        height: 1px;
        margin: 30px 0;
        background: #edf0f5;
    }


    /* =========================================================
       DÍAS
    ========================================================= */

    .days-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 9px;
    }

    .day-option {
        cursor: pointer;
    }

    .day-option input {
        position: absolute;
        opacity: 0;
    }

    .day-option span {
        min-height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
        border: 1px solid #d5dbe5;
        border-radius: 9px;
        background: white;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
        transition: .18s;
    }

    .day-option input:checked + span {
        border-color: #2563eb;
        background: #e0ecff;
        color: #1d4ed8;
    }


    /* =========================================================
       ATAJOS DÍAS
    ========================================================= */

    .quick-days {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .quick-days button {
        padding: 8px 12px;
        border: 1px solid #d5dbe5;
        border-radius: 7px;
        background: white;
        color: #475569;
        cursor: pointer;
    }

    .quick-days button:hover {
        background: #f8fafc;
    }


    /* =========================================================
       ESTADO
    ========================================================= */

    .status-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
    }

    .status-row h3 {
        margin: 0;
        font-size: 15px;
    }

    .status-row p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
    }


    /* =========================================================
       SWITCH
    ========================================================= */

    .switch {
        position: relative;
        width: 46px;
        height: 25px;
        flex-shrink: 0;
    }

    .switch input {
        width: 0;
        height: 0;
        opacity: 0;
    }

    .slider {
        position: absolute;
        inset: 0;
        border-radius: 30px;
        background: #cbd5e1;
        cursor: pointer;
        transition: .2s;
    }

    .slider::before {
        content: "";
        position: absolute;
        top: 3px;
        left: 3px;
        width: 19px;
        height: 19px;
        border-radius: 50%;
        background: white;
        transition: .2s;
    }

    .switch input:checked + .slider {
        background: #2563eb;
    }

    .switch input:checked + .slider::before {
        transform: translateX(21px);
    }


    /* =========================================================
       BOTONES
    ========================================================= */

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 30px;
        padding-top: 22px;
        border-top: 1px solid #edf0f5;
    }

    .btn-cancel {
        padding: 11px 17px;
        border: 1px solid #d5dbe5;
        border-radius: 8px;
        background: white;
        color: #475569;
        font-weight: 600;
        text-decoration: none;
    }

    .btn-save {
        padding: 11px 18px;
        border: 0;
        border-radius: 8px;
        background: #2563eb;
        color: white;
        font-weight: 700;
        cursor: pointer;
    }

    .btn-save:hover {
        background: #1d4ed8;
    }


    /* =========================================================
       ERRORES
    ========================================================= */

    .alert-error {
        max-width: 950px;
        margin-bottom: 18px;
        padding: 15px 18px;
        border: 1px solid #fecaca;
        border-radius: 9px;
        background: #fef2f2;
        color: #991b1b;
    }

    .alert-error ul {
        margin-bottom: 0;
    }


    /* =========================================================
       MOBILE
    ========================================================= */

    @media(max-width: 768px) {

        .page-header {
            flex-direction: column;
            align-items: stretch;
        }

        .form-card {
            padding: 18px 14px;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full {
            grid-column: auto;
        }

        .days-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .btn-save,
        .btn-cancel {
            width: 100%;
            text-align: center;
        }

    }

</style>

@endpush



@push('scripts')

<script>

    /*
    |--------------------------------------------------------------------------
    | DÍAS
    |--------------------------------------------------------------------------
    */

    const dias = [
        'lunes',
        'martes',
        'miercoles',
        'jueves',
        'viernes',
        'sabado',
        'domingo'
    ];


    /*
    |--------------------------------------------------------------------------
    | LUNES A VIERNES
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('btnSemana')
        .addEventListener(
            'click',
            function () {

                dias.forEach(
                    function (dia, index) {

                        const input =
                            document.querySelector(
                                '[name="' + dia + '"]'
                            );

                        if (input) {
                            input.checked =
                                index <= 4;
                        }

                    }
                );

            }
        );


    /*
    |--------------------------------------------------------------------------
    | TODOS LOS DÍAS
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('btnTodos')
        .addEventListener(
            'click',
            function () {

                dias.forEach(
                    function (dia) {

                        const input =
                            document.querySelector(
                                '[name="' + dia + '"]'
                            );

                        if (input) {
                            input.checked = true;
                        }

                    }
                );

            }
        );


    /*
    |--------------------------------------------------------------------------
    | LIMPIAR
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('btnLimpiar')
        .addEventListener(
            'click',
            function () {

                dias.forEach(
                    function (dia) {

                        const input =
                            document.querySelector(
                                '[name="' + dia + '"]'
                            );

                        if (input) {
                            input.checked = false;
                        }

                    }
                );

            }
        );


    /*
    |--------------------------------------------------------------------------
    | TIPO DE COLABORADOR
    |--------------------------------------------------------------------------
    */

    const colaboradorSelect =
        document.getElementById(
            'id_vendedor'
        );

    const grupoPuntoVenta =
        document.getElementById(
            'grupoPuntoVenta'
        );

    const puntoVentaSelect =
        document.getElementById(
            'id_punto_venta'
        );

    const supervisorInfo =
        document.getElementById(
            'supervisorInfo'
        );

    const textoDiasTrabajo =
        document.getElementById(
            'textoDiasTrabajo'
        );


    /*
    |--------------------------------------------------------------------------
    | MOSTRAR / OCULTAR PUNTO
    |--------------------------------------------------------------------------
    */

    function actualizarTipoAsignacion()
    {
        if (!colaboradorSelect) {
            return;
        }


        const opcion =
            colaboradorSelect.options[
                colaboradorSelect.selectedIndex
            ];


        const rol =
            opcion?.dataset?.rol
                ? opcion.dataset.rol.toUpperCase()
                : '';


        /*
        |--------------------------------------------------------------------------
        | SUPERVISOR
        |--------------------------------------------------------------------------
        */

        if (rol === 'SUPERVISOR') {

            grupoPuntoVenta.style.display =
                'none';

            supervisorInfo.style.display =
                'flex';


            puntoVentaSelect.required =
                false;

            puntoVentaSelect.value =
                '';


            if (textoDiasTrabajo) {

                textoDiasTrabajo.textContent =
                    'Selecciona los días en que el supervisor tendrá jornada de trabajo.';

            }


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | VENDEDOR
        |--------------------------------------------------------------------------
        */

        supervisorInfo.style.display =
            'none';

        grupoPuntoVenta.style.display =
            'flex';


        /*
         * Si todavía no se ha seleccionado
         * colaborador, no forzamos required.
         */

        puntoVentaSelect.required =
            rol === 'VENDEDOR';


        if (textoDiasTrabajo) {

            textoDiasTrabajo.textContent =
                'Selecciona los días en que el vendedor debe presentarse.';

        }
    }


    colaboradorSelect.addEventListener(
        'change',
        actualizarTipoAsignacion
    );


    /*
    |--------------------------------------------------------------------------
    | EJECUTAR AL CARGAR
    |--------------------------------------------------------------------------
    |
    | Importante cuando Laravel vuelve al formulario con old().
    |
    */

    actualizarTipoAsignacion();

</script>

@endpush
