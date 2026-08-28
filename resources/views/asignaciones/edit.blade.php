@extends('layouts.app')

@section('title', 'Editar asignación')

@section('content')

<div class="page-header">

    <div>
        <h1>Editar asignación</h1>
        <p>Actualiza la jornada, horario, punto y días de trabajo.</p>
    </div>

    <a href="{{ route('asignaciones.index') }}" class="btn-back">
        ← Regresar
    </a>

</div>


@if ($errors->any())

    <div class="alert-error">

        <strong>Revisa la información ingresada.</strong>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>

    </div>

@endif


<form
    method="POST"
    action="{{ route('asignaciones.update', $asignacion->id_asignacion) }}"
    class="form-card"
    id="formAsignacion"
>

    @csrf
    @method('PUT')

    @php
        $rolActual = strtoupper(
            $asignacion->vendedor?->usuario?->rol?->nombre ?? ''
        );
    @endphp


    <div class="section-header">

        <div class="section-number">
            1
        </div>

        <div>
            <h2>Colaborador</h2>
            <p>Selecciona la persona a quien corresponde esta jornada.</p>
        </div>

    </div>


    <div class="form-group">

        <label for="id_vendedor">
            Colaborador
        </label>

        <select
            name="id_vendedor"
            id="id_vendedor"
            required
        >

            <option value="">
                Selecciona un colaborador
            </option>

            @foreach($vendedores as $vendedor)

                @php
                    $usuario = $vendedor->usuario;
                    $rol = strtoupper($usuario?->rol?->nombre ?? '');
                @endphp

                <option
                    value="{{ $vendedor->id_vendedor }}"
                    data-rol="{{ $rol }}"
                    data-supervisor="{{ $vendedor->id_supervisor ?? '' }}"
                    {{ (int) old('id_vendedor', $asignacion->id_vendedor) === (int) $vendedor->id_vendedor ? 'selected' : '' }}
                >
                    {{ $usuario?->nombre }}
                    {{ $usuario?->apellido }}

                    @if($vendedor->codigo_empleado)
                        - {{ $vendedor->codigo_empleado }}
                    @endif

                    @if($rol)
                        ({{ ucfirst(strtolower($rol)) }})
                    @endif
                </option>

            @endforeach

        </select>

        <small id="colaboradorAyuda">
            Los supervisores móviles no requieren un punto fijo.
        </small>

    </div>


    <div class="divider"></div>


    <div class="section-header">

        <div class="section-number">
            2
        </div>

        <div>
            <h2>Ubicación y horario</h2>
            <p>Define el punto de trabajo y el horario aplicable.</p>
        </div>

    </div>


    <div class="form-grid">

        <div
            class="form-group"
            id="grupoPunto"
        >

            <label for="id_punto_venta">
                Punto de venta
            </label>

            <select
                name="id_punto_venta"
                id="id_punto_venta"
            >

                <option value="">
                    Selecciona un punto
                </option>

                @foreach($puntos as $punto)

                    <option
                        value="{{ $punto->id_punto_venta }}"
                        data-supervisor="{{ $punto->id_supervisor ?? '' }}"
                        {{ (int) old('id_punto_venta', $asignacion->id_punto_venta) === (int) $punto->id_punto_venta ? 'selected' : '' }}
                    >
                        {{ $punto->nombre }}
                    </option>

                @endforeach

            </select>

            <small>
                Para vendedores, el punto debe pertenecer a su supervisor.
            </small>

        </div>


        <div
            class="supervisor-box"
            id="supervisorBox"
        >

            <strong>Supervisor móvil</strong>

            <p>
                Esta jornada no utiliza un punto de venta fijo.
                El supervisor podrá iniciar y finalizar en sus puntos autorizados.
            </p>

        </div>


        <div class="form-group">

            <label for="id_horario">
                Horario
            </label>

            <select
                name="id_horario"
                id="id_horario"
                required
            >

                <option value="">
                    Selecciona un horario
                </option>

                @foreach($horarios as $horario)

                    <option
                        value="{{ $horario->id_horario }}"
                        {{ (int) old('id_horario', $asignacion->id_horario) === (int) $horario->id_horario ? 'selected' : '' }}
                    >
                        {{ $horario->nombre }}
                        -
                        {{ \Carbon\Carbon::parse($horario->hora_entrada)->format('H:i') }}
                        a
                        {{ \Carbon\Carbon::parse($horario->hora_salida)->format('H:i') }}

                        @if(!$horario->activo)
                            (actual - inactivo)
                        @endif
                    </option>

                @endforeach

            </select>

            @if($asignacion->horario && !$asignacion->horario->activo)
                <small class="warning-text">
                    El horario actual está inactivo. Puedes conservarlo, pero si lo cambias deberás elegir uno activo.
                </small>
            @else
                <small>
                    Solo los horarios activos pueden seleccionarse como nuevo horario.
                </small>
            @endif

        </div>

    </div>


    <div class="divider"></div>


    <div class="section-header">

        <div class="section-number">
            3
        </div>

        <div>
            <h2>Vigencia</h2>
            <p>Indica desde cuándo aplica la asignación y, si corresponde, cuándo finaliza.</p>
        </div>

    </div>


    <div class="form-grid">

        <div class="form-group">

            <label for="fecha_inicio">
                Fecha de inicio
            </label>

            <input
                type="date"
                name="fecha_inicio"
                id="fecha_inicio"
                value="{{ old('fecha_inicio', optional($asignacion->fecha_inicio)->format('Y-m-d') ?? $asignacion->fecha_inicio) }}"
                required
            >

        </div>


        <div class="form-group">

            <label for="fecha_fin">
                Fecha de finalización
            </label>

            <input
                type="date"
                name="fecha_fin"
                id="fecha_fin"
                value="{{ old('fecha_fin', optional($asignacion->fecha_fin)->format('Y-m-d') ?? $asignacion->fecha_fin) }}"
            >

            <small>
                Déjala vacía si la asignación no tiene una fecha de finalización definida.
            </small>

        </div>

    </div>


    <div class="divider"></div>


    <div class="section-header">

        <div class="section-number">
            4
        </div>

        <div>
            <h2>Días de trabajo</h2>
            <p>Selecciona al menos un día en el que esta jornada estará vigente.</p>
        </div>

    </div>


    @error('dias')
        <div class="field-error">
            {{ $message }}
        </div>
    @enderror


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

        @foreach($dias as $campo => $nombreDia)

            <label class="day-card">

                <input
                    type="checkbox"
                    name="{{ $campo }}"
                    value="1"
                    {{ old($campo, $asignacion->{$campo}) ? 'checked' : '' }}
                >

                <span>
                    {{ $nombreDia }}
                </span>

            </label>

        @endforeach

    </div>


    <div class="divider"></div>


    <div class="status-row">

        <div>
            <h3>Asignación activa</h3>

            <p>
                Las asignaciones inactivas no se utilizan para registrar nuevas jornadas.
            </p>
        </div>


        <label class="switch">

            <input
                type="hidden"
                name="activo"
                value="0"
            >

            <input
                type="checkbox"
                name="activo"
                value="1"
                {{ old('activo', $asignacion->activo) ? 'checked' : '' }}
            >

            <span class="slider"></span>

        </label>

    </div>


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
            Guardar cambios
        </button>

    </div>

</form>

@endsection


@push('styles')

<style>

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
        font-weight: 700;
    }

    .page-header p {
        margin: 6px 0 0;
        color: #64748b;
    }

    .btn-back {
        padding: 10px 15px;
        border: 1px solid #dbe1ea;
        border-radius: 8px;
        background: #ffffff;
        color: #334155;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .alert-error {
        max-width: 960px;
        padding: 14px 16px;
        margin-bottom: 18px;
        border: 1px solid #fecaca;
        border-radius: 9px;
        background: #fef2f2;
        color: #991b1b;
    }

    .alert-error ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .form-card {
        max-width: 960px;
        padding: 28px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 3px 14px rgba(15, 23, 42, .04);
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 13px;
        margin-bottom: 20px;
    }

    .section-number {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 10px;
        background: #e0ecff;
        color: #1d4ed8;
        font-weight: 800;
    }

    .section-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
    }

    .section-header p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .divider {
        height: 1px;
        margin: 28px 0;
        background: #edf0f5;
    }

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

    .form-group label {
        color: #334155;
        font-size: 13px;
        font-weight: 700;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        min-height: 43px;
        padding: 10px 12px;
        border: 1px solid #d5dbe5;
        border-radius: 8px;
        background: #ffffff;
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
        line-height: 1.4;
    }

    .warning-text {
        color: #b45309 !important;
        font-weight: 600;
    }

    .supervisor-box {
        display: none;
        padding: 16px;
        border: 1px solid #dbeafe;
        border-radius: 10px;
        background: #f8fbff;
    }

    .supervisor-box.show {
        display: block;
    }

    .supervisor-box strong {
        display: block;
        margin-bottom: 5px;
        color: #1e3a8a;
    }

    .supervisor-box p {
        margin: 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.5;
    }

    .days-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 8px;
    }

    .day-card {
        position: relative;
        cursor: pointer;
    }

    .day-card input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .day-card span {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 8px;
        border: 1px solid #dbe1ea;
        border-radius: 8px;
        background: #ffffff;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        transition: .15s ease;
    }

    .day-card input:checked + span {
        border-color: #93c5fd;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .field-error {
        padding: 10px 12px;
        margin-bottom: 12px;
        border: 1px solid #fecaca;
        border-radius: 8px;
        background: #fef2f2;
        color: #991b1b;
        font-size: 13px;
    }

    .status-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
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

    .switch {
        position: relative;
        width: 46px;
        height: 25px;
        flex-shrink: 0;
    }

    .switch input[type="checkbox"] {
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
        width: 19px;
        height: 19px;
        top: 3px;
        left: 3px;
        border-radius: 50%;
        background: #ffffff;
        transition: .2s;
    }

    .switch input[type="checkbox"]:checked + .slider {
        background: #2563eb;
    }

    .switch input[type="checkbox"]:checked + .slider::before {
        transform: translateX(21px);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 30px;
        padding-top: 22px;
        border-top: 1px solid #edf0f5;
    }

    .btn-cancel,
    .btn-save {
        padding: 11px 17px;
        border-radius: 8px;
        font-weight: 700;
        text-decoration: none;
    }

    .btn-cancel {
        border: 1px solid #d5dbe5;
        background: #ffffff;
        color: #475569;
    }

    .btn-save {
        border: 0;
        background: #2563eb;
        color: #ffffff;
        cursor: pointer;
    }

    .btn-save:hover {
        background: #1d4ed8;
    }

    @media (max-width: 768px) {

        .page-header {
            align-items: stretch;
            flex-direction: column;
        }

        .form-card {
            padding: 20px;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .days-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .form-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .btn-cancel,
        .btn-save {
            text-align: center;
        }
    }

</style>

@endpush


@push('scripts')

<script>

    const vendedorSelect =
        document.getElementById('id_vendedor');

    const puntoSelect =
        document.getElementById('id_punto_venta');

    const grupoPunto =
        document.getElementById('grupoPunto');

    const supervisorBox =
        document.getElementById('supervisorBox');


    function actualizarTipoColaborador() {

        const option =
            vendedorSelect.options[
                vendedorSelect.selectedIndex
            ];

        const rol =
            (option?.dataset?.rol || '')
                .toUpperCase();

        const esSupervisor =
            rol === 'SUPERVISOR';


        if (esSupervisor) {

            grupoPunto.style.display =
                'none';

            supervisorBox.classList.add(
                'show'
            );

            puntoSelect.value =
                '';

            puntoSelect.required =
                false;

        } else {

            grupoPunto.style.display =
                'flex';

            supervisorBox.classList.remove(
                'show'
            );

            puntoSelect.required =
                rol === 'VENDEDOR';
        }
    }


    vendedorSelect.addEventListener(
        'change',
        actualizarTipoColaborador
    );


    actualizarTipoColaborador();

</script>

@endpush
