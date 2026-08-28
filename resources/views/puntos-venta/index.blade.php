@extends('layouts.app')

@section('title', 'Puntos de venta')

@section('content')

<div class="page-header">

    <div>
        <h1>Puntos de venta</h1>
        <p>
            Administra las ubicaciones donde deben presentarse los vendedores.
        </p>
    </div>

    <a
        href="{{ route('puntos-venta.create') }}"
        class="btn-primary"
    >
        + Nuevo punto
    </a>

</div>


{{-- =========================================================
     ALERTA DE ÉXITO
========================================================= --}}

@if(session('success'))

    <div
        class="alert-success"
        id="alertSuccess"
    >
        {{ session('success') }}
    </div>

@endif


{{-- =========================================================
     FILTROS
========================================================= --}}

<form
    method="GET"
    action="{{ route('puntos-venta.index') }}"
    class="filter-box"
>

    <div class="filters-grid">


        {{-- BUSCADOR --}}

        <input
            type="text"
            name="q"
            value="{{ $q }}"
            placeholder="Buscar por punto, dirección, municipio o departamento..."
            autocomplete="off"
        >


        {{-- ESTADO --}}

        <select
            name="estado"
            class="filter-select"
        >

            <option value="">
                Todos los estados
            </option>

            <option
                value="1"
                @selected(
                    (string) $estado === '1'
                )
            >
                Activos
            </option>

            <option
                value="0"
                @selected(
                    (string) $estado === '0'
                )
            >
                Inactivos
            </option>

        </select>


        {{-- SUPERVISOR SOLO PARA ADMIN --}}

        @if((int) auth()->user()->id_rol === 1)

            <select
                name="id_supervisor"
                class="filter-select supervisor-select"
            >

                <option value="">
                    Todos los supervisores
                </option>

                @foreach($supervisores as $supervisor)

                    <option
                        value="{{ $supervisor->id_usuario }}"
                        @selected(
                            (string) $idSupervisor ===
                            (string) $supervisor->id_usuario
                        )
                    >
                        {{ $supervisor->nombre }}
                        {{ $supervisor->apellido }}
                    </option>

                @endforeach

            </select>

        @endif


        <button
            type="submit"
            class="btn-filter"
        >
            Filtrar
        </button>


        @if(
            request()->filled('q') ||
            request()->filled('estado') ||
            request()->filled('id_supervisor')
        )

            <a
                href="{{ route('puntos-venta.index') }}"
                class="btn-clear"
            >
                Limpiar
            </a>

        @endif

    </div>

</form>


{{-- =========================================================
     ESCRITORIO
========================================================= --}}

<div class="desktop-view">

    <div class="table-card">

        <div class="table-responsive">

            <table>

                <thead>

                    <tr>

                        <th>
                            Punto de venta
                        </th>

                        <th>
                            Ubicación
                        </th>

                        @if((int) auth()->user()->id_rol === 1)

                            <th>
                                Supervisor
                            </th>

                        @endif

                        <th>
                            Coordenadas
                        </th>

                        <th>
                            Radio
                        </th>

                        <th>
                            Estado
                        </th>

                        <th>
                            Acciones
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($puntos as $punto)

                        <tr
                            class="row-clickable"
                            onclick="window.location='{{ route(
                                'puntos-venta.edit',
                                $punto->id_punto_venta
                            ) }}'"
                        >

                            {{-- PUNTO --}}

                            <td>

                                <strong>
                                    {{ $punto->nombre }}
                                </strong>

                                <div class="secondary">
                                    {{ $punto->direccion }}
                                </div>

                            </td>


                            {{-- UBICACIÓN --}}

                            <td>

                                {{ $punto->municipio }},
                                {{ $punto->departamento }}

                            </td>


                            {{-- SUPERVISOR --}}

                            @if((int) auth()->user()->id_rol === 1)

                                <td>

                                    @if($punto->supervisor)

                                        {{ $punto->supervisor->nombre }}
                                        {{ $punto->supervisor->apellido }}

                                    @else

                                        <span class="without-supervisor">
                                            Sin supervisor
                                        </span>

                                    @endif

                                </td>

                            @endif


                            {{-- COORDENADAS --}}

                            <td>

                                <div class="coordinate">
                                    {{ $punto->latitud }}
                                </div>

                                <div class="coordinate">
                                    {{ $punto->longitud }}
                                </div>

                            </td>


                            {{-- RADIO --}}

                            <td>

                                {{ $punto->radio_permitido_metros }}
                                m

                            </td>


                            {{-- ESTADO --}}

                            <td>

                                @if($punto->activo)

                                    <span class="status active">
                                        Activo
                                    </span>

                                @else

                                    <span class="status inactive">
                                        Inactivo
                                    </span>

                                @endif

                            </td>


                            {{-- ACCIONES --}}

                            <td>

                                <div
                                    class="actions"
                                    onclick="event.stopPropagation();"
                                >

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'puntos-venta.estado',
                                            $punto->id_punto_venta
                                        ) }}"
                                        id="formEstado-{{ $punto->id_punto_venta }}"
                                    >

                                        @csrf
                                        @method('PATCH')


                                        <button
                                            type="button"
                                            class="btn-action {{ $punto->activo
                                                ? 'btn-disable'
                                                : 'btn-enable' }}"
                                            onclick="abrirModalEstado(
                                                {{ $punto->id_punto_venta }},
                                                '{{ $punto->activo
                                                    ? 'desactivar'
                                                    : 'activar' }}',
                                                @js($punto->nombre),
                                                false
                                            )"
                                        >

                                            {{ $punto->activo
                                                ? 'Desactivar'
                                                : 'Activar' }}

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="{{ (int) auth()->user()->id_rol === 1 ? 7 : 6 }}"
                                class="empty"
                            >
                                No hay puntos de venta registrados con los filtros seleccionados.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


{{-- =========================================================
     MÓVIL
========================================================= --}}

<div class="mobile-view">

    @forelse($puntos as $punto)

        <div
            class="point-card"
            onclick="window.location='{{ route(
                'puntos-venta.edit',
                $punto->id_punto_venta
            ) }}'"
        >

            <div class="point-card-header">

                <div>

                    <div class="point-name">
                        {{ $punto->nombre }}
                    </div>

                    <div class="point-address">
                        {{ $punto->direccion }}
                    </div>

                </div>


                @if($punto->activo)

                    <span class="status active">
                        Activo
                    </span>

                @else

                    <span class="status inactive">
                        Inactivo
                    </span>

                @endif

            </div>


            <div class="point-data">

                <strong>
                    Ubicación:
                </strong>

                {{ $punto->municipio }},
                {{ $punto->departamento }}

            </div>


            @if((int) auth()->user()->id_rol === 1)

                <div class="point-data">

                    <strong>
                        Supervisor:
                    </strong>

                    @if($punto->supervisor)

                        {{ $punto->supervisor->nombre }}
                        {{ $punto->supervisor->apellido }}

                    @else

                        Sin supervisor

                    @endif

                </div>

            @endif


            <div class="point-data">

                <strong>
                    Latitud:
                </strong>

                {{ $punto->latitud }}

            </div>


            <div class="point-data">

                <strong>
                    Longitud:
                </strong>

                {{ $punto->longitud }}

            </div>


            <div class="point-data">

                <strong>
                    Radio permitido:
                </strong>

                {{ $punto->radio_permitido_metros }}
                metros

            </div>


            <div
                class="mobile-actions"
                onclick="event.stopPropagation();"
            >

                <form
                    method="POST"
                    action="{{ route(
                        'puntos-venta.estado',
                        $punto->id_punto_venta
                    ) }}"
                    id="formEstadoMobile-{{ $punto->id_punto_venta }}"
                >

                    @csrf
                    @method('PATCH')


                    <button
                        type="button"
                        class="btn-action {{ $punto->activo
                            ? 'btn-disable'
                            : 'btn-enable' }}"
                        onclick="abrirModalEstado(
                            {{ $punto->id_punto_venta }},
                            '{{ $punto->activo
                                ? 'desactivar'
                                : 'activar' }}',
                            @js($punto->nombre),
                            true
                        )"
                    >

                        {{ $punto->activo
                            ? 'Desactivar punto'
                            : 'Activar punto' }}

                    </button>

                </form>

            </div>

        </div>

    @empty

        <div class="mobile-empty">
            No hay puntos de venta registrados con los filtros seleccionados.
        </div>

    @endforelse

</div>


{{-- =========================================================
     PAGINACIÓN
========================================================= --}}

@if($puntos->hasPages())

    <div class="pagination">
        {{ $puntos->links() }}
    </div>

@endif


{{-- =========================================================
     MODAL
========================================================= --}}

<div
    class="modal-overlay"
    id="modalEstado"
>

    <div class="modal-box">

        <div
            class="modal-icon"
            id="modalIcon"
        >
            !
        </div>


        <h3 id="modalTitle">
            Confirmar acción
        </h3>


        <p id="modalMessage"></p>


        <div class="modal-actions">

            <button
                type="button"
                class="modal-button modal-cancel"
                onclick="cerrarModalEstado()"
            >
                Cancelar
            </button>


            <button
                type="button"
                class="modal-button modal-confirm"
                id="btnConfirmarEstado"
            >
                Confirmar
            </button>

        </div>

    </div>

</div>

@endsection


@push('styles')

<style>

    /* =========================================================
       ENCABEZADO
    ========================================================= */

    .page-header {

        display: flex;
        align-items: center;
        justify-content: space-between;

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


    .btn-primary {

        padding: 11px 17px;

        background: #2563eb;
        color: #ffffff;

        border-radius: 8px;

        font-weight: 600;
        text-decoration: none;

        white-space: nowrap;

    }


    .btn-primary:hover {

        background: #1d4ed8;

    }


    /* =========================================================
       ALERTA
    ========================================================= */

    .alert-success {

        padding: 14px 16px;

        margin-bottom: 18px;

        border: 1px solid #a7f3d0;
        border-radius: 9px;

        background: #ecfdf5;
        color: #065f46;

        transition: opacity .5s ease;

    }


    /* =========================================================
       FILTROS
    ========================================================= */

    .filter-box {

        margin-bottom: 18px;

    }


    .filters-grid {

        display: flex;
        align-items: center;
        flex-wrap: wrap;

        gap: 9px;

    }


    .filters-grid input {

        width: 100%;
        max-width: 420px;

        padding: 11px 13px;

        border: 1px solid #d1d5db;
        border-radius: 8px;

        background: #ffffff;

        font-size: 14px;

        outline: none;

    }


    .filters-grid input:focus {

        border-color: #2563eb;

    }


    .filter-select {

        min-width: 170px;

        padding: 11px 13px;

        border: 1px solid #d1d5db;
        border-radius: 8px;

        background: #ffffff;
        color: #334155;

        font-size: 14px;

        outline: none;

    }


    .supervisor-select {

        min-width: 220px;

    }


    .filter-select:focus {

        border-color: #2563eb;

    }


    .btn-filter {

        padding: 11px 16px;

        border: 0;
        border-radius: 8px;

        background: #071f3d;
        color: #ffffff;

        cursor: pointer;

        font-family: inherit;

    }


    .btn-filter:hover {

        background: #0b2d55;

    }


    .btn-clear {

        padding: 11px 15px;

        border-radius: 8px;

        background: #f1f5f9;
        color: #475569;

        text-decoration: none;

    }


    .btn-clear:hover {

        background: #e2e8f0;

    }


    /* =========================================================
       TABLA
    ========================================================= */

    .desktop-view {

        display: block;

    }


    .table-card {

        overflow: hidden;

        background: #ffffff;

        border: 1px solid #e5e7eb;
        border-radius: 12px;

    }


    .table-responsive {

        overflow-x: auto;

    }


    table {

        width: 100%;

        border-collapse: collapse;

        min-width: 900px;

    }


    th {

        padding: 14px 16px;

        background: #f8fafc;

        color: #475569;

        font-size: 13px;
        font-weight: 700;

        text-align: left;

        border-bottom: 1px solid #e5e7eb;

        white-space: nowrap;

    }


    td {

        padding: 15px 16px;

        border-bottom: 1px solid #eef2f7;

        vertical-align: middle;

    }


    tbody tr:last-child td {

        border-bottom: 0;

    }


    .row-clickable {

        cursor: pointer;

        transition: background .15s ease;

    }


    .row-clickable:hover {

        background: #f8fafc;

    }


    .secondary {

        margin-top: 4px;

        color: #64748b;

        font-size: 13px;

        max-width: 310px;

    }


    .coordinate {

        font-size: 13px;

        color: #475569;

        white-space: nowrap;

    }


    .without-supervisor {

        color: #94a3b8;

    }


    /* =========================================================
       ESTADOS
    ========================================================= */

    .status {

        display: inline-flex;

        padding: 5px 9px;

        border-radius: 20px;

        font-size: 12px;
        font-weight: 700;

        white-space: nowrap;

    }


    .status.active {

        background: #dcfce7;
        color: #166534;

    }


    .status.inactive {

        background: #fee2e2;
        color: #991b1b;

    }


    /* =========================================================
       ACCIONES
    ========================================================= */

    .actions {

        display: flex;

        gap: 7px;

    }


    .actions form {

        margin: 0;

    }


    .btn-action {

        padding: 7px 11px;

        border: 0;
        border-radius: 7px;

        font-family: inherit;
        font-size: 12px;

        cursor: pointer;

        white-space: nowrap;

    }


    .btn-disable {

        background: #fef2f2;
        color: #b91c1c;

    }


    .btn-disable:hover {

        background: #fee2e2;

    }


    .btn-enable {

        background: #ecfdf5;
        color: #047857;

    }


    .btn-enable:hover {

        background: #d1fae5;

    }


    .empty {

        padding: 35px;

        color: #64748b;

        text-align: center;

    }


    /* =========================================================
       MÓVIL
    ========================================================= */

    .mobile-view {

        display: none;

    }


    .point-card {

        padding: 16px;

        margin-bottom: 12px;

        background: #ffffff;

        border: 1px solid #e5e7eb;
        border-radius: 12px;

        cursor: pointer;

    }


    .point-card-header {

        display: flex;
        align-items: flex-start;
        justify-content: space-between;

        gap: 12px;

        margin-bottom: 13px;

    }


    .point-name {

        font-size: 17px;
        font-weight: 700;

        color: #0f172a;

    }


    .point-address {

        margin-top: 4px;

        color: #64748b;

        font-size: 13px;
        line-height: 1.4;

    }


    .point-data {

        margin-top: 8px;

        color: #475569;

        font-size: 14px;

    }


    .point-data strong {

        color: #0f172a;

    }


    .mobile-actions {

        margin-top: 15px;
        padding-top: 14px;

        border-top: 1px solid #e5e7eb;

    }


    .mobile-actions form {

        margin: 0;

    }


    .mobile-actions .btn-action {

        width: 100%;

        padding: 10px;

    }


    .mobile-empty {

        padding: 30px 20px;

        background: #ffffff;

        border: 1px solid #e5e7eb;
        border-radius: 12px;

        color: #64748b;

        text-align: center;

    }


    /* =========================================================
       PAGINACIÓN
    ========================================================= */

    .pagination {

        margin-top: 20px;

    }


    /* =========================================================
       MODAL
    ========================================================= */

    .modal-overlay {

        position: fixed;

        inset: 0;

        display: none;
        align-items: center;
        justify-content: center;

        padding: 20px;

        background: rgba(15, 23, 42, .50);

        z-index: 9999;

    }


    .modal-overlay.active {

        display: flex;

    }


    .modal-box {

        width: 100%;
        max-width: 430px;

        padding: 27px;

        background: #ffffff;

        border-radius: 15px;

        box-shadow: 0 20px 60px rgba(0, 0, 0, .20);

        text-align: center;

        animation: modalIn .18s ease;

    }


    .modal-icon {

        width: 56px;
        height: 56px;

        display: flex;
        align-items: center;
        justify-content: center;

        margin: 0 auto 16px;

        border-radius: 50%;

        font-size: 24px;
        font-weight: 700;

    }


    .modal-box h3 {

        margin: 0 0 10px;

        color: #0f172a;

        font-size: 21px;

    }


    .modal-box p {

        margin: 0;

        color: #64748b;

        line-height: 1.5;

    }


    .modal-actions {

        display: flex;
        justify-content: flex-end;

        gap: 10px;

        margin-top: 24px;

    }


    .modal-button {

        padding: 10px 17px;

        border: 0;
        border-radius: 8px;

        cursor: pointer;

        font-family: inherit;

    }


    .modal-cancel {

        background: #f1f5f9;
        color: #475569;

    }


    .modal-confirm {

        color: #ffffff;

    }


    @keyframes modalIn {

        from {

            opacity: 0;

            transform:
                translateY(8px)
                scale(.98);

        }

        to {

            opacity: 1;

            transform:
                translateY(0)
                scale(1);

        }

    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 768px) {

        .page-header {

            align-items: stretch;
            flex-direction: column;

        }


        .btn-primary {

            text-align: center;

        }


        .filters-grid {

            display: grid;

            grid-template-columns:
                1fr
                1fr;

        }


        .filters-grid input {

            grid-column: 1 / -1;

            max-width: none;

            box-sizing: border-box;

        }


        .filter-select {

            grid-column: 1 / -1;

            width: 100%;

            box-sizing: border-box;

        }


        .btn-filter,
        .btn-clear {

            width: 100%;

            box-sizing: border-box;

            text-align: center;

        }


        .desktop-view {

            display: none;

        }


        .mobile-view {

            display: block;

        }


        .modal-actions {

            flex-direction: column-reverse;

        }


        .modal-button {

            width: 100%;

        }

    }

</style>

@endpush


@push('scripts')

<script>

    let formularioEstado = null;


    /*
    |--------------------------------------------------------------------------
    | ABRIR MODAL
    |--------------------------------------------------------------------------
    */

    function abrirModalEstado(
        id,
        accion,
        nombre,
        mobile
    ) {

        if (mobile) {

            formularioEstado =
                document.getElementById(
                    'formEstadoMobile-' + id
                );

        } else {

            formularioEstado =
                document.getElementById(
                    'formEstado-' + id
                );

        }


        const modal =
            document.getElementById(
                'modalEstado'
            );

        const titulo =
            document.getElementById(
                'modalTitle'
            );

        const mensaje =
            document.getElementById(
                'modalMessage'
            );

        const boton =
            document.getElementById(
                'btnConfirmarEstado'
            );

        const icono =
            document.getElementById(
                'modalIcon'
            );


        /*
        |--------------------------------------------------------------------------
        | DESACTIVAR
        |--------------------------------------------------------------------------
        */

        if (accion === 'desactivar') {

            titulo.textContent =
                'Desactivar punto de venta';

            mensaje.textContent =
                '¿Deseas desactivar "' +
                nombre +
                '"? Este punto dejará de estar disponible para registrar asistencia.';

            boton.textContent =
                'Sí, desactivar';

            boton.style.background =
                '#dc2626';

            icono.style.background =
                '#fef2f2';

            icono.style.color =
                '#dc2626';

            icono.textContent =
                '!';

        }

        /*
        |--------------------------------------------------------------------------
        | ACTIVAR
        |--------------------------------------------------------------------------
        */

        else {

            titulo.textContent =
                'Activar punto de venta';

            mensaje.textContent =
                '¿Deseas activar nuevamente "' +
                nombre +
                '"? El punto volverá a estar disponible para las operaciones del sistema.';

            boton.textContent =
                'Sí, activar';

            boton.style.background =
                '#059669';

            icono.style.background =
                '#ecfdf5';

            icono.style.color =
                '#059669';

            icono.textContent =
                '✓';

        }


        modal.classList.add(
            'active'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CERRAR MODAL
    |--------------------------------------------------------------------------
    */

    function cerrarModalEstado() {

        document
            .getElementById(
                'modalEstado'
            )
            .classList
            .remove(
                'active'
            );


        formularioEstado = null;
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIRMAR
    |--------------------------------------------------------------------------
    */

    document
        .getElementById(
            'btnConfirmarEstado'
        )
        .addEventListener(
            'click',
            function () {

                if (formularioEstado) {

                    formularioEstado.submit();

                }

            }
        );


    /*
    |--------------------------------------------------------------------------
    | CLICK FUERA DEL MODAL
    |--------------------------------------------------------------------------
    */

    document
        .getElementById(
            'modalEstado'
        )
        .addEventListener(
            'click',
            function (event) {

                if (event.target === this) {

                    cerrarModalEstado();

                }

            }
        );


    /*
    |--------------------------------------------------------------------------
    | ESC
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (event.key === 'Escape') {

                cerrarModalEstado();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | ALERTA AUTOMÁTICA
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const alerta =
                document.getElementById(
                    'alertSuccess'
                );


            if (alerta) {

                setTimeout(
                    function () {

                        alerta.style.opacity =
                            '0';


                        setTimeout(
                            function () {

                                alerta.remove();

                            },
                            500
                        );

                    },
                    3000
                );

            }

        }
    );

</script>

@endpush
