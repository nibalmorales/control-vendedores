@extends('layouts.app')

@section('title', 'Nuevo vendedor')

@section('content')

<div style="max-width:900px;">

    <div style="margin-bottom:25px;">
        <h1 style="margin:0 0 6px;">Nuevo vendedor</h1>

        <div style="color:#6b7280;">
            Registra un nuevo vendedor en el sistema
        </div>
    </div>


    @if($errors->any())

        <div style="
            background:#fef2f2;
            color:#991b1b;
            padding:15px;
            border-radius:8px;
            margin-bottom:20px;
        ">

            <ul style="margin:0;">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    <form
        method="POST"
        action="{{ route('vendedores.store') }}"
    >

        @csrf


        <div style="
            background:white;
            padding:25px;
            border:1px solid #e5e7eb;
            border-radius:12px;
        ">


            <div style="
                display:grid;
                grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
                gap:20px;
            ">


                {{-- NOMBRE --}}

                <div>

                    <label for="nombre">
                        Nombre *
                    </label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        value="{{ old('nombre') }}"
                        required
                        maxlength="100"
                        style="
                            width:100%;
                            padding:12px;
                            margin-top:7px;
                            border:1px solid #d1d5db;
                            border-radius:8px;
                        "
                    >

                </div>


                {{-- APELLIDO --}}

                <div>

                    <label for="apellido">
                        Apellido *
                    </label>

                    <input
                        type="text"
                        id="apellido"
                        name="apellido"
                        value="{{ old('apellido') }}"
                        required
                        maxlength="100"
                        style="
                            width:100%;
                            padding:12px;
                            margin-top:7px;
                            border:1px solid #d1d5db;
                            border-radius:8px;
                        "
                    >

                </div>


                {{-- CORREO --}}

                <div>

                    <label for="correo">
                        Correo electrónico *
                    </label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        value="{{ old('correo') }}"
                        required
                        maxlength="150"
                        style="
                            width:100%;
                            padding:12px;
                            margin-top:7px;
                            border:1px solid #d1d5db;
                            border-radius:8px;
                        "
                    >

                </div>


                {{-- CÓDIGO --}}

                <div>

                    <label for="codigo_empleado">
                        Código de empleado
                    </label>

                    <input
                        type="text"
                        id="codigo_empleado"
                        name="codigo_empleado"
                        value="{{ old('codigo_empleado') }}"
                        maxlength="50"
                        style="
                            width:100%;
                            padding:12px;
                            margin-top:7px;
                            border:1px solid #d1d5db;
                            border-radius:8px;
                        "
                    >

                </div>


                {{-- TELÉFONO --}}

                <div>

                    <label for="telefono">
                        Teléfono
                    </label>

                    <input
                        type="text"
                        id="telefono"
                        name="telefono"
                        value="{{ old('telefono') }}"
                        maxlength="30"
                        style="
                            width:100%;
                            padding:12px;
                            margin-top:7px;
                            border:1px solid #d1d5db;
                            border-radius:8px;
                        "
                    >

                </div>


                {{-- DPI --}}

                <div>

                    <label for="dpi">
                        DPI
                    </label>

                    <input
                        type="text"
                        id="dpi"
                        name="dpi"
                        value="{{ old('dpi') }}"
                        maxlength="20"
                        style="
                            width:100%;
                            padding:12px;
                            margin-top:7px;
                            border:1px solid #d1d5db;
                            border-radius:8px;
                        "
                    >

                </div>


                {{-- =====================================================
                     SUPERVISOR
                ====================================================== --}}

                @if((int) auth()->user()->id_rol === 1)

                    <div>

                        <label for="id_supervisor">
                            Supervisor *
                        </label>

                        <select
                            id="id_supervisor"
                            name="id_supervisor"
                            required
                            style="
                                width:100%;
                                padding:12px;
                                margin-top:7px;
                                border:1px solid #d1d5db;
                                border-radius:8px;
                                background:white;
                            "
                        >

                            <option value="">
                                Selecciona un supervisor
                            </option>


                            @foreach($supervisores as $supervisor)

                                <option
                                    value="{{ $supervisor->id_usuario }}"
                                    {{ old('id_supervisor') == $supervisor->id_usuario ? 'selected' : '' }}
                                >

                                    {{ $supervisor->nombre }}
                                    {{ $supervisor->apellido }}

                                </option>

                            @endforeach

                        </select>


                        <div style="
                            margin-top:6px;
                            color:#6b7280;
                            font-size:12px;
                        ">
                            El vendedor quedará asociado al equipo de este supervisor.
                        </div>

                    </div>

                @else

                    {{-- SUPERVISOR LOGUEADO --}}

                    <div>

                        <label>
                            Supervisor
                        </label>

                        <div style="
                            width:100%;
                            padding:12px;
                            margin-top:7px;
                            border:1px solid #dbeafe;
                            border-radius:8px;
                            background:#eff6ff;
                            color:#1e40af;
                            box-sizing:border-box;
                        ">

                            {{ auth()->user()->nombre }}
                            {{ auth()->user()->apellido }}

                        </div>


                        <div style="
                            margin-top:6px;
                            color:#6b7280;
                            font-size:12px;
                        ">
                            El vendedor será asignado automáticamente a tu equipo.
                        </div>

                    </div>

                @endif


            </div>


            {{-- =========================================================
                 RESUMEN
            ========================================================== --}}

            <div
                id="resumenSupervisor"
                style="
                    display:none;
                    margin-top:25px;
                    padding:14px 16px;
                    border:1px solid #bfdbfe;
                    border-radius:9px;
                    background:#eff6ff;
                "
            >

                <div style="
                    color:#1e40af;
                    font-weight:700;
                    margin-bottom:4px;
                ">
                    Supervisor seleccionado
                </div>

                <div
                    id="nombreSupervisor"
                    style="
                        color:#475569;
                        font-size:13px;
                    "
                >
                </div>

            </div>


            {{-- =========================================================
                 BOTONES
            ========================================================== --}}

            <div style="
                display:flex;
                justify-content:flex-end;
                gap:10px;
                margin-top:30px;
            ">

                <a
                    href="{{ route('vendedores.index') }}"
                    style="
                        padding:11px 18px;
                        border:1px solid #d1d5db;
                        border-radius:8px;
                        text-decoration:none;
                        color:#374151;
                    "
                >
                    Cancelar
                </a>


                <button
                    type="submit"
                    style="
                        padding:11px 20px;
                        border:0;
                        border-radius:8px;
                        background:#2563eb;
                        color:white;
                        cursor:pointer;
                        font-size:15px;
                    "
                >
                    Crear vendedor
                </button>

            </div>

        </div>

    </form>

</div>

@endsection


@push('scripts')

<script>

    const supervisorSelect =
        document.getElementById('id_supervisor');

    const resumenSupervisor =
        document.getElementById('resumenSupervisor');

    const nombreSupervisor =
        document.getElementById('nombreSupervisor');


    function actualizarSupervisor()
    {
        if (!supervisorSelect) {
            return;
        }

        const opcion =
            supervisorSelect.options[
                supervisorSelect.selectedIndex
            ];


        if (!opcion || !opcion.value) {

            resumenSupervisor.style.display =
                'none';

            nombreSupervisor.textContent =
                '';

            return;
        }


        nombreSupervisor.textContent =
            'Este vendedor pertenecerá al equipo de ' +
            opcion.text.trim() +
            '.';


        resumenSupervisor.style.display =
            'block';
    }


    if (supervisorSelect) {

        supervisorSelect.addEventListener(
            'change',
            actualizarSupervisor
        );

        actualizarSupervisor();
    }

</script>

@endpush
