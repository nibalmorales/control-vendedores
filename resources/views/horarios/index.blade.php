@extends('layouts.app')

@section('title', 'Horarios')

@section('content')

<div class="page-header">

    <div>
        <h1>Horarios</h1>
        <p>Administra los horarios disponibles para asignar a los vendedores.</p>
    </div>

    <a href="{{ route('horarios.create') }}" class="btn-primary">
        + Nuevo horario
    </a>

</div>


@if(session('success'))

    <div class="alert-success" id="successMessage">
        {{ session('success') }}
    </div>

@endif


<div class="table-card">

    <div class="table-responsive">

        <table>

            <thead>

                <tr>
                    <th>Horario</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Tolerancia</th>
                    <th>Estado</th>
                </tr>

            </thead>

            <tbody>

                @forelse($horarios as $horario)

                    <tr>

                        <td>

                            <strong>
                                {{ $horario->nombre }}
                            </strong>

                        </td>


                        <td>

                            {{ \Carbon\Carbon::parse($horario->hora_entrada)->format('H:i') }}

                        </td>


                        <td>

                            {{ \Carbon\Carbon::parse($horario->hora_salida)->format('H:i') }}

                        </td>


                        <td>

                            {{ $horario->tolerancia_minutos }} min

                        </td>


                        <td>

                            @if($horario->activo)

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

                            No hay horarios registrados.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    @if($horarios->hasPages())

        <div class="pagination">
            {{ $horarios->links() }}
        </div>

    @endif

</div>

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


    .btn-primary {
        padding: 11px 17px;

        border-radius: 8px;

        background: #2563eb;
        color: #ffffff;

        font-weight: 600;

        text-decoration: none;

        white-space: nowrap;
    }


    .btn-primary:hover {
        background: #1d4ed8;
    }


    .alert-success {
        margin-bottom: 18px;

        padding: 13px 15px;

        border: 1px solid #bbf7d0;
        border-radius: 8px;

        background: #ecfdf5;
        color: #166534;
    }


    .table-card {
        overflow: hidden;

        border: 1px solid #e5e7eb;
        border-radius: 12px;

        background: #ffffff;
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


        th,
        td {
            white-space: nowrap;
        }

    }

</style>

@endpush

@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const mensaje = document.getElementById('successMessage');

        if (mensaje) {

            setTimeout(function () {

                mensaje.style.transition = 'opacity 0.4s ease';
                mensaje.style.opacity = '0';

                setTimeout(function () {
                    mensaje.remove();
                }, 400);

            }, 3000);

        }

    });
</script>

@endpush
