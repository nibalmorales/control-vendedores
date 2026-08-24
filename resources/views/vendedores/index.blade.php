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

    .alert-success {
        background: #ecfdf5;
        color: #065f46;
        padding: 14px;
        border-radius: 8px;
        margin-bottom: 20px;
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

    .buscador {
        margin-bottom: 22px;
    }

    .buscador input {
        width: 100%;
        max-width: 560px;
        padding: 12px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 15px;
        background: #ffffff;
    }

    .buscador input:focus {
        outline: none;
        border-color: #2563eb;
    }

    /* =========================
       VERSIÓN ESCRITORIO
    ========================= */

    .vendedores-desktop {
        display: block;
    }

    .tabla-wrapper {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }

    .tabla-vendedores {
        width: 100%;
        border-collapse: collapse;
    }

    .tabla-vendedores thead {
        background: #f9fafb;
    }

    .tabla-vendedores th {
        padding: 15px;
        text-align: left;
        font-size: 15px;
    }

    .tabla-vendedores td {
        padding: 15px;
        border-top: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .estado {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 13px;
    }

    .estado-activo {
        background: #ecfdf5;
        color: #065f46;
    }

    .estado-inactivo {
        background: #f3f4f6;
        color: #6b7280;
    }

    /* =========================
       VERSIÓN MÓVIL
    ========================= */

    .vendedores-mobile {
        display: none;
    }

    .vendedor-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 12px;
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
            line-height: 1.4;
        }

        .btn-nuevo {
            padding: 10px 13px;
            font-size: 14px;
            text-align: center;
        }

        .buscador {
            margin-bottom: 18px;
        }

        .buscador input {
            max-width: none;
            font-size: 14px;
        }

        .vendedores-desktop {
            display: none;
        }

        .vendedores-mobile {
            display: block;
        }

        .alert-invitacion {
            font-size: 14px;
        }
    }

    @media (max-width: 390px) {

        .vendedores-header h1 {
            font-size: 25px;
        }

        .btn-nuevo {
            max-width: 110px;
        }
    }
</style>


<div class="vendedores-header">

    <div>
        <h1>Vendedores</h1>

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


@if(session('success'))

    <div class="alert-success">
        {{ session('success') }}
    </div>

@endif


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


<form
    method="GET"
    action="{{ route('vendedores.index') }}"
    class="buscador"
>

    <input
        type="text"
        name="q"
        value="{{ $q }}"
        placeholder="Buscar por nombre, correo, código, teléfono o DPI..."
    >

</form>


{{-- =====================================================
     VERSIÓN ESCRITORIO
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
                    <th>Estado</th>
                </tr>

            </thead>

            <tbody>

                @forelse($vendedores as $vendedor)

                    <tr>

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

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="5"
                            style="
                                padding:30px;
                                text-align:center;
                                color:#6b7280;
                            "
                        >
                            No hay vendedores registrados.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>


{{-- =====================================================
     VERSIÓN MÓVIL
===================================================== --}}

<div class="vendedores-mobile">

    @forelse($vendedores as $vendedor)

        <div class="vendedor-card">

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
                <strong>Correo:</strong><br>
                {{ $vendedor->usuario->correo }}
            </div>

            <div class="vendedor-dato">
                <strong>Teléfono:</strong>
                {{ $vendedor->telefono ?? '-' }}
            </div>

            @if($vendedor->dpi)

                <div class="vendedor-dato">
                    <strong>DPI:</strong>
                    {{ $vendedor->dpi }}
                </div>

            @endif

        </div>

    @empty

        <div class="sin-registros">
            No hay vendedores registrados.
        </div>

    @endforelse

</div>


<div class="paginacion">
    {{ $vendedores->links() }}
</div>

@endsection
