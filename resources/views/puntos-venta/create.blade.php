@extends('layouts.app')

@section('title', 'Nuevo punto de venta')

@section('content')

<div class="page-header">

    <div>
        <h1>Nuevo punto de venta</h1>
        <p>Registra la ubicación donde deberá presentarse el vendedor.</p>
    </div>

    <a href="{{ route('puntos-venta.index') }}" class="btn-back">
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
    action="{{ route('puntos-venta.store') }}"
    class="form-card"
    id="formPuntoVenta"
>

    @csrf


    {{-- =========================================================
         INFORMACIÓN GENERAL
    ========================================================= --}}

    <div class="section-header">

        <div class="section-icon">
            🏪
        </div>

        <div>
            <h2>Información general</h2>
            <p>Datos de identificación del punto de venta.</p>
        </div>

    </div>


    <div class="form-grid">

        <div class="form-group full">

            <label for="nombre">
                Nombre del punto de venta
            </label>

            <input
                type="text"
                id="nombre"
                name="nombre"
                value="{{ old('nombre') }}"
                placeholder="Ej. Centro Comercial Miraflores"
                maxlength="150"
                required
            >

        </div>


        <div class="form-group full">

            <label for="direccion">
                Dirección
            </label>

            <input
                type="text"
                id="direccion"
                name="direccion"
                value="{{ old('direccion') }}"
                placeholder="Ej. 21 avenida 4-32, zona 11"
                maxlength="255"
                required
            >

        </div>


        <div class="form-group">

            <label for="departamento">
                Departamento
            </label>

            <input
                type="text"
                id="departamento"
                name="departamento"
                value="{{ old('departamento') }}"
                placeholder="Ej. Guatemala"
                maxlength="100"
                required
            >

        </div>


        <div class="form-group">

            <label for="municipio">
                Municipio
            </label>

            <input
                type="text"
                id="municipio"
                name="municipio"
                value="{{ old('municipio') }}"
                placeholder="Ej. Guatemala"
                maxlength="100"
                required
            >

        </div>

    </div>



    {{-- =========================================================
         UBICACIÓN
    ========================================================= --}}

    <div class="section-divider"></div>


    <div class="section-header">

        <div class="section-icon location">
            📍
        </div>

        <div>
            <h2>Ubicación del punto</h2>
            <p>Define las coordenadas exactas donde debe presentarse el vendedor.</p>
        </div>

    </div>


    <div class="location-box">

        <div class="location-info">

            <div class="location-title">
                Capturar ubicación actual
            </div>

            <div class="location-description">
                Ubícate físicamente en el punto de venta y permite que el dispositivo obtenga tu ubicación.
            </div>

        </div>


        <button
            type="button"
            class="btn-location"
            id="btnObtenerUbicacion"
        >
            <span>◎</span>
            Obtener mi ubicación
        </button>

    </div>


    <div
        class="location-status"
        id="locationStatus"
    ></div>


    <div class="form-grid coordinates">

        <div class="form-group">

            <label for="latitud">
                Latitud
            </label>

            <input
                type="number"
                step="0.0000001"
                id="latitud"
                name="latitud"
                value="{{ old('latitud') }}"
                placeholder="14.6349150"
                required
            >

        </div>


        <div class="form-group">

            <label for="longitud">
                Longitud
            </label>

            <input
                type="number"
                step="0.0000001"
                id="longitud"
                name="longitud"
                value="{{ old('longitud') }}"
                placeholder="-90.5068820"
                required
            >

        </div>

    </div>


    <div class="coordinates-preview" id="coordinatesPreview">

        <div>
            <span class="coordinate-label">Latitud</span>
            <strong id="previewLatitud">
                {{ old('latitud') ?: 'Sin definir' }}
            </strong>
        </div>

        <div>
            <span class="coordinate-label">Longitud</span>
            <strong id="previewLongitud">
                {{ old('longitud') ?: 'Sin definir' }}
            </strong>
        </div>

    </div>



    {{-- =========================================================
         RADIO PERMITIDO
    ========================================================= --}}

    <div class="section-divider"></div>


    <div class="section-header">

        <div class="section-icon radius">
            ◎
        </div>

        <div>
            <h2>Radio permitido</h2>
            <p>Distancia máxima desde el punto para considerar válida la asistencia.</p>
        </div>

    </div>


    <div class="radius-container">

        <div class="form-group">

            <label for="radio_permitido_metros">
                Radio en metros
            </label>

            <div class="input-unit">

                <input
                    type="number"
                    id="radio_permitido_metros"
                    name="radio_permitido_metros"
                    value="{{ old('radio_permitido_metros', 100) }}"
                    min="10"
                    max="5000"
                    required
                >

                <span>
                    metros
                </span>

            </div>

            <small>
                Recomendado: entre 50 y 150 metros dependiendo del tamaño del lugar.
            </small>

        </div>


        <div class="radius-example">

            <div class="radius-circle">
                <div class="radius-center">
                    📍
                </div>
            </div>

            <div>
                El vendedor deberá estar dentro de este radio para registrar su llegada.
            </div>

        </div>

    </div>



    {{-- =========================================================
         ESTADO
    ========================================================= --}}

    <div class="section-divider"></div>


    <div class="status-row">

        <div>

            <h3>Estado del punto</h3>

            <p>
                Los puntos inactivos no podrán utilizarse para registrar asistencia.
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
         BOTONES
    ========================================================= --}}

    <div class="form-actions">

        <a
            href="{{ route('puntos-venta.index') }}"
            class="btn-cancel"
        >
            Cancelar
        </a>


        <button
            type="submit"
            class="btn-save"
        >
            Guardar punto de venta
        </button>

    </div>

</form>

@endsection



@push('styles')

<style>

    /* =========================================================
       ENCABEZADO
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


    /* =========================================================
       FORMULARIO
    ========================================================= */

    .form-card {

        max-width: 1000px;

        padding: 28px;

        background: #ffffff;

        border: 1px solid #e5e7eb;

        border-radius: 14px;

        box-shadow: 0 3px 14px rgba(15, 23, 42, .04);

    }


    .section-header {

        display: flex;

        align-items: center;

        gap: 13px;

        margin-bottom: 22px;

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


    .section-icon.location {

        background: #fee2e2;

    }


    .section-icon.radius {

        background: #dcfce7;

    }


    .section-divider {

        height: 1px;

        margin: 30px 0;

        background: #edf0f5;

    }


    /* =========================================================
       CAMPOS
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

        transition:
            border-color .18s ease,
            box-shadow .18s ease;

    }


    .form-group input:focus {

        border-color: #2563eb;

        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);

    }


    .form-group small {

        color: #64748b;

        font-size: 12px;

    }


    /* =========================================================
       UBICACIÓN GPS
    ========================================================= */

    .location-box {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;

        padding: 18px;

        margin-bottom: 15px;

        border: 1px solid #dbeafe;

        border-radius: 11px;

        background: #f8fbff;

    }


    .location-title {

        margin-bottom: 4px;

        font-weight: 700;

        color: #172033;

    }


    .location-description {

        max-width: 580px;

        color: #64748b;

        font-size: 13px;

        line-height: 1.5;

    }


    .btn-location {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 7px;

        padding: 11px 15px;

        border: 0;

        border-radius: 8px;

        background: #2563eb;

        color: #ffffff;

        font-weight: 700;

        cursor: pointer;

        white-space: nowrap;

    }


    .btn-location:hover {

        background: #1d4ed8;

    }


    .btn-location:disabled {

        opacity: .65;

        cursor: wait;

    }


    .location-status {

        display: none;

        padding: 11px 13px;

        margin-bottom: 17px;

        border-radius: 8px;

        font-size: 13px;

    }


    .location-status.success {

        display: block;

        background: #dcfce7;

        color: #166534;

    }


    .location-status.error {

        display: block;

        background: #fee2e2;

        color: #991b1b;

    }


    .location-status.loading {

        display: block;

        background: #e0ecff;

        color: #1e40af;

    }


    .coordinates {

        margin-top: 18px;

    }


    .coordinates-preview {

        display: grid;

        grid-template-columns: 1fr 1fr;

        gap: 10px;

        margin-top: 15px;

        padding: 14px;

        border-radius: 9px;

        background: #f8fafc;

    }


    .coordinates-preview > div {

        display: flex;

        flex-direction: column;

        gap: 3px;

    }


    .coordinate-label {

        color: #64748b;

        font-size: 11px;

        text-transform: uppercase;

        letter-spacing: .4px;

    }


    /* =========================================================
       RADIO
    ========================================================= */

    .radius-container {

        display: grid;

        grid-template-columns: 1fr 1fr;

        gap: 30px;

        align-items: center;

    }


    .input-unit {

        position: relative;

    }


    .input-unit input {

        padding-right: 80px;

    }


    .input-unit span {

        position: absolute;

        top: 50%;

        right: 12px;

        color: #64748b;

        font-size: 12px;

        transform: translateY(-50%);

        pointer-events: none;

    }


    .radius-example {

        display: flex;

        align-items: center;

        gap: 18px;

        color: #64748b;

        font-size: 13px;

        line-height: 1.5;

    }


    .radius-circle {

        width: 92px;

        height: 92px;

        display: flex;

        align-items: center;
        justify-content: center;

        flex-shrink: 0;

        border: 2px dashed #60a5fa;

        border-radius: 50%;

        background: rgba(96, 165, 250, .10);

    }


    .radius-center {

        width: 38px;

        height: 38px;

        display: flex;

        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: #ffffff;

        box-shadow: 0 2px 8px rgba(15, 23, 42, .12);

    }


    /* =========================================================
       ESTADO
    ========================================================= */

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

        left: 3px;

        top: 3px;

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


    /* =========================================================
       ERRORES
    ========================================================= */

    .alert-error {

        max-width: 1000px;

        padding: 15px 18px;

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


    /* =========================================================
       MÓVIL
    ========================================================= */

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


        .location-box {

            align-items: stretch;

            flex-direction: column;

        }


        .btn-location {

            width: 100%;

        }


        .coordinates-preview {

            grid-template-columns: 1fr;

        }


        .radius-container {

            grid-template-columns: 1fr;

        }


        .radius-example {

            padding: 15px;

            border-radius: 10px;

            background: #f8fafc;

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

    const btnUbicacion =
        document.getElementById('btnObtenerUbicacion');

    const inputLatitud =
        document.getElementById('latitud');

    const inputLongitud =
        document.getElementById('longitud');

    const locationStatus =
        document.getElementById('locationStatus');

    const previewLatitud =
        document.getElementById('previewLatitud');

    const previewLongitud =
        document.getElementById('previewLongitud');


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR VISTA PREVIA
    |--------------------------------------------------------------------------
    */

    function actualizarPreview() {

        previewLatitud.textContent =
            inputLatitud.value || 'Sin definir';

        previewLongitud.textContent =
            inputLongitud.value || 'Sin definir';

    }


    inputLatitud.addEventListener(
        'input',
        actualizarPreview
    );


    inputLongitud.addEventListener(
        'input',
        actualizarPreview
    );


    /*
    |--------------------------------------------------------------------------
    | OBTENER GPS
    |--------------------------------------------------------------------------
    */

    btnUbicacion.addEventListener(
        'click',
        function () {

            /*
             * Verificar soporte del navegador
             */

            if (!navigator.geolocation) {

                locationStatus.className =
                    'location-status error';

                locationStatus.textContent =
                    'Este dispositivo o navegador no permite obtener la ubicación.';

                return;

            }


            /*
             * Mostrar estado
             */

            btnUbicacion.disabled = true;

            locationStatus.className =
                'location-status loading';

            locationStatus.textContent =
                'Obteniendo ubicación GPS...';


            /*
             * Solicitar ubicación
             */

            navigator.geolocation.getCurrentPosition(

                /*
                 * ÉXITO
                 */

                function (position) {

                    const latitud =
                        position.coords.latitude.toFixed(7);

                    const longitud =
                        position.coords.longitude.toFixed(7);


                    inputLatitud.value =
                        latitud;

                    inputLongitud.value =
                        longitud;


                    actualizarPreview();


                    locationStatus.className =
                        'location-status success';


                    locationStatus.textContent =
                        'Ubicación obtenida correctamente. Precisión aproximada: ' +
                        Math.round(position.coords.accuracy) +
                        ' metros.';


                    btnUbicacion.disabled =
                        false;

                },


                /*
                 * ERROR
                 */

                function (error) {

                    btnUbicacion.disabled =
                        false;


                    locationStatus.className =
                        'location-status error';


                    switch (error.code) {

                        case error.PERMISSION_DENIED:

                            locationStatus.textContent =
                                'No se concedió permiso para acceder a la ubicación.';

                            break;


                        case error.POSITION_UNAVAILABLE:

                            locationStatus.textContent =
                                'No fue posible determinar la ubicación del dispositivo.';

                            break;


                        case error.TIMEOUT:

                            locationStatus.textContent =
                                'El dispositivo tardó demasiado en obtener la ubicación. Intenta nuevamente.';

                            break;


                        default:

                            locationStatus.textContent =
                                'Ocurrió un error al obtener la ubicación.';

                    }

                },


                /*
                 * CONFIGURACIÓN GPS
                 */

                {
                    enableHighAccuracy: true,

                    timeout: 15000,

                    maximumAge: 0
                }

            );

        }
    );

</script>

@endpush
