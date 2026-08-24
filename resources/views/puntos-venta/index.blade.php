@extends('layouts.app')

@section('title', 'Puntos de venta')

@section('content')

<div class="page-header">

    <div>
        <h1>Puntos de venta</h1>
        <p>Administra las ubicaciones donde deben presentarse los vendedores.</p>
    </div>

    <a href="{{ route('puntos-venta.create') }}" class="btn-primary">
        + Nuevo punto
    </a>

</div>


<form method="GET"
      action="{{ route('puntos-venta.index') }}"
      class="search-box">

    <input
        type="text"
        name="q"
        value="{{ $q }}"
        placeholder="Buscar punto de venta..."
    >

    <button type="submit">
        Buscar
    </button>

    @if($q)
        <a href="{{ route('puntos-venta.index') }}">
            Limpiar
        </a>
    @endif

</form>


<div class="table-card">

    <div class="table-responsive">

        <table>

            <thead>
                <tr>
                    <th>Punto de venta</th>
                    <th>Ubicación</th>
                    <th>Coordenadas</th>
                    <th>Radio permitido</th>
                    <th>Estado</th>
                </tr>
            </thead>

            <tbody>

                @forelse($puntos as $punto)

                    <tr>

                        <td>
                            <strong>
                                {{ $punto->nombre }}
                            </strong>

                            <div class="secondary">
                                {{ $punto->direccion }}
                            </div>
                        </td>

                        <td>
                            {{ $punto->municipio }},
                            {{ $punto->departamento }}
                        </td>

                        <td>
                            <div>
                                {{ $punto->latitud }}
                            </div>

                            <div>
                                {{ $punto->longitud }}
                            </div>
                        </td>

                        <td>
                            {{ $punto->radio_permitido_metros }} m
                        </td>

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

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="empty">
                            No hay puntos de venta registrados.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    @if($puntos->hasPages())

        <div class="pagination">
            {{ $puntos->links() }}
        </div>

    @endif

</div>

@endsection


@push('styles')

<style>

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


    .search-box {
        display: flex;
        align-items: center;

        gap: 8px;

        margin-bottom: 18px;
    }

    .search-box input {
        width: 100%;
        max-width: 420px;

        padding: 11px 13px;

        border: 1px solid #d1d5db;
        border-radius: 8px;

        background: #ffffff;

        outline: none;
    }

    .search-box input:focus {
        border-color: #2563eb;
    }

    .search-box button {
        padding: 11px 16px;

        border: 0;
        border-radius: 8px;

        background: #071f3d;
        color: #ffffff;

        cursor: pointer;
    }

    .search-box a {
        color: #64748b;

        text-decoration: none;
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
    }

    th {
        padding: 14px 16px;

        background: #f8fafc;

        color: #475569;

        font-size: 13px;
        font-weight: 700;

        text-align: left;

        border-bottom: 1px solid #e5e7eb;
    }

    td {
        padding: 15px 16px;

        border-bottom: 1px solid #eef2f7;

        vertical-align: middle;
    }

    tbody tr:last-child td {
        border-bottom: 0;
    }

    tbody tr:hover {
        background: #f8fafc;
    }

    .secondary {
        margin-top: 4px;

        color: #64748b;

        font-size: 13px;
    }


    .status {
        display: inline-flex;

        padding: 5px 9px;

        border-radius: 20px;

        font-size: 12px;
        font-weight: 700;
    }

    .status.active {
        background: #dcfce7;
        color: #166534;
    }

    .status.inactive {
        background: #fee2e2;
        color: #991b1b;
    }


    .empty {
        padding: 35px;

        color: #64748b;

        text-align: center;
    }


    .pagination {
        padding: 15px;

        border-top: 1px solid #e5e7eb;
    }


    @media (max-width: 768px) {

        .page-header {
            align-items: stretch;
            flex-direction: column;
        }

        .btn-primary {
            text-align: center;
        }

        .search-box {
            align-items: stretch;
            flex-direction: column;
        }

        .search-box input {
            max-width: none;
        }

        th,
        td {
            white-space: nowrap;
        }

    }

</style>

@endpush
