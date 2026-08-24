<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Horario;
use App\Models\PuntoVenta;
use App\Models\Vendedor;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AsignacionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTADO
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $usuarioActual = auth()->user();

        $asignaciones = Asignacion::with([
            'vendedor.usuario.rol',
            'puntoVenta',
            'horario'
        ])

        /*
        |--------------------------------------------------------------------------
        | SUPERVISOR: SOLO SU EQUIPO
        |--------------------------------------------------------------------------
        */

        ->when(
            (int) $usuarioActual->id_rol === 2,
            function ($query) use ($usuarioActual) {

                $query->whereHas(
                    'vendedor',
                    function ($vendedor) use ($usuarioActual) {

                        $vendedor->where(
                            'id_supervisor',
                            $usuarioActual->id_usuario
                        );
                    }
                );
            }
        )

        ->orderByDesc('id_asignacion')
        ->paginate(15);


        return view(
            'asignaciones.index',
            compact('asignaciones')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREAR
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $usuarioActual = auth()->user();


        /*
        |--------------------------------------------------------------------------
        | COLABORADORES
        |--------------------------------------------------------------------------
        */

        $vendedores = Vendedor::with([
            'usuario.rol'
        ])
        ->where('activo', 1)

        ->whereHas(
            'usuario',
            function ($query) {
                $query->where('activo', 1);
            }
        )

        /*
        |--------------------------------------------------------------------------
        | SUPERVISOR: SOLO SUS VENDEDORES
        |--------------------------------------------------------------------------
        */

        ->when(
            (int) $usuarioActual->id_rol === 2,
            function ($query) use ($usuarioActual) {

                $query->where(
                    'id_supervisor',
                    $usuarioActual->id_usuario
                );
            }
        )

        ->orderBy('codigo_empleado')
        ->get();


        /*
        |--------------------------------------------------------------------------
        | PUNTOS DE VENTA
        |--------------------------------------------------------------------------
        */

        $puntos = PuntoVenta::where('activo', 1)

        ->when(
            (int) $usuarioActual->id_rol === 2,
            function ($query) use ($usuarioActual) {

                $query->where(
                    'id_supervisor',
                    $usuarioActual->id_usuario
                );
            }
        )

        ->orderBy('nombre')
        ->get();


        /*
        |--------------------------------------------------------------------------
        | HORARIOS
        |--------------------------------------------------------------------------
        */

        $horarios = Horario::where('activo', 1)
            ->orderBy('hora_entrada')
            ->get();


        return view(
            'asignaciones.create',
            compact(
                'vendedores',
                'puntos',
                'horarios'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $datos = $request->validate([

            'id_vendedor' => [
                'required',
                'exists:tb_vendedores,id_vendedor',
            ],

            'id_punto_venta' => [
                'nullable',
                'exists:tb_puntos_venta,id_punto_venta',
            ],

            'id_horario' => [
                'required',
                'exists:tb_horarios,id_horario',
            ],

            'fecha_inicio' => [
                'required',
                'date',
            ],

            'fecha_fin' => [
                'nullable',
                'date',
                'after_or_equal:fecha_inicio',
            ],

        ]);


        $usuarioActual = auth()->user();


        /*
        |--------------------------------------------------------------------------
        | PERSONA SELECCIONADA
        |--------------------------------------------------------------------------
        */

        $vendedor = Vendedor::with(
            'usuario.rol'
        )
        ->where(
            'id_vendedor',
            $datos['id_vendedor']
        )
        ->where('activo', 1)
        ->firstOrFail();


        if (!$vendedor->usuario || !$vendedor->usuario->activo) {

            throw ValidationException::withMessages([

                'id_vendedor' =>
                    'El colaborador seleccionado no está activo.'

            ]);
        }


        $rolPersona =
            strtoupper(
                $vendedor->usuario->rol?->nombre ?? ''
            );


        /*
        |--------------------------------------------------------------------------
        | SEGURIDAD DEL SUPERVISOR
        |--------------------------------------------------------------------------
        */

        if ((int) $usuarioActual->id_rol === 2) {

            /*
            | Solo puede operar sobre vendedores
            | pertenecientes a su equipo.
            */

            if (
                (int) $vendedor->id_supervisor !==
                (int) $usuarioActual->id_usuario
            ) {

                abort(
                    403,
                    'No tienes permiso para asignar este vendedor.'
                );
            }


            /*
            | Un supervisor no puede crear
            | jornadas para otro supervisor.
            */

            if ($rolPersona !== 'VENDEDOR') {

                abort(
                    403,
                    'No tienes permiso para crear esta asignación.'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SUPERVISOR
        |--------------------------------------------------------------------------
        |
        | Tiene horario y días.
        | NO tiene punto fijo.
        |
        |--------------------------------------------------------------------------
        */

        if ($rolPersona === 'SUPERVISOR') {

            /*
            | Solo ADMIN puede crear
            | la jornada personal del supervisor.
            */

            if ((int) $usuarioActual->id_rol !== 1) {

                abort(
                    403,
                    'Solo el administrador puede asignar jornadas a supervisores.'
                );
            }


            /*
            | Ignoramos cualquier punto recibido.
            */

            $datos['id_punto_venta'] = null;
        }


        /*
        |--------------------------------------------------------------------------
        | VENDEDOR
        |--------------------------------------------------------------------------
        */

        elseif ($rolPersona === 'VENDEDOR') {

            /*
            |--------------------------------------------------------------------------
            | PUNTO OBLIGATORIO
            |--------------------------------------------------------------------------
            */

            if (empty($datos['id_punto_venta'])) {

                throw ValidationException::withMessages([

                    'id_punto_venta' =>
                        'Debes seleccionar un punto de venta para el vendedor.'

                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | OBTENER PUNTO
            |--------------------------------------------------------------------------
            */

            $punto = PuntoVenta::where(
                'id_punto_venta',
                $datos['id_punto_venta']
            )
            ->where('activo', 1)
            ->first();


            if (!$punto) {

                throw ValidationException::withMessages([

                    'id_punto_venta' =>
                        'El punto de venta seleccionado no está disponible.'

                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | VENDEDOR DEBE TENER SUPERVISOR
            |--------------------------------------------------------------------------
            */

            if (!$vendedor->id_supervisor) {

                throw ValidationException::withMessages([

                    'id_vendedor' =>
                        'Este vendedor todavía no tiene un supervisor asignado.'

                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | EL PUNTO DEBE PERTENECER AL MISMO SUPERVISOR
            |--------------------------------------------------------------------------
            |
            | Esto también se valida para ADMIN.
            |
            | Evita:
            |
            | Vendedor Supervisor A
            |        +
            | Punto Supervisor B
            |
            |--------------------------------------------------------------------------
            */

            if (
                (int) $punto->id_supervisor !==
                (int) $vendedor->id_supervisor
            ) {

                throw ValidationException::withMessages([

                    'id_punto_venta' =>
                        'El punto seleccionado no pertenece al supervisor de este vendedor.'

                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | DOBLE SEGURIDAD PARA SUPERVISOR
            |--------------------------------------------------------------------------
            */

            if (
                (int) $usuarioActual->id_rol === 2 &&
                (int) $punto->id_supervisor !==
                (int) $usuarioActual->id_usuario
            ) {

                abort(
                    403,
                    'No tienes permiso para utilizar este punto de venta.'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ROL NO PERMITIDO
        |--------------------------------------------------------------------------
        */

        else {

            throw ValidationException::withMessages([

                'id_vendedor' =>
                    'El colaborador seleccionado no tiene un rol válido para una jornada.'

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | DÍAS
        |--------------------------------------------------------------------------
        */

        $datos['lunes'] =
            $request->boolean('lunes');

        $datos['martes'] =
            $request->boolean('martes');

        $datos['miercoles'] =
            $request->boolean('miercoles');

        $datos['jueves'] =
            $request->boolean('jueves');

        $datos['viernes'] =
            $request->boolean('viernes');

        $datos['sabado'] =
            $request->boolean('sabado');

        $datos['domingo'] =
            $request->boolean('domingo');

        $datos['activo'] =
            $request->boolean('activo');


        /*
        |--------------------------------------------------------------------------
        | VALIDAR DÍAS
        |--------------------------------------------------------------------------
        */

        if (
            !$datos['lunes'] &&
            !$datos['martes'] &&
            !$datos['miercoles'] &&
            !$datos['jueves'] &&
            !$datos['viernes'] &&
            !$datos['sabado'] &&
            !$datos['domingo']
        ) {

            return back()
                ->withInput()
                ->withErrors([

                    'dias' =>
                        'Debes seleccionar al menos un día de trabajo.'

                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | GUARDAR
        |--------------------------------------------------------------------------
        */

        Asignacion::create($datos);


        return redirect()
            ->route('asignaciones.index')
            ->with(
                'success',
                $rolPersona === 'SUPERVISOR'
                    ? 'Jornada del supervisor registrada correctamente.'
                    : 'Asignación del vendedor registrada correctamente.'
            );
    }
}
