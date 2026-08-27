@extends('layouts.app')

@section('title', 'Vendedores')

@section('content')

<style>

    .vendedores-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 25px;
    }

    .vendedores-header h1 {
        margin: 0 0 6px;
        font-size: 32px;
    }

    .vendedores-subtitle {
        color: #6b7280;
    }

    .btn-nuevo {
        display: inline-block;
        background: #2563eb;
        color: #ffffff;
        text-decoration: none;
        padding: 11px 18px;
        border-radius: 8px;
        white-space: nowrap;
    }

    .btn-nuevo:hover {
        background: #1d4ed8;
    }


    /* =====================================================
       ALERTAS
    ===================================================== */

    .alert-success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        padding: 14px 16px;
        border-radius: 10px;
        margin-bottom: 20px;
        transition: opacity .5s ease;
    }

    .alert-invitacion {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        padding: 14px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .alert-invitacion a {
        overflow-wrap: anywhere;
    }


    /* =====================================================
       FILTROS
    ===================================================== */

    .buscador {
        margin-bottom: 22px;
    }

    .filtros-vendedores {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filtros-vendedores input {
        flex: 1;
        min-width: 260px;
        max-width: 560px;
        padding: 12px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 15px;
        background: #ffffff;
        box-sizing: border-box;
    }

    .filtros-vendedores input:focus {
        outline: none;
        border-color: #2563eb;
    }

    .filtro-select {
        min-width: 180px;
        padding: 12px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 15px;
        background: #ffffff;
        color: #374151;
    }

    .filtro-supervisor {
        min-width: 220px;
    }

    .filtro-select:focus {
        outline: none;
        border-color: #2563eb;
    }

    .btn-filtrar {
        border: 0;
        background: #2563eb;
        color: #ffffff;
        padding: 12px 17px;
        border-radius: 8px;
        cursor: pointer;
        font-family: inherit;
        font-size: 14px;
    }

    .btn-filtrar:hover {
        background: #1d4ed8;
    }

    .btn-limpiar {
        background: #f3f4f6;
        color: #374151;
        text-decoration: none;
        padding: 12px 17px;
        border-radius: 8px;
        font-size: 14px;
    }

    .btn-limpiar:hover {
        background: #e5e7eb;
    }


    /* =====================================================
       ESCRITORIO
    ===================================================== */

    .vendedores-desktop {
        display: block;
    }

    .tabla-wrapper {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow-x: auto;
    }

    .tabla-vendedores {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }

    .tabla-vendedores thead {
        background: #f9fafb;
    }

    .tabla-vendedores th {
        padding: 15px;
        text-align: left;
        font-size: 15px;
        white-space: nowrap;
    }

    .tabla-vendedores td {
        padding: 15px;
        border-top: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .fila-vendedor {
        cursor: pointer;
        transition: background .18s ease;
    }

    .fila-vendedor:hover {
        background: #f8fafc;
    }


    /* =====================================================
       ESTADO
    ===================================================== */

    .estado {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 13px;
        white-space: nowrap;
    }

    .estado-activo {
        background: #ecfdf5;
        color: #065f46;
    }

    .estado-inactivo {
        background: #f3f4f6;
        color: #6b7280;
    }


    /* =====================================================
       ACCIONES
    ===================================================== */

    .acciones {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .acciones form {
        margin: 0;
    }

    .btn-accion {
        border: 0;
        border-radius: 7px;
        padding: 7px 11px;
        font-size: 13px;
        cursor: pointer;
        font-family: inherit;
        white-space: nowrap;
    }

    .btn-desactivar {
        background: #fef2f2;
        color: #b91c1c;
    }

    .btn-desactivar:hover {
        background: #fee2e2;
    }

    .btn-activar {
        background: #ecfdf5;
        color: #047857;
    }

    .btn-activar:hover {
        background: #d1fae5;
    }


    /* =====================================================
       MÓVIL
    ===================================================== */

    .vendedores-mobile {
        display: none;
    }

    .vendedor-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 12px;
        cursor: pointer;
    }

    .vendedor-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
    }

    .vendedor-nombre {
        font-size: 17px;
        font-weight: 600;
        color: #111827;
    }

    .vendedor-codigo {
        color: #6b7280;
        font-size: 13px;
        margin-top: 3px;
    }

    .vendedor-dato {
        margin-top: 8px;
        font-size: 14px;
        color: #4b5563;
        overflow-wrap: anywhere;
    }

    .vendedor-dato strong {
        color: #111827;
        font-weight: 500;
    }

    .acciones-mobile {
        display: flex;
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid #e5e7eb;
    }

    .acciones-mobile form {
        width: 100%;
        margin: 0;
    }

    .acciones-mobile button {
        width: 100%;
    }

    .sin-registros {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 30px 20px;
        text-align: center;
        color: #6b7280;
    }

    .paginacion {
        margin-top: 20px;
    }


    /* =====================================================
       MODAL
    ===================================================== */

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .48);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }

    .modal-overlay.activo {
        display: flex;
    }

    .modal-box {
        width: 100%;
        max-width: 430px;
        background: #ffffff;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .20);
        text-align: center;
        animation: modalEntrada .18s ease;
    }

    .modal-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        margin: 0 auto 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 25px;
        font-weight: 700;
    }

    .modal-box h3 {
        margin: 0 0 10px;
        font-size: 21px;
        color: #111827;
    }

    .modal-box p {
        margin: 0;
        color: #6b7280;
        line-height: 1.5;
    }

    .modal-botones {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 24px;
    }

    .modal-btn {
        border: 0;
        border-radius: 8px;
        padding: 10px 17px;
        cursor: pointer;
        font-family: inherit;
        font-size: 14px;
    }

    .modal-cancelar {
        background: #f3f4f6;
        color: #374151;
    }

    .modal-confirmar {
        color: #ffffff;
    }

    @keyframes modalEntrada {

        from {
            opacity: 0;
            transform: translateY(8px) scale(.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }


    /* =====================================================
       RESPONSIVE
    ===================================================== */

    @media (max-width: 768px) {

        .vendedores-header {
            align-items: flex-start;
            gap: 12px;
        }

        .vendedores-header h1 {
            font-size: 27px;
        }

        .vendedores-subtitle {
            font-size: 14px;
        }

        .btn-nuevo {
            padding: 10px 13px;
            font-size: 14px;
        }

        .filtros-vendedores {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .filtros-vendedores input {
            grid-column: 1 / -1;
            min-width: 0;
            max-width: none;
            width: 100%;
        }

        .filtro-select {
            grid-column: 1 / -1;
            width: 100%;
            min-width: 0;
        }

        .btn-filtrar,
        .btn-limpiar {
            width: 100%;
            box-sizing: border-box;
            text-align: center;
        }

        .vendedores-desktop {
            display: none;
        }

        .vendedores-mobile {
            display: block;
        }

        .modal-botones {
            flex-direction: column-reverse;
        }

        .modal-btn {
            width: 100%;
        }
    }

</style>


<div class="vendedores-header">

    <div>

        <h1>
            Vendedores
        </h1>

        <div class="vendedores-subtitle">
            Administración de vendedores del sistema
        </div>

    </div>


    <a
        href="{{ route('vendedores.create') }}"
        class="btn-nuevo"
    >
        Nuevo vendedor
    </a>

</div>


{{-- =====================================================
     ALERTA
===================================================== --}}

@if(session('success'))

    <div
        class="alert-success"
        id="alert-success"
    >
        {{ session('success') }}
    </div>

@endif


{{-- =====================================================
     INVITACIÓN
===================================================== --}}

@if(session('enlace_invitacion'))

    <div class="alert-invitacion">

        <div style="font-weight:600; margin-bottom:6px;">
            Enlace de invitación — Solo desarrollo
        </div>

        <a
            href="{{ session('enlace_invitacion') }}"
            target="_blank"
        >
            {{ session('enlace_invitacion') }}
        </a>

    </div>

@endif


{{-- =====================================================
     FILTROS
===================================================== --}}

<form
    method="GET"
    action="{{ route('vendedores.index') }}"
    class="buscador"
>

    <div class="filtros-vendedores">


        {{-- BUSCADOR --}}

        <input
            type="text"
            name="q"
            value="{{ $q }}"
            placeholder="Buscar por nombre, correo, código, teléfono o DPI..."
            autocomplete="off"
        >


        {{-- ESTADO --}}

        <select
            name="estado"
            class="filtro-select"
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


        {{-- SUPERVISOR SOLO ADMIN --}}

        @if((int) auth()->user()->id_rol === 1)

            <select
                name="id_supervisor"
                class="filtro-select filtro-supervisor"
            >

                <option value="">
                    Todos los supervisores
                </option>


                @foreach(
                    $supervisores
                    as $supervisor
                )

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


        {{-- FILTRAR --}}

        <button
            type="submit"
            class="btn-filtrar"
        >
            Filtrar
        </button>


        {{-- LIMPIAR --}}

        @if(
            request()->filled('q') ||
            request()->filled('estado') ||
            request()->filled('id_supervisor')
        )

            <a
                href="{{ route('vendedores.index') }}"
                class="btn-limpiar"
            >
                Limpiar
            </a>

        @endif

    </div>

</form>


{{-- =====================================================
     ESCRITORIO
===================================================== --}}

<div class="vendedores-desktop">

    <div class="tabla-wrapper">

        <table class="tabla-vendedores">

            <thead>

                <tr>

                    <th>Código</th>

                    <th>Vendedor</th>

                    <th>Correo</th>

                    <th>Teléfono</th>


                    @if((int) auth()->user()->id_rol === 1)

                        <th>
                            Supervisor
                        </th>

                    @endif


                    <th>Estado</th>

                    <th>Acciones</th>

                </tr>

            </thead>


            <tbody>

                @forelse(
                    $vendedores
                    as $vendedor
                )

                    <tr
                        class="fila-vendedor"
                        onclick="window.location='{{ route(
                            'vendedores.edit',
                            $vendedor->id_vendedor
                        ) }}'"
                    >


                        <td>
                            {{ $vendedor->codigo_empleado ?? '-' }}
                        </td>


                        <td>

                            {{ $vendedor->usuario->nombre }}
                            {{ $vendedor->usuario->apellido }}

                        </td>


                        <td>
                            {{ $vendedor->usuario->correo }}
                        </td>


                        <td>
                            {{ $vendedor->telefono ?? '-' }}
                        </td>


                        {{-- SUPERVISOR SOLO ADMIN --}}

                        @if((int) auth()->user()->id_rol === 1)

                            <td>

                                @if($vendedor->supervisor)

                                    {{ $vendedor->supervisor->nombre }}
                                    {{ $vendedor->supervisor->apellido }}

                                @else

                                    <span style="color:#9ca3af;">
                                        Sin supervisor
                                    </span>

                                @endif

                            </td>

                        @endif


                        {{-- ESTADO --}}

                        <td>

                            @if($vendedor->activo)

                                <span class="estado estado-activo">
                                    Activo
                                </span>

                            @else

                                <span class="estado estado-inactivo">
                                    Inactivo
                                </span>

                            @endif

                        </td>


                        {{-- ACCIONES --}}

                        <td>

                            <div
                                class="acciones"
                                onclick="event.stopPropagation();"
                            >

                                <form
                                    id="form-estado-{{ $vendedor->id_vendedor }}"
                                    method="POST"
                                    action="{{ route(
                                        'vendedores.estado',
                                        $vendedor->id_vendedor
                                    ) }}"
                                >

                                    @csrf
                                    @method('PATCH')


                                    <button
                                        type="button"
                                        class="btn-accion {{ $vendedor->activo
                                            ? 'btn-desactivar'
                                            : 'btn-activar' }}"
                                        onclick="abrirModalEstado(
                                            {{ $vendedor->id_vendedor }},
                                            '{{ $vendedor->activo
                                                ? 'desactivar'
                                                : 'activar' }}',
                                            @js(
                                                $vendedor->usuario->nombre
                                                . ' '
                                                . $vendedor->usuario->apellido
                                            )
                                        )"
                                    >

                                        {{ $vendedor->activo
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
                            style="
                                padding:30px;
                                text-align:center;
                                color:#6b7280;
                            "
                        >
                            No hay vendedores registrados con los filtros seleccionados.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>


{{-- =====================================================
     MÓVIL
===================================================== --}}

<div class="vendedores-mobile">

    @forelse(
        $vendedores
        as $vendedor
    )

        <div
            class="vendedor-card"
            onclick="window.location='{{ route(
                'vendedores.edit',
                $vendedor->id_vendedor
            ) }}'"
        >


            <div class="vendedor-card-header">

                <div>

                    <div class="vendedor-nombre">

                        {{ $vendedor->usuario->nombre }}
                        {{ $vendedor->usuario->apellido }}

                    </div>


                    <div class="vendedor-codigo">

                        Código:

                        {{ $vendedor->codigo_empleado ?? '-' }}

                    </div>

                </div>


                @if($vendedor->activo)

                    <span class="estado estado-activo">
                        Activo
                    </span>

                @else

                    <span class="estado estado-inactivo">
                        Inactivo
                    </span>

                @endif

            </div>


            <div class="vendedor-dato">

                <strong>
                    Correo:
                </strong>

                <br>

                {{ $vendedor->usuario->correo }}

            </div>


            <div class="vendedor-dato">

                <strong>
                    Teléfono:
                </strong>

                {{ $vendedor->telefono ?? '-' }}

            </div>


            @if($vendedor->dpi)

                <div class="vendedor-dato">

                    <strong>
                        DPI:
                    </strong>

                    {{ $vendedor->dpi }}

                </div>

            @endif


            {{-- SUPERVISOR SOLO ADMIN --}}

            @if((int) auth()->user()->id_rol === 1)

                <div class="vendedor-dato">

                    <strong>
                        Supervisor:
                    </strong>

                    @if($vendedor->supervisor)

                        {{ $vendedor->supervisor->nombre }}
                        {{ $vendedor->supervisor->apellido }}

                    @else

                        Sin supervisor

                    @endif

                </div>

            @endif


            {{-- ACCIÓN --}}

            <div
                class="acciones-mobile"
                onclick="event.stopPropagation();"
            >

                <form
                    id="form-estado-mobile-{{ $vendedor->id_vendedor }}"
                    method="POST"
                    action="{{ route(
                        'vendedores.estado',
                        $vendedor->id_vendedor
                    ) }}"
                >

                    @csrf
                    @method('PATCH')


                    <button
                        type="button"
                        class="btn-accion {{ $vendedor->activo
                            ? 'btn-desactivar'
                            : 'btn-activar' }}"
                        onclick="abrirModalEstadoMobile(
                            {{ $vendedor->id_vendedor }},
                            '{{ $vendedor->activo
                                ? 'desactivar'
                                : 'activar' }}',
                            @js(
                                $vendedor->usuario->nombre
                                . ' '
                                . $vendedor->usuario->apellido
                            )
                        )"
                    >

                        {{ $vendedor->activo
                            ? 'Desactivar'
                            : 'Activar' }}

                    </button>

                </form>

            </div>

        </div>

    @empty

        <div class="sin-registros">

            No hay vendedores registrados con los filtros seleccionados.

        </div>

    @endforelse

</div>


{{-- =====================================================
     PAGINACIÓN
===================================================== --}}

<div class="paginacion">

    {{ $vendedores->links() }}

</div>


{{-- =====================================================
     MODAL ACTIVAR / DESACTIVAR
===================================================== --}}

<div
    id="modalEstado"
    class="modal-overlay"
>

    <div class="modal-box">

        <div
            class="modal-icon"
            id="modalIcon"
        >
            !
        </div>


        <h3 id="modalTitulo">
            Confirmar acción
        </h3>


        <p id="modalMensaje"></p>


        <div class="modal-botones">

            <button
                type="button"
                class="modal-btn modal-cancelar"
                onclick="cerrarModalEstado()"
            >
                Cancelar
            </button>


            <button
                type="button"
                class="modal-btn modal-confirmar"
                id="btnConfirmarEstado"
            >
                Confirmar
            </button>

        </div>

    </div>

</div>


<script>

    let formularioEstado = null;


    /*
    |--------------------------------------------------------------------------
    | ESCRITORIO
    |--------------------------------------------------------------------------
    */

    function abrirModalEstado(
        id,
        accion,
        nombre
    ) {

        formularioEstado =
            document.getElementById(
                'form-estado-' + id
            );

        configurarModal(
            accion,
            nombre
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MÓVIL
    |--------------------------------------------------------------------------
    */

    function abrirModalEstadoMobile(
        id,
        accion,
        nombre
    ) {

        formularioEstado =
            document.getElementById(
                'form-estado-mobile-' + id
            );

        configurarModal(
            accion,
            nombre
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIGURAR MODAL
    |--------------------------------------------------------------------------
    */

    function configurarModal(
        accion,
        nombre
    ) {

        const modal =
            document.getElementById(
                'modalEstado'
            );

        const titulo =
            document.getElementById(
                'modalTitulo'
            );

        const mensaje =
            document.getElementById(
                'modalMensaje'
            );

        const boton =
            document.getElementById(
                'btnConfirmarEstado'
            );

        const icono =
            document.getElementById(
                'modalIcon'
            );


        if (accion === 'desactivar') {

            titulo.textContent =
                'Desactivar vendedor';

            mensaje.textContent =
                '¿Deseas desactivar a '
                + nombre
                + '? El usuario ya no podrá iniciar sesión en el sistema.';

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

        } else {

            titulo.textContent =
                'Activar vendedor';

            mensaje.textContent =
                '¿Deseas activar nuevamente a '
                + nombre
                + '? El usuario podrá volver a iniciar sesión.';

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
            'activo'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CERRAR
    |--------------------------------------------------------------------------
    */

    function cerrarModalEstado() {

        document
            .getElementById(
                'modalEstado'
            )
            .classList
            .remove(
                'activo'
            );

        formularioEstado =
            null;
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
    | CLICK FUERA
    |--------------------------------------------------------------------------
    */

    document
        .getElementById(
            'modalEstado'
        )
        .addEventListener(
            'click',
            function (e) {

                if (e.target === this) {

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
        function (e) {

            if (e.key === 'Escape') {

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
                    'alert-success'
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

@endsection
