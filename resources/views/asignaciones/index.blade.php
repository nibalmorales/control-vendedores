@extends('layouts.app')

@section('title', 'Asignaciones')

@section('content')

<div class="page-header">
    <div>
        <h1>Asignaciones</h1>
        <p>Administra jornadas, puntos de venta, horarios y días de trabajo.</p>
    </div>

    <a href="{{ route('asignaciones.create') }}" class="btn-primary">
        + Nueva asignación
    </a>
</div>


@if (session('success'))
    <div class="alert-success" id="successAlert">
        {{ session('success') }}
    </div>
@endif


<form
    method="GET"
    action="{{ route('asignaciones.index') }}"
    class="filters-card"
>
    <div class="filter-search">
        <label for="q">Buscar</label>

        <input
            type="text"
            name="q"
            id="q"
            value="{{ $q ?? request('q') }}"
            placeholder="Vendedor, código, punto u horario..."
        >
    </div>

    <div class="filter-state">
        <label for="estado">Estado</label>

        <select name="estado" id="estado">
            <option value="">Todos</option>

            <option
                value="1"
                {{ (string)($estado ?? request('estado')) === '1' ? 'selected' : '' }}
            >
                Activas
            </option>

            <option
                value="0"
                {{ (string)($estado ?? request('estado')) === '0' ? 'selected' : '' }}
            >
                Inactivas
            </option>
        </select>
    </div>

    <div class="filter-actions">
        <button type="submit" class="btn-filter">
            Filtrar
        </button>

        @if(
            filled($q ?? request('q')) ||
            ($estado ?? request('estado')) !== null &&
            ($estado ?? request('estado')) !== ''
        )
            <a
                href="{{ route('asignaciones.index') }}"
                class="btn-clear"
            >
                Limpiar
            </a>
        @endif
    </div>
</form>


<div class="desktop-view">

    <div class="table-card">
        <div class="table-responsive">

            <table>
                <thead>
                    <tr>
                        <th>Colaborador</th>
                        <th>Punto de venta</th>
                        <th>Horario</th>
                        <th>Días</th>
                        <th>Vigencia</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($asignaciones as $asignacion)

                        @php
                            $vendedor = $asignacion->vendedor;
                            $usuario = $vendedor?->usuario;
                            $rol = strtoupper($usuario?->rol?->nombre ?? '');
                            $esSupervisor = $rol === 'SUPERVISOR';

                            $dias = collect([
                                'Lun' => $asignacion->lunes,
                                'Mar' => $asignacion->martes,
                                'Mié' => $asignacion->miercoles,
                                'Jue' => $asignacion->jueves,
                                'Vie' => $asignacion->viernes,
                                'Sáb' => $asignacion->sabado,
                                'Dom' => $asignacion->domingo,
                            ])->filter();
                        @endphp

                        <tr
                            class="clickable-row"
                            onclick="window.location='{{ route('asignaciones.edit', $asignacion->id_asignacion) }}'"
                        >

                            <td>
                                <strong>
                                    {{ $usuario?->nombre ?? 'Sin nombre' }}
                                    {{ $usuario?->apellido ?? '' }}
                                </strong>

                                <div class="secondary">
                                    {{ $vendedor?->codigo_empleado ?? 'Sin código' }}
                                    @if($rol)
                                        · {{ ucfirst(strtolower($rol)) }}
                                    @endif
                                </div>
                            </td>

                            <td>
                                @if($esSupervisor)
                                    <span class="mobile-point">
                                        Supervisor móvil
                                    </span>
                                @else
                                    {{ $asignacion->puntoVenta?->nombre ?? 'Sin punto' }}
                                @endif
                            </td>

                            <td>
                                <strong>
                                    {{ $asignacion->horario?->nombre ?? 'Sin horario' }}
                                </strong>

                                @if($asignacion->horario)
                                    <div class="secondary">
                                        {{ \Carbon\Carbon::parse($asignacion->horario->hora_entrada)->format('H:i') }}
                                        -
                                        {{ \Carbon\Carbon::parse($asignacion->horario->hora_salida)->format('H:i') }}
                                    </div>
                                @endif

                                @if($asignacion->horario && !$asignacion->horario->activo)
                                    <div class="warning-text">
                                        Horario inactivo
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div class="days-list">
                                    @foreach($dias as $dia => $valor)
                                        <span>{{ $dia }}</span>
                                    @endforeach
                                </div>
                            </td>

                            <td>
                                <div>
                                    {{ optional($asignacion->fecha_inicio)->format('d/m/Y') ?? $asignacion->fecha_inicio }}
                                </div>

                                <div class="secondary">
                                    @if($asignacion->fecha_fin)
                                        Hasta
                                        {{ optional($asignacion->fecha_fin)->format('d/m/Y') ?? $asignacion->fecha_fin }}
                                    @else
                                        Sin fecha fin
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

                            <td onclick="event.stopPropagation()">
                                <div class="actions">

                                    <a
                                        href="{{ route('asignaciones.edit', $asignacion->id_asignacion) }}"
                                        class="btn-edit"
                                    >
                                        Editar
                                    </a>

                                    <button
                                        type="button"
                                        class="{{ $asignacion->activo ? 'btn-disable' : 'btn-enable' }}"
                                        onclick="abrirModalEstado(
                                            {{ $asignacion->id_asignacion }},
                                            '{{ $asignacion->activo ? 'desactivar' : 'activar' }}',
                                            @js(trim(($usuario?->nombre ?? '') . ' ' . ($usuario?->apellido ?? '')))
                                        )"
                                    >
                                        {{ $asignacion->activo ? 'Desactivar' : 'Activar' }}
                                    </button>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="empty">
                                No hay asignaciones que coincidan con los filtros.
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

</div>


<div class="mobile-view">

    @forelse($asignaciones as $asignacion)

        @php
            $vendedor = $asignacion->vendedor;
            $usuario = $vendedor?->usuario;
            $rol = strtoupper($usuario?->rol?->nombre ?? '');
            $esSupervisor = $rol === 'SUPERVISOR';

            $dias = collect([
                'Lun' => $asignacion->lunes,
                'Mar' => $asignacion->martes,
                'Mié' => $asignacion->miercoles,
                'Jue' => $asignacion->jueves,
                'Vie' => $asignacion->viernes,
                'Sáb' => $asignacion->sabado,
                'Dom' => $asignacion->domingo,
            ])->filter();
        @endphp

        <div class="assignment-card">

            <div class="assignment-card-header">
                <div>
                    <div class="assignment-name">
                        {{ $usuario?->nombre ?? 'Sin nombre' }}
                        {{ $usuario?->apellido ?? '' }}
                    </div>

                    <div class="secondary">
                        {{ $vendedor?->codigo_empleado ?? 'Sin código' }}
                    </div>
                </div>

                @if($asignacion->activo)
                    <span class="status active">Activa</span>
                @else
                    <span class="status inactive">Inactiva</span>
                @endif
            </div>

            <div class="card-data">
                <span>Punto</span>
                <strong>
                    {{ $esSupervisor
                        ? 'Supervisor móvil'
                        : ($asignacion->puntoVenta?->nombre ?? 'Sin punto')
                    }}
                </strong>
            </div>

            <div class="card-data">
                <span>Horario</span>
                <strong>
                    {{ $asignacion->horario?->nombre ?? 'Sin horario' }}
                </strong>

                @if($asignacion->horario && !$asignacion->horario->activo)
                    <small class="warning-text">
                        Horario inactivo
                    </small>
                @endif
            </div>

            <div class="card-data">
                <span>Días</span>

                <div class="days-list">
                    @foreach($dias as $dia => $valor)
                        <span>{{ $dia }}</span>
                    @endforeach
                </div>
            </div>

            <div class="card-data">
                <span>Vigencia</span>
                <strong>
                    {{ optional($asignacion->fecha_inicio)->format('d/m/Y') ?? $asignacion->fecha_inicio }}
                    -
                    {{ $asignacion->fecha_fin
                        ? (optional($asignacion->fecha_fin)->format('d/m/Y') ?? $asignacion->fecha_fin)
                        : 'Sin fecha fin'
                    }}
                </strong>
            </div>

            <div class="mobile-actions">

                <a
                    href="{{ route('asignaciones.edit', $asignacion->id_asignacion) }}"
                    class="btn-edit"
                >
                    Editar
                </a>

                <button
                    type="button"
                    class="{{ $asignacion->activo ? 'btn-disable' : 'btn-enable' }}"
                    onclick="abrirModalEstado(
                        {{ $asignacion->id_asignacion }},
                        '{{ $asignacion->activo ? 'desactivar' : 'activar' }}',
                        @js(trim(($usuario?->nombre ?? '') . ' ' . ($usuario?->apellido ?? '')))
                    )"
                >
                    {{ $asignacion->activo ? 'Desactivar' : 'Activar' }}
                </button>

            </div>

        </div>

    @empty

        <div class="empty-mobile">
            No hay asignaciones que coincidan con los filtros.
        </div>

    @endforelse


    @if($asignaciones->hasPages())
        <div class="pagination mobile-pagination">
            {{ $asignaciones->links() }}
        </div>
    @endif

</div>


<div class="modal-backdrop" id="modalEstado">
    <div class="modal-box">

        <h3 id="modalTitulo">
            Cambiar estado
        </h3>

        <p id="modalTexto"></p>

        <form
            method="POST"
            id="formEstado"
        >
            @csrf
            @method('PATCH')

            <div class="modal-actions">
                <button
                    type="button"
                    class="btn-modal-cancel"
                    onclick="cerrarModalEstado()"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="btn-modal-confirm"
                    id="btnConfirmarEstado"
                >
                    Confirmar
                </button>
            </div>
        </form>

    </div>
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

    .filters-card {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) 190px auto;
        align-items: end;
        gap: 12px;
        padding: 16px;
        margin-bottom: 18px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }

    .filter-search,
    .filter-state {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filters-card label {
        color: #475569;
        font-size: 12px;
        font-weight: 700;
    }

    .filters-card input,
    .filters-card select {
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid #d5dbe5;
        border-radius: 8px;
        background: #ffffff;
        color: #172033;
        font-size: 14px;
        outline: none;
    }

    .filters-card input:focus,
    .filters-card select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
    }

    .filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-filter,
    .btn-clear {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 15px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
    }

    .btn-filter {
        border: 0;
        background: #071f3d;
        color: #ffffff;
        cursor: pointer;
    }

    .btn-clear {
        border: 1px solid #d5dbe5;
        background: #ffffff;
        color: #475569;
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
        padding: 14px 15px;
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        text-align: left;
        white-space: nowrap;
    }

    td {
        padding: 15px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
    }

    tbody tr:last-child td {
        border-bottom: 0;
    }

    .clickable-row {
        cursor: pointer;
        transition: background .15s ease;
    }

    .clickable-row:hover {
        background: #f8fafc;
    }

    .secondary {
        margin-top: 4px;
        color: #64748b;
        font-size: 12px;
    }

    .mobile-point {
        color: #475569;
        font-weight: 600;
    }

    .warning-text {
        display: block;
        margin-top: 4px;
        color: #b45309;
        font-size: 11px;
        font-weight: 700;
    }

    .days-list {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }

    .days-list span {
        padding: 3px 6px;
        border-radius: 5px;
        background: #eef2ff;
        color: #3730a3;
        font-size: 10px;
        font-weight: 700;
    }

    .status {
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
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

    .actions {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .btn-edit,
    .btn-disable,
    .btn-enable {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 7px 10px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-edit {
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .btn-disable {
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #b91c1c;
    }

    .btn-enable {
        border: 1px solid #bbf7d0;
        background: #f0fdf4;
        color: #15803d;
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

    .mobile-view {
        display: none;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .48);
    }

    .modal-backdrop.show {
        display: flex;
    }

    .modal-box {
        width: 100%;
        max-width: 430px;
        padding: 24px;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 20px 60px rgba(15, 23, 42, .20);
    }

    .modal-box h3 {
        margin: 0 0 8px;
        font-size: 20px;
    }

    .modal-box p {
        margin: 0;
        color: #64748b;
        line-height: 1.5;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 24px;
    }

    .btn-modal-cancel,
    .btn-modal-confirm {
        padding: 10px 14px;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
    }

    .btn-modal-cancel {
        border: 1px solid #d5dbe5;
        background: #ffffff;
        color: #475569;
    }

    .btn-modal-confirm {
        border: 0;
        background: #071f3d;
        color: #ffffff;
    }

    @media (max-width: 900px) {

        .desktop-view {
            display: none;
        }

        .mobile-view {
            display: block;
        }

        .filters-card {
            grid-template-columns: 1fr;
        }

        .assignment-card {
            padding: 16px;
            margin-bottom: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #ffffff;
        }

        .assignment-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .assignment-name {
            color: #172033;
            font-size: 16px;
            font-weight: 700;
        }

        .card-data {
            margin-top: 11px;
        }

        .card-data > span {
            display: block;
            margin-bottom: 3px;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .card-data > strong {
            display: block;
            color: #334155;
            font-size: 13px;
        }

        .mobile-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid #eef2f7;
        }

        .mobile-actions .btn-edit,
        .mobile-actions .btn-disable,
        .mobile-actions .btn-enable {
            width: 100%;
        }

        .empty-mobile {
            padding: 30px 18px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #ffffff;
            color: #64748b;
            text-align: center;
        }

        .mobile-pagination {
            padding-left: 0;
            padding-right: 0;
            border-top: 0;
        }
    }

    @media (max-width: 640px) {

        .page-header {
            align-items: stretch;
            flex-direction: column;
        }

        .btn-primary {
            text-align: center;
        }

        .filter-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
    }

</style>

@endpush


@push('scripts')

<script>

    function abrirModalEstado(id, accion, nombre) {

        const modal = document.getElementById('modalEstado');
        const form = document.getElementById('formEstado');
        const titulo = document.getElementById('modalTitulo');
        const texto = document.getElementById('modalTexto');
        const boton = document.getElementById('btnConfirmarEstado');

        form.action = "{{ url('/asignaciones') }}/" + id + "/estado";

        const nombreSeguro =
            nombre && nombre.trim()
                ? nombre
                : 'este colaborador';

        if (accion === 'desactivar') {

            titulo.textContent = 'Desactivar asignación';

            texto.textContent =
                'La asignación de ' +
                nombreSeguro +
                ' dejará de utilizarse para nuevas marcaciones mientras permanezca inactiva.';

            boton.textContent = 'Desactivar';

        } else {

            titulo.textContent = 'Activar asignación';

            texto.textContent =
                'La asignación de ' +
                nombreSeguro +
                ' volverá a estar disponible según sus fechas y días configurados.';

            boton.textContent = 'Activar';
        }

        modal.classList.add('show');
    }


    function cerrarModalEstado() {
        document
            .getElementById('modalEstado')
            .classList
            .remove('show');
    }


    document
        .getElementById('modalEstado')
        ?.addEventListener('click', function (event) {

            if (event.target === this) {
                cerrarModalEstado();
            }
        });


    const successAlert =
        document.getElementById('successAlert');

    if (successAlert) {

        setTimeout(() => {

            successAlert.style.transition =
                'opacity .35s ease';

            successAlert.style.opacity =
                '0';

            setTimeout(() => {
                successAlert.remove();
            }, 350);

        }, 3500);
    }

</script>

@endpush
