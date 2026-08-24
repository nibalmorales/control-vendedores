@extends('layouts.app')

@section('title', 'Asignaciones')

@section('content')

<div class="page-header">

    <div>
        <h1>Asignaciones</h1>
        <p>Administra qué vendedor debe trabajar en cada punto de venta y horario.</p>
    </div>

    <a href="{{ route('asignaciones.create') }}" class="btn-primary">
        + Nueva asignación
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
                    <th>Vendedor</th>
                    <th>Punto de venta</th>
                    <th>Horario</th>
                    <th>Días</th>
                    <th>Vigencia</th>
                    <th>Estado</th>
                </tr>

            </thead>

            <tbody>

                @forelse($asignaciones as $asignacion)

                    <tr>

                        <td>

                            <strong>
                                {{ $asignacion->vendedor->usuario->nombre }}
                                {{ $asignacion->vendedor->usuario->apellido }}
                            </strong>

                            <div class="secondary">
                                {{ $asignacion->vendedor->codigo_empleado ?? '-' }}
                            </div>

                        </td>


                        <td data-label="Punto de venta">

                            @if($asignacion->puntoVenta)

                                <strong>
                                    {{ $asignacion->puntoVenta->nombre }}
                                </strong>

                                <div class="secondary">

                                    {{ $asignacion->puntoVenta->municipio }}

                                    @if($asignacion->puntoVenta->departamento)
                                        ,
                                        {{ $asignacion->puntoVenta->departamento }}
                                    @endif

                                </div>

                            @else

                                <strong>
                                    Supervisor móvil
                                </strong>

                                <div class="secondary">
                                    Sin punto fijo
                                </div>

                            @endif

                        </td>


                        <td>

                            {{ \Carbon\Carbon::parse($asignacion->horario->hora_entrada)->format('H:i') }}

                            –

                            {{ \Carbon\Carbon::parse($asignacion->horario->hora_salida)->format('H:i') }}

                            <div class="secondary">
                                {{ $asignacion->horario->nombre }}
                            </div>

                        </td>


                        <td>

                            <div class="days">

                                @if($asignacion->lunes)
                                    <span>L</span>
                                @endif

                                @if($asignacion->martes)
                                    <span>M</span>
                                @endif

                                @if($asignacion->miercoles)
                                    <span>X</span>
                                @endif

                                @if($asignacion->jueves)
                                    <span>J</span>
                                @endif

                                @if($asignacion->viernes)
                                    <span>V</span>
                                @endif

                                @if($asignacion->sabado)
                                    <span>S</span>
                                @endif

                                @if($asignacion->domingo)
                                    <span>D</span>
                                @endif

                            </div>

                        </td>


                        <td>

                            {{ $asignacion->fecha_inicio->format('d/m/Y') }}

                            <div class="secondary">

                                @if($asignacion->fecha_fin)

                                    hasta
                                    {{ $asignacion->fecha_fin->format('d/m/Y') }}

                                @else

                                    Sin fecha final

                                @endif

                            </div>

                        </td>


                        <td>

                            @if($asignacion->activo)

                                <span class="status active">
                                    Activa
                                </span>

                            @else

                                <span class="status inactive">
                                    Inactiva
                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6" class="empty">
                            No hay asignaciones registradas.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    @if($asignaciones->hasPages())

        <div class="pagination">
            {{ $asignaciones->links() }}
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
    }

    .page-header p {
        margin: 6px 0 0;
        color: #64748b;
    }

    .btn-primary {
        padding: 11px 17px;
        border-radius: 8px;
        background: #2563eb;
        color: white;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
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
        background: white;
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

    .secondary {
        margin-top: 4px;
        color: #64748b;
        font-size: 12px;
    }

    .days {
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
    }

    .days span {
        width: 25px;
        height: 25px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #e0ecff;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 700;
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
        text-align: center;
        color: #64748b;
    }

    .pagination {
        padding: 15px;
        border-top: 1px solid #e5e7eb;
    }

    @media (max-width: 768px) {

        .page-header {
            flex-direction: column;
            align-items: stretch;
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

                mensaje.style.transition = 'opacity .4s ease';
                mensaje.style.opacity = '0';

                setTimeout(function () {
                    mensaje.remove();
                }, 400);

            }, 3000);

        }

    });
</script>

@endpush
