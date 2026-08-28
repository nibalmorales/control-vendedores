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


{{-- =========================================================
     FILTROS
========================================================= --}}

<form
    method="GET"
    action="{{ route('horarios.index') }}"
    class="filter-box"
>

    <div class="filters-grid">

        <input
            type="text"
            name="q"
            value="{{ $q }}"
            placeholder="Buscar horario..."
            autocomplete="off"
        >


        <select name="estado">

            <option value="">
                Todos los estados
            </option>

            <option
                value="1"
                @selected((string) $estado === '1')
            >
                Activos
            </option>

            <option
                value="0"
                @selected((string) $estado === '0')
            >
                Inactivos
            </option>

        </select>


        <button
            type="submit"
            class="btn-filter"
        >
            Filtrar
        </button>


        @if(
            request()->filled('q') ||
            request()->filled('estado')
        )

            <a
                href="{{ route('horarios.index') }}"
                class="btn-clear"
            >
                Limpiar
            </a>

        @endif

    </div>

</form>


{{-- =========================================================
     TABLA ESCRITORIO
========================================================= --}}

<div class="desktop-view">

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
                        <th>Acciones</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($horarios as $horario)

                        <tr
                            class="row-clickable"
                            onclick="window.location='{{ route(
                                'horarios.edit',
                                $horario->id_horario
                            ) }}'"
                        >

                            <td>

                                <strong>
                                    {{ $horario->nombre }}
                                </strong>

                            </td>


                            <td>

                                {{ \Carbon\Carbon::parse(
                                    $horario->hora_entrada
                                )->format('H:i') }}

                            </td>


                            <td>

                                {{ \Carbon\Carbon::parse(
                                    $horario->hora_salida
                                )->format('H:i') }}

                            </td>


                            <td>

                                {{ $horario->tolerancia_minutos }}
                                min

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


                            <td>

                                <div
                                    class="actions"
                                    onclick="event.stopPropagation();"
                                >

                                    <a
                                        href="{{ route(
                                            'horarios.edit',
                                            $horario->id_horario
                                        ) }}"
                                        class="btn-edit"
                                    >
                                        Editar
                                    </a>


                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'horarios.estado',
                                            $horario->id_horario
                                        ) }}"
                                        id="formEstado-{{ $horario->id_horario }}"
                                    >

                                        @csrf
                                        @method('PATCH')


                                        <button
                                            type="button"
                                            class="btn-state {{ $horario->activo
                                                ? 'btn-disable'
                                                : 'btn-enable' }}"
                                            onclick="abrirModalEstado(
                                                {{ $horario->id_horario }},
                                                '{{ $horario->activo
                                                    ? 'desactivar'
                                                    : 'activar' }}',
                                                @js($horario->nombre),
                                                false
                                            )"
                                        >

                                            {{ $horario->activo
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
                                colspan="6"
                                class="empty"
                            >
                                No hay horarios registrados con los filtros seleccionados.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


{{-- =========================================================
     VISTA MÓVIL
========================================================= --}}

<div class="mobile-view">

    @forelse($horarios as $horario)

        <div
            class="schedule-card"
            onclick="window.location='{{ route(
                'horarios.edit',
                $horario->id_horario
            ) }}'"
        >

            <div class="schedule-header">

                <div>

                    <div class="schedule-name">
                        {{ $horario->nombre }}
                    </div>

                    <div class="schedule-time">

                        {{ \Carbon\Carbon::parse(
                            $horario->hora_entrada
                        )->format('H:i') }}

                        –

                        {{ \Carbon\Carbon::parse(
                            $horario->hora_salida
                        )->format('H:i') }}

                    </div>

                </div>


                @if($horario->activo)

                    <span class="status active">
                        Activo
                    </span>

                @else

                    <span class="status inactive">
                        Inactivo
                    </span>

                @endif

            </div>


            <div class="schedule-data">

                <strong>
                    Tolerancia:
                </strong>

                {{ $horario->tolerancia_minutos }}
                minutos

            </div>


            <div
                class="mobile-actions"
                onclick="event.stopPropagation();"
            >

                <a
                    href="{{ route(
                        'horarios.edit',
                        $horario->id_horario
                    ) }}"
                    class="btn-edit"
                >
                    Editar
                </a>


                <form
                    method="POST"
                    action="{{ route(
                        'horarios.estado',
                        $horario->id_horario
                    ) }}"
                    id="formEstadoMobile-{{ $horario->id_horario }}"
                >

                    @csrf
                    @method('PATCH')


                    <button
                        type="button"
                        class="btn-state {{ $horario->activo
                            ? 'btn-disable'
                            : 'btn-enable' }}"
                        onclick="abrirModalEstado(
                            {{ $horario->id_horario }},
                            '{{ $horario->activo
                                ? 'desactivar'
                                : 'activar' }}',
                            @js($horario->nombre),
                            true
                        )"
                    >

                        {{ $horario->activo
                            ? 'Desactivar'
                            : 'Activar' }}

                    </button>

                </form>

            </div>

        </div>

    @empty

        <div class="mobile-empty">
            No hay horarios registrados con los filtros seleccionados.
        </div>

    @endforelse

</div>


@if($horarios->hasPages())

    <div class="pagination">
        {{ $horarios->links() }}
    </div>

@endif


{{-- =========================================================
     MODAL ACTIVAR / DESACTIVAR
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

    .filters-grid input:focus,
    .filters-grid select:focus {
        border-color: #2563eb;
    }

    .filters-grid select {
        min-width: 170px;
        padding: 11px 13px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #ffffff;
        color: #334155;
        font-size: 14px;
        outline: none;
    }

    .btn-filter {
        padding: 11px 16px;
        border: 0;
        border-radius: 8px;
        background: #071f3d;
        color: #ffffff;
        cursor: pointer;
    }

    .btn-clear {
        padding: 11px 15px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #475569;
        text-decoration: none;
    }

    .desktop-view {
        display: block;
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

    .actions {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .actions form {
        margin: 0;
    }

    .btn-edit,
    .btn-state {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 11px;
        border: 0;
        border-radius: 7px;
        font-size: 12px;
        font-family: inherit;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-edit {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .btn-edit:hover {
        background: #dbeafe;
    }

    .btn-disable {
        background: #fef2f2;
        color: #b91c1c;
    }

    .btn-enable {
        background: #ecfdf5;
        color: #047857;
    }

    .empty {
        padding: 35px;
        color: #64748b;
        text-align: center;
    }

    .mobile-view {
        display: none;
    }

    .schedule-card {
        margin-bottom: 12px;
        padding: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
        cursor: pointer;
    }

    .schedule-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .schedule-name {
        color: #0f172a;
        font-size: 17px;
        font-weight: 700;
    }

    .schedule-time {
        margin-top: 5px;
        color: #475569;
        font-size: 15px;
    }

    .schedule-data {
        margin-top: 12px;
        color: #475569;
        font-size: 14px;
    }

    .mobile-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 15px;
        padding-top: 14px;
        border-top: 1px solid #e5e7eb;
    }

    .mobile-actions form {
        margin: 0;
    }

    .mobile-actions .btn-edit,
    .mobile-actions .btn-state {
        width: 100%;
        box-sizing: border-box;
    }

    .mobile-empty {
        padding: 30px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
        color: #64748b;
        text-align: center;
    }

    .pagination {
        margin-top: 20px;
    }

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
        border-radius: 15px;
        background: #ffffff;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .20);
        text-align: center;
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
    }

    .modal-cancel {
        background: #f1f5f9;
        color: #475569;
    }

    .modal-confirm {
        color: #ffffff;
    }

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
            grid-template-columns: 1fr 1fr;
        }

        .filters-grid input,
        .filters-grid select {
            grid-column: 1 / -1;
            width: 100%;
            max-width: none;
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


    function abrirModalEstado(
        id,
        accion,
        nombre,
        mobile
    ) {

        formularioEstado =
            document.getElementById(
                mobile
                    ? 'formEstadoMobile-' + id
                    : 'formEstado-' + id
            );


        const modal =
            document.getElementById('modalEstado');

        const titulo =
            document.getElementById('modalTitle');

        const mensaje =
            document.getElementById('modalMessage');

        const boton =
            document.getElementById('btnConfirmarEstado');

        const icono =
            document.getElementById('modalIcon');


        if (accion === 'desactivar') {

            titulo.textContent =
                'Desactivar horario';

            mensaje.textContent =
                '¿Deseas desactivar "' +
                nombre +
                '"? Ya no estará disponible para nuevas asignaciones.';

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
                'Activar horario';

            mensaje.textContent =
                '¿Deseas activar "' +
                nombre +
                '"? Volverá a estar disponible para nuevas asignaciones.';

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


        modal.classList.add('active');
    }


    function cerrarModalEstado() {

        document
            .getElementById('modalEstado')
            .classList
            .remove('active');

        formularioEstado = null;
    }


    document
        .getElementById('btnConfirmarEstado')
        .addEventListener(
            'click',
            function () {

                if (formularioEstado) {
                    formularioEstado.submit();
                }

            }
        );


    document
        .getElementById('modalEstado')
        .addEventListener(
            'click',
            function (event) {

                if (event.target === this) {
                    cerrarModalEstado();
                }

            }
        );


    document.addEventListener(
        'keydown',
        function (event) {

            if (event.key === 'Escape') {
                cerrarModalEstado();
            }

        }
    );


    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const mensaje =
                document.getElementById('successMessage');

            if (mensaje) {

                setTimeout(
                    function () {

                        mensaje.style.transition =
                            'opacity 0.4s ease';

                        mensaje.style.opacity =
                            '0';

                        setTimeout(
                            function () {
                                mensaje.remove();
                            },
                            400
                        );

                    },
                    3000
                );

            }

        }
    );

</script>

@endpush
