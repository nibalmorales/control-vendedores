@extends('layouts.app')

@section('title', 'Nuevo horario')

@section('content')

<div class="page-header">

    <div>
        <h1>Nuevo horario</h1>
        <p>Define el horario de trabajo y la tolerancia permitida.</p>
    </div>

    <a href="{{ route('horarios.index') }}" class="btn-back">
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
    action="{{ route('horarios.store') }}"
    class="form-card"
>

    @csrf


    <div class="section-header">

        <div class="section-icon">
            🕒
        </div>

        <div>

            <h2>Información del horario</h2>

            <p>
                Este horario podrá reutilizarse en diferentes vendedores y puntos de venta.
            </p>

        </div>

    </div>


    <div class="form-grid">


        <div class="form-group full">

            <label for="nombre">
                Nombre del horario
            </label>

            <input
                type="text"
                id="nombre"
                name="nombre"
                value="{{ old('nombre') }}"
                placeholder="Ej. Horario 08:00 - 17:00"
                maxlength="100"
                required
            >

        </div>


        <div class="form-group">

            <label for="hora_entrada">
                Hora de entrada
            </label>

            <input
                type="time"
                id="hora_entrada"
                name="hora_entrada"
                value="{{ old('hora_entrada', '08:00') }}"
                required
            >

        </div>


        <div class="form-group">

            <label for="hora_salida">
                Hora de salida
            </label>

            <input
                type="time"
                id="hora_salida"
                name="hora_salida"
                value="{{ old('hora_salida', '17:00') }}"
                required
            >

        </div>


        <div class="form-group">

            <label for="tolerancia_minutos">
                Tolerancia de entrada
            </label>

            <div class="input-unit">

                <input
                    type="number"
                    id="tolerancia_minutos"
                    name="tolerancia_minutos"
                    value="{{ old('tolerancia_minutos', 10) }}"
                    min="0"
                    max="120"
                    required
                >

                <span>
                    minutos
                </span>

            </div>

            <small>
                Después de este tiempo la llegada será considerada tarde.
            </small>

        </div>


        <div class="example-box">

            <div class="example-title">
                Ejemplo
            </div>

            <div class="example-time" id="exampleHorario">
                08:00 – 17:00
            </div>

            <div class="example-description" id="exampleTolerancia">
                Llegada válida hasta las 08:10.
            </div>

        </div>

    </div>


    <div class="divider"></div>


    <div class="status-row">

        <div>

            <h3>Horario activo</h3>

            <p>
                Solo los horarios activos podrán utilizarse en nuevas asignaciones.
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


    <div class="form-actions">

        <a
            href="{{ route('horarios.index') }}"
            class="btn-cancel"
        >
            Cancelar
        </a>


        <button
            type="submit"
            class="btn-save"
        >
            Guardar horario
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


    .form-card {
        max-width: 900px;

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

        margin-bottom: 24px;
    }


    .section-icon {
        width: 42px;
        height: 42px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex-shrink: 0;

        border-radius: 11px;

        background: #e0ecff;

        font-size: 20px;
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


    .form-grid {
        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

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
        font-size: 13px;
        font-weight: 700;

        color: #334155;
    }


    .form-group input {
        width: 100%;

        padding: 11px 12px;

        border: 1px solid #d5dbe5;
        border-radius: 8px;

        background: #ffffff;

        color: #172033;

        font-size: 14px;

        outline: none;
    }


    .form-group input:focus {
        border-color: #2563eb;

        box-shadow:
            0 0 0 3px rgba(37, 99, 235, .10);
    }


    .form-group small {
        color: #64748b;

        font-size: 12px;
    }


    .input-unit {
        position: relative;
    }


    .input-unit input {
        padding-right: 85px;
    }


    .input-unit span {
        position: absolute;

        top: 50%;
        right: 12px;

        transform: translateY(-50%);

        color: #64748b;

        font-size: 12px;

        pointer-events: none;
    }


    .example-box {
        padding: 18px;

        border: 1px solid #dbeafe;
        border-radius: 10px;

        background: #f8fbff;
    }


    .example-title {
        margin-bottom: 7px;

        color: #64748b;

        font-size: 12px;
        font-weight: 700;

        text-transform: uppercase;
    }


    .example-time {
        margin-bottom: 5px;

        color: #172033;

        font-size: 22px;
        font-weight: 700;
    }


    .example-description {
        color: #64748b;

        font-size: 13px;
    }


    .divider {
        height: 1px;

        margin: 30px 0;

        background: #edf0f5;
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

        width: 19px;
        height: 19px;

        top: 3px;
        left: 3px;

        border-radius: 50%;

        background: #ffffff;

        transition: .2s;
    }


    .switch input:checked + .slider {
        background: #2563eb;
    }


    .switch input:checked + .slider::before {
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


    .btn-cancel {
        padding: 11px 17px;

        border: 1px solid #d5dbe5;
        border-radius: 8px;

        background: #ffffff;

        color: #475569;

        font-weight: 600;

        text-decoration: none;
    }


    .btn-save {
        padding: 11px 18px;

        border: 0;
        border-radius: 8px;

        background: #2563eb;

        color: #ffffff;

        font-weight: 700;

        cursor: pointer;
    }


    .btn-save:hover {
        background: #1d4ed8;
    }


    .alert-error {
        max-width: 900px;

        margin-bottom: 18px;
        padding: 15px 18px;

        border: 1px solid #fecaca;
        border-radius: 9px;

        background: #fef2f2;

        color: #991b1b;
    }


    .alert-error ul {
        margin: 8px 0 0;

        padding-left: 20px;
    }


    @media (max-width: 768px) {

        .page-header {
            align-items: stretch;

            flex-direction: column;
        }


        .btn-back {
            text-align: center;
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


        .form-actions {
            flex-direction: column-reverse;
        }


        .btn-cancel,
        .btn-save {
            width: 100%;

            text-align: center;
        }

    }

</style>

@endpush


@push('scripts')

<script>

    const horaEntrada =
        document.getElementById('hora_entrada');

    const horaSalida =
        document.getElementById('hora_salida');

    const tolerancia =
        document.getElementById('tolerancia_minutos');

    const exampleHorario =
        document.getElementById('exampleHorario');

    const exampleTolerancia =
        document.getElementById('exampleTolerancia');


    function actualizarEjemplo() {

        const entrada =
            horaEntrada.value || '--:--';

        const salida =
            horaSalida.value || '--:--';

        const minutos =
            parseInt(tolerancia.value || 0);


        exampleHorario.textContent =
            entrada + ' – ' + salida;


        if (!horaEntrada.value) {

            exampleTolerancia.textContent =
                'Define una hora de entrada.';

            return;

        }


        const partes =
            horaEntrada.value.split(':');


        const fecha =
            new Date();


        fecha.setHours(
            parseInt(partes[0]),
            parseInt(partes[1]) + minutos,
            0,
            0
        );


        const limite =
            fecha.toLocaleTimeString(
                'es-GT',
                {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                }
            );


        exampleTolerancia.textContent =
            minutos > 0
                ? 'Llegada válida hasta las ' + limite + '.'
                : 'No existe tolerancia de entrada.';

    }


    horaEntrada.addEventListener(
        'input',
        actualizarEjemplo
    );


    horaSalida.addEventListener(
        'input',
        actualizarEjemplo
    );


    tolerancia.addEventListener(
        'input',
        actualizarEjemplo
    );


    actualizarEjemplo();

</script>

@endpush
