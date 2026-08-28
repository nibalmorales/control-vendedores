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

    public function index(Request $request)
    {
        $usuarioActual = auth()->user();

        $q = trim((string) $request->get('q', ''));
        $estado = $request->get('estado', '');

        $query = Asignacion::with([
            'vendedor.usuario.rol',
            'puntoVenta',
            'horario'
        ]);

        if ((int) $usuarioActual->id_rol === 2) {
            $query->whereHas('vendedor', function ($vendedor) use ($usuarioActual) {
                $vendedor->where(
                    'id_supervisor',
                    $usuarioActual->id_usuario
                );
            });
        }

        if ($q !== '') {
            $query->where(function ($asignacion) use ($q) {
                $asignacion
                    ->whereHas('vendedor.usuario', function ($usuario) use ($q) {
                        $usuario
                            ->where('nombre', 'like', '%' . $q . '%')
                            ->orWhere('apellido', 'like', '%' . $q . '%');
                    })
                    ->orWhereHas('vendedor', function ($vendedor) use ($q) {
                        $vendedor->where('codigo_empleado', 'like', '%' . $q . '%');
                    })
                    ->orWhereHas('puntoVenta', function ($punto) use ($q) {
                        $punto->where('nombre', 'like', '%' . $q . '%');
                    })
                    ->orWhereHas('horario', function ($horario) use ($q) {
                        $horario->where('nombre', 'like', '%' . $q . '%');
                    });
            });
        }

        if ($estado !== '' && in_array((string) $estado, ['0', '1'], true)) {
            $query->where('activo', (int) $estado);
        }

        $asignaciones = $query
            ->orderByDesc('id_asignacion')
            ->paginate(15)
            ->withQueryString();

        return view(
            'asignaciones.index',
            compact(
                'asignaciones',
                'q',
                'estado'
            )
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
        | VALIDAR HORARIO ACTIVO
        |--------------------------------------------------------------------------
        |
        | Un horario inactivo no puede utilizarse para crear
        | nuevas asignaciones.
        |
        | Las asignaciones que ya existen conservan su horario,
        | aunque posteriormente sea desactivado.
        |
        |--------------------------------------------------------------------------
        */

        $horario = Horario::where(
            'id_horario',
            $datos['id_horario']
        )
        ->where('activo', 1)
        ->first();


        if (!$horario) {

            throw ValidationException::withMessages([

                'id_horario' =>
                    'El horario seleccionado no está disponible para nuevas asignaciones.'

            ]);
        }


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


    /*
    |--------------------------------------------------------------------------
    | EDITAR
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $usuarioActual = auth()->user();

        $asignacion = Asignacion::with([
            'vendedor.usuario.rol',
            'puntoVenta',
            'horario'
        ])->findOrFail($id);

        $this->validarAccesoAsignacion(
            $asignacion,
            $usuarioActual
        );

        $vendedores = Vendedor::with([
            'usuario.rol'
        ])
        ->where('activo', 1)
        ->whereHas('usuario', function ($query) {
            $query->where('activo', 1);
        })
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
        | Solo se muestran horarios activos para cambios.
        | Si el horario actual está inactivo, se incluye únicamente
        | para conservar la asignación existente sin obligar a cambiarla.
        */
        $horarios = Horario::where(function ($query) use ($asignacion) {
                $query->where('activo', 1)
                    ->orWhere(
                        'id_horario',
                        $asignacion->id_horario
                    );
            })
            ->orderBy('hora_entrada')
            ->get();

        return view(
            'asignaciones.edit',
            compact(
                'asignacion',
                'vendedores',
                'puntos',
                'horarios'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $usuarioActual = auth()->user();

        $asignacion = Asignacion::with([
            'vendedor.usuario.rol',
            'puntoVenta',
            'horario'
        ])->findOrFail($id);

        $this->validarAccesoAsignacion(
            $asignacion,
            $usuarioActual
        );

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

        /*
        | Si se cambia de horario, el nuevo debe estar activo.
        | Si se conserva el horario actual, puede permanecer aunque
        | haya sido desactivado después de crear la asignación.
        */
        if (
            (int) $datos['id_horario'] !==
            (int) $asignacion->id_horario
        ) {
            $horarioActivo = Horario::where(
                'id_horario',
                $datos['id_horario']
            )
            ->where('activo', 1)
            ->exists();

            if (!$horarioActivo) {
                throw ValidationException::withMessages([
                    'id_horario' =>
                        'El nuevo horario seleccionado no está disponible.'
                ]);
            }
        }

        $vendedor = Vendedor::with('usuario.rol')
            ->where(
                'id_vendedor',
                $datos['id_vendedor']
            )
            ->where('activo', 1)
            ->first();

        if (
            !$vendedor ||
            !$vendedor->usuario ||
            !$vendedor->usuario->activo
        ) {
            throw ValidationException::withMessages([
                'id_vendedor' =>
                    'El colaborador seleccionado no está activo.'
            ]);
        }

        $rolPersona = strtoupper(
            $vendedor->usuario->rol?->nombre ?? ''
        );

        if ((int) $usuarioActual->id_rol === 2) {
            if (
                (int) $vendedor->id_supervisor !==
                (int) $usuarioActual->id_usuario
            ) {
                abort(
                    403,
                    'No tienes permiso para asignar este vendedor.'
                );
            }

            if ($rolPersona !== 'VENDEDOR') {
                abort(
                    403,
                    'No tienes permiso para modificar esta asignación.'
                );
            }
        }

        if ($rolPersona === 'SUPERVISOR') {
            if ((int) $usuarioActual->id_rol !== 1) {
                abort(
                    403,
                    'Solo el administrador puede modificar jornadas de supervisores.'
                );
            }

            $datos['id_punto_venta'] = null;
        } elseif ($rolPersona === 'VENDEDOR') {
            if (empty($datos['id_punto_venta'])) {
                throw ValidationException::withMessages([
                    'id_punto_venta' =>
                        'Debes seleccionar un punto de venta para el vendedor.'
                ]);
            }

            $punto = PuntoVenta::where(
                'id_punto_venta',
                $datos['id_punto_venta']
            )
            ->where('activo', 1)
            ->first();

            /*
            | Si se conserva exactamente el punto actual, permitimos
            | mantenerlo aunque haya sido desactivado posteriormente.
            */
            if (
                !$punto &&
                (int) $datos['id_punto_venta'] ===
                (int) $asignacion->id_punto_venta
            ) {
                $punto = PuntoVenta::find(
                    $datos['id_punto_venta']
                );
            }

            if (!$punto) {
                throw ValidationException::withMessages([
                    'id_punto_venta' =>
                        'El punto de venta seleccionado no está disponible.'
                ]);
            }

            if (!$vendedor->id_supervisor) {
                throw ValidationException::withMessages([
                    'id_vendedor' =>
                        'Este vendedor todavía no tiene un supervisor asignado.'
                ]);
            }

            if (
                (int) $punto->id_supervisor !==
                (int) $vendedor->id_supervisor
            ) {
                throw ValidationException::withMessages([
                    'id_punto_venta' =>
                        'El punto seleccionado no pertenece al supervisor de este vendedor.'
                ]);
            }

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
        } else {
            throw ValidationException::withMessages([
                'id_vendedor' =>
                    'El colaborador seleccionado no tiene un rol válido para una jornada.'
            ]);
        }

        $datos['lunes'] = $request->boolean('lunes');
        $datos['martes'] = $request->boolean('martes');
        $datos['miercoles'] = $request->boolean('miercoles');
        $datos['jueves'] = $request->boolean('jueves');
        $datos['viernes'] = $request->boolean('viernes');
        $datos['sabado'] = $request->boolean('sabado');
        $datos['domingo'] = $request->boolean('domingo');
        $datos['activo'] = $request->boolean('activo');

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

        $asignacion->update($datos);

        return redirect()
            ->route('asignaciones.index')
            ->with(
                'success',
                'Asignación actualizada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIVAR / DESACTIVAR
    |--------------------------------------------------------------------------
    */

    public function cambiarEstado($id)
    {
        $usuarioActual = auth()->user();

        $asignacion = Asignacion::with([
            'vendedor.usuario.rol'
        ])->findOrFail($id);

        $this->validarAccesoAsignacion(
            $asignacion,
            $usuarioActual
        );

        /*
        | Un supervisor únicamente puede administrar
        | asignaciones de vendedores de su equipo.
        */
        if (
            (int) $usuarioActual->id_rol === 2 &&
            strtoupper(
                $asignacion->vendedor?->usuario?->rol?->nombre ?? ''
            ) !== 'VENDEDOR'
        ) {
            abort(
                403,
                'No tienes permiso para cambiar el estado de esta asignación.'
            );
        }

        $asignacion->activo = !$asignacion->activo;
        $asignacion->save();

        return redirect()
            ->route('asignaciones.index')
            ->with(
                'success',
                $asignacion->activo
                    ? 'Asignación activada correctamente.'
                    : 'Asignación desactivada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDAR ACCESO
    |--------------------------------------------------------------------------
    */

    private function validarAccesoAsignacion(
        Asignacion $asignacion,
        $usuarioActual
    ): void {
        if ((int) $usuarioActual->id_rol !== 2) {
            return;
        }

        if (
            !$asignacion->vendedor ||
            (int) $asignacion->vendedor->id_supervisor !==
            (int) $usuarioActual->id_usuario
        ) {
            abort(
                403,
                'No tienes permiso para administrar esta asignación.'
            );
        }
    }

}
