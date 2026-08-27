@extends('layouts.app')

@section('title', 'Editar vendedor')

@section('content')

<style>

    .form-page {
        max-width: 850px;
    }

    .form-header {
        margin-bottom: 25px;
    }

    .form-header h1 {
        margin: 0 0 6px;
        font-size: 32px;
        color: #111827;
    }

    .form-subtitle {
        color: #6b7280;
    }

    .form-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 25px;
    }

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
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }

    .form-control {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 11px 12px;
        font-size: 15px;
        background: #ffffff;
        color: #111827;
    }

    .form-control:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
    }

    .form-control:disabled {
        background: #f3f4f6;
        color: #6b7280;
        cursor: not-allowed;
    }

    .error {
        font-size: 13px;
        color: #b91c1c;
    }

    .info-supervisor {
        padding: 12px 14px;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        border-radius: 8px;
        color: #1e40af;
        font-size: 14px;
    }

    .acciones-form {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .btn {
        border: 0;
        border-radius: 8px;
        padding: 11px 18px;
        font-size: 15px;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
    }

    .btn-cancelar {
        background: #f3f4f6;
        color: #374151;
    }

    .btn-cancelar:hover {
        background: #e5e7eb;
    }

    .btn-guardar {
        background: #2563eb;
        color: #ffffff;
    }

    .btn-guardar:hover {
        background: #1d4ed8;
    }


    @media(max-width: 768px) {

        .form-header h1 {
            font-size: 27px;
        }

        .form-card {
            padding: 18px;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full {
            grid-column: auto;
        }

        .acciones-form {
            flex-direction: column-reverse;
        }

        .acciones-form .btn {
            width: 100%;
            text-align: center;
            box-sizing: border-box;
        }
    }

</style>


<div class="form-page">

    <div class="form-header">

        <h1>
            Editar vendedor
        </h1>

        <div class="form-subtitle">

            Actualiza la información del vendedor

        </div>

    </div>


    <div class="form-card">

        <form
            method="POST"
            action="{{ route(
                'vendedores.update',
                $vendedor->id_vendedor
            ) }}"
        >

            @csrf

            @method('PUT')


            <div class="form-grid">


                {{-- NOMBRE --}}

                <div class="form-group">

                    <label for="nombre">
                        Nombre
                    </label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        class="form-control"
                        value="{{ old(
                            'nombre',
                            $vendedor->usuario->nombre
                        ) }}"
                        maxlength="100"
                        required
                    >

                    @error('nombre')

                        <div class="error">
                            {{ $message }}
                        </div>

                    @enderror

                </div>



                {{-- APELLIDO --}}

                <div class="form-group">

                    <label for="apellido">
                        Apellido
                    </label>

                    <input
                        type="text"
                        id="apellido"
                        name="apellido"
                        class="form-control"
                        value="{{ old(
                            'apellido',
                            $vendedor->usuario->apellido
                        ) }}"
                        maxlength="100"
                        required
                    >

                    @error('apellido')

                        <div class="error">
                            {{ $message }}
                        </div>

                    @enderror

                </div>



                {{-- CORREO --}}

                <div class="form-group full">

                    <label for="correo">
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        class="form-control"
                        value="{{ old(
                            'correo',
                            $vendedor->usuario->correo
                        ) }}"
                        maxlength="150"
                        required
                    >

                    @error('correo')

                        <div class="error">
                            {{ $message }}
                        </div>

                    @enderror

                </div>



                {{-- CÓDIGO --}}

                <div class="form-group">

                    <label for="codigo_empleado">
                        Código de empleado
                    </label>

                    <input
                        type="text"
                        id="codigo_empleado"
                        name="codigo_empleado"
                        class="form-control"
                        value="{{ old(
                            'codigo_empleado',
                            $vendedor->codigo_empleado
                        ) }}"
                        maxlength="50"
                    >

                    @error('codigo_empleado')

                        <div class="error">
                            {{ $message }}
                        </div>

                    @enderror

                </div>



                {{-- TELÉFONO --}}

                <div class="form-group">

                    <label for="telefono">
                        Teléfono
                    </label>

                    <input
                        type="text"
                        id="telefono"
                        name="telefono"
                        class="form-control"
                        value="{{ old(
                            'telefono',
                            $vendedor->telefono
                        ) }}"
                        maxlength="30"
                    >

                    @error('telefono')

                        <div class="error">
                            {{ $message }}
                        </div>

                    @enderror

                </div>



                {{-- DPI --}}

                <div class="form-group full">

                    <label for="dpi">
                        DPI
                    </label>

                    <input
                        type="text"
                        id="dpi"
                        name="dpi"
                        class="form-control"
                        value="{{ old(
                            'dpi',
                            $vendedor->dpi
                        ) }}"
                        maxlength="20"
                    >

                    @error('dpi')

                        <div class="error">
                            {{ $message }}
                        </div>

                    @enderror

                </div>



                {{-- SUPERVISOR --}}

                @if((int) auth()->user()->id_rol === 1)

                    <div class="form-group full">

                        <label for="id_supervisor">
                            Supervisor
                        </label>

                        <select
                            id="id_supervisor"
                            name="id_supervisor"
                            class="form-control"
                            required
                        >

                            <option value="">
                                Selecciona un supervisor
                            </option>


                            @foreach($supervisores as $supervisor)

                                <option
                                    value="{{ $supervisor->id_usuario }}"
                                    @selected(
                                        (int) old(
                                            'id_supervisor',
                                            $vendedor->id_supervisor
                                        ) ===
                                        (int) $supervisor->id_usuario
                                    )
                                >

                                    {{ $supervisor->nombre }}

                                    {{ $supervisor->apellido }}

                                </option>

                            @endforeach

                        </select>


                        @error('id_supervisor')

                            <div class="error">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                @else

                    <div class="form-group full">

                        <label>
                            Supervisor
                        </label>

                        <div class="info-supervisor">

                            Este vendedor pertenece a tu equipo.

                            La asignación de supervisor solo puede ser
                            modificada por un administrador.

                        </div>

                    </div>

                @endif

            </div>


            <div class="acciones-form">

                <a
                    href="{{ route('vendedores.index') }}"
                    class="btn btn-cancelar"
                >
                    Cancelar
                </a>


                <button
                    type="submit"
                    class="btn btn-guardar"
                >
                    Guardar cambios
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
