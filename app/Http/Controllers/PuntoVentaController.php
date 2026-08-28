<?php

namespace App\Http\Controllers;

use App\Models\PuntoVenta;
use App\Models\Usuario;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PuntoVentaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTADO
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $usuarioActual = auth()->user();

        $q = trim(
            (string) $request->input('q', '')
        );

        $estado = $request->input('estado');

        $idSupervisor = $request->input('id_supervisor');


        /*
        |--------------------------------------------------------------------------
        | CONSULTA BASE
        |--------------------------------------------------------------------------
        */

        $query = PuntoVenta::with([
            'supervisor'
        ]);


        /*
        |--------------------------------------------------------------------------
        | SUPERVISOR: SOLO SUS PUNTOS
        |--------------------------------------------------------------------------
        */

        if ((int) $usuarioActual->id_rol === 2) {

            $query->where(
                'id_supervisor',
                $usuarioActual->id_usuario
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ADMIN: FILTRO POR SUPERVISOR
        |--------------------------------------------------------------------------
        */

        if (
            (int) $usuarioActual->id_rol === 1 &&
            $idSupervisor !== null &&
            $idSupervisor !== ''
        ) {

            $query->where(
                'id_supervisor',
                (int) $idSupervisor
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRO POR ESTADO
        |--------------------------------------------------------------------------
        */

        if (
            $estado !== null &&
            $estado !== ''
        ) {

            $query->where(
                'activo',
                (int) $estado
            );
        }


        /*
        |--------------------------------------------------------------------------
        | BUSCADOR
        |--------------------------------------------------------------------------
        */

        if ($q !== '') {

            $query->where(
                function ($subquery) use ($q) {

                    $subquery
                        ->where(
                            'nombre',
                            'like',
                            "%{$q}%"
                        )

                        ->orWhere(
                            'direccion',
                            'like',
                            "%{$q}%"
                        )

                        ->orWhere(
                            'departamento',
                            'like',
                            "%{$q}%"
                        )

                        ->orWhere(
                            'municipio',
                            'like',
                            "%{$q}%"
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | BUSCAR TAMBIÉN POR SUPERVISOR
                        |--------------------------------------------------------------------------
                        */

                        ->orWhereHas(
                            'supervisor',
                            function ($supervisor) use ($q) {

                                $supervisor
                                    ->where(
                                        'nombre',
                                        'like',
                                        "%{$q}%"
                                    )

                                    ->orWhere(
                                        'apellido',
                                        'like',
                                        "%{$q}%"
                                    );
                            }
                        );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | PAGINACIÓN
        |--------------------------------------------------------------------------
        */

        $puntos = $query
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | SUPERVISORES PARA FILTRO
        |--------------------------------------------------------------------------
        |
        | Solo el ADMIN necesita cargar esta lista.
        |
        */

        $supervisores = collect();


        if ((int) $usuarioActual->id_rol === 1) {

            $supervisores = $this->obtenerSupervisores();
        }


        return view(
            'puntos-venta.index',
            compact(
                'puntos',
                'q',
                'estado',
                'idSupervisor',
                'supervisores'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORMULARIO CREAR
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $usuarioActual = auth()->user();

        $supervisores = collect();


        /*
        |--------------------------------------------------------------------------
        | ADMIN PUEDE SELECCIONAR SUPERVISOR
        |--------------------------------------------------------------------------
        */

        if ((int) $usuarioActual->id_rol === 1) {

            $supervisores = $this->obtenerSupervisores();
        }


        return view(
            'puntos-venta.create',
            compact(
                'supervisores'
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
        $usuarioActual = auth()->user();


        /*
        |--------------------------------------------------------------------------
        | REGLAS
        |--------------------------------------------------------------------------
        */

        $reglas = [

            'nombre' => [
                'required',
                'string',
                'max:150',
            ],

            'direccion' => [
                'required',
                'string',
                'max:255',
            ],

            'departamento' => [
                'required',
                'string',
                'max:100',
            ],

            'municipio' => [
                'required',
                'string',
                'max:100',
            ],

            'latitud' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitud' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

            'radio_permitido_metros' => [
                'required',
                'integer',
                'min:10',
                'max:5000',
            ],

        ];


        /*
        |--------------------------------------------------------------------------
        | ADMIN DEBE SELECCIONAR SUPERVISOR
        |--------------------------------------------------------------------------
        */

        if ((int) $usuarioActual->id_rol === 1) {

            $reglas['id_supervisor'] = [
                'required',
                'integer',
                'exists:tb_usuarios,id_usuario',
            ];
        }


        $datos = $request->validate(
            $reglas
        );


        /*
        |--------------------------------------------------------------------------
        | DETERMINAR SUPERVISOR
        |--------------------------------------------------------------------------
        */

        if ((int) $usuarioActual->id_rol === 2) {

            /*
            |--------------------------------------------------------------------------
            | SUPERVISOR
            |--------------------------------------------------------------------------
            |
            | El punto queda automáticamente asignado
            | al supervisor autenticado.
            |
            */

            $idSupervisor =
                $usuarioActual->id_usuario;

        } elseif ((int) $usuarioActual->id_rol === 1) {

            /*
            |--------------------------------------------------------------------------
            | ADMIN
            |--------------------------------------------------------------------------
            */

            $idSupervisor =
                (int) $datos['id_supervisor'];


            /*
            |--------------------------------------------------------------------------
            | VALIDAR SUPERVISOR
            |--------------------------------------------------------------------------
            */

            $this->validarSupervisor(
                $idSupervisor
            );

        } else {

            abort(
                403,
                'No tienes permiso para crear puntos de venta.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CREAR
        |--------------------------------------------------------------------------
        */

        $datos['id_supervisor'] =
            $idSupervisor;

        $datos['activo'] =
            $request->boolean('activo');


        PuntoVenta::create(
            $datos
        );


        return redirect()
            ->route(
                'puntos-venta.index'
            )
            ->with(
                'success',
                'Punto de venta registrado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | FORMULARIO EDITAR
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $usuarioActual = auth()->user();


        $punto = PuntoVenta::with([
            'supervisor'
        ])
            ->where(
                'id_punto_venta',
                $id
            )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | VALIDAR ACCESO
        |--------------------------------------------------------------------------
        */

        $this->validarAcceso(
            $punto,
            $usuarioActual
        );


        /*
        |--------------------------------------------------------------------------
        | SUPERVISORES
        |--------------------------------------------------------------------------
        */

        $supervisores = collect();


        if ((int) $usuarioActual->id_rol === 1) {

            $supervisores = $this->obtenerSupervisores();
        }


        return view(
            'puntos-venta.edit',
            compact(
                'punto',
                'supervisores'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id
    ) {
        $usuarioActual = auth()->user();


        $punto = PuntoVenta::with([
            'supervisor'
        ])
            ->where(
                'id_punto_venta',
                $id
            )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | VALIDAR ACCESO
        |--------------------------------------------------------------------------
        */

        $this->validarAcceso(
            $punto,
            $usuarioActual
        );


        /*
        |--------------------------------------------------------------------------
        | REGLAS
        |--------------------------------------------------------------------------
        */

        $reglas = [

            'nombre' => [
                'required',
                'string',
                'max:150',
            ],

            'direccion' => [
                'required',
                'string',
                'max:255',
            ],

            'departamento' => [
                'required',
                'string',
                'max:100',
            ],

            'municipio' => [
                'required',
                'string',
                'max:100',
            ],

            'latitud' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitud' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

            'radio_permitido_metros' => [
                'required',
                'integer',
                'min:10',
                'max:5000',
            ],

        ];


        /*
        |--------------------------------------------------------------------------
        | ADMIN PUEDE CAMBIAR SUPERVISOR
        |--------------------------------------------------------------------------
        */

        if ((int) $usuarioActual->id_rol === 1) {

            $reglas['id_supervisor'] = [
                'required',
                'integer',
                'exists:tb_usuarios,id_usuario',
            ];
        }


        $datos = $request->validate(
            $reglas
        );


        /*
        |--------------------------------------------------------------------------
        | DETERMINAR SUPERVISOR
        |--------------------------------------------------------------------------
        */

        if ((int) $usuarioActual->id_rol === 1) {

            $idSupervisor =
                (int) $datos['id_supervisor'];


            $this->validarSupervisor(
                $idSupervisor
            );

        } else {

            /*
            |--------------------------------------------------------------------------
            | SUPERVISOR
            |--------------------------------------------------------------------------
            |
            | Nunca se toma un id_supervisor enviado desde el formulario.
            | Siempre conserva al usuario autenticado como propietario.
            |
            */

            $idSupervisor =
                $usuarioActual->id_usuario;
        }


        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $punto,
                $datos,
                $idSupervisor,
                $request
            ) {

                $punto->nombre =
                    $datos['nombre'];

                $punto->direccion =
                    $datos['direccion'];

                $punto->departamento =
                    $datos['departamento'];

                $punto->municipio =
                    $datos['municipio'];

                $punto->latitud =
                    $datos['latitud'];

                $punto->longitud =
                    $datos['longitud'];

                $punto->radio_permitido_metros =
                    $datos['radio_permitido_metros'];

                $punto->id_supervisor =
                    $idSupervisor;

                $punto->activo =
                    $request->boolean('activo');

                $punto->save();
            }
        );


        return redirect()
            ->route(
                'puntos-venta.index'
            )
            ->with(
                'success',
                'Punto de venta actualizado correctamente.'
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


        $punto = PuntoVenta::query()
            ->where(
                'id_punto_venta',
                $id
            )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | VALIDAR ACCESO
        |--------------------------------------------------------------------------
        */

        $this->validarAcceso(
            $punto,
            $usuarioActual
        );


        /*
        |--------------------------------------------------------------------------
        | CAMBIAR ESTADO
        |--------------------------------------------------------------------------
        */

        $nuevoEstado =
            $punto->activo
                ? 0
                : 1;


        $punto->activo =
            $nuevoEstado;

        $punto->save();


        $mensaje =
            $nuevoEstado
                ? 'Punto de venta activado correctamente.'
                : 'Punto de venta desactivado correctamente.';


        return redirect()
            ->route(
                'puntos-venta.index'
            )
            ->with(
                'success',
                $mensaje
            );
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDAR ACCESO
    |--------------------------------------------------------------------------
    */

    private function validarAcceso(
        PuntoVenta $punto,
        Usuario $usuarioActual
    ): void {

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        if ((int) $usuarioActual->id_rol === 1) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | SUPERVISOR
        |--------------------------------------------------------------------------
        */

        if ((int) $usuarioActual->id_rol === 2) {

            if (
                (int) $punto->id_supervisor !==
                (int) $usuarioActual->id_usuario
            ) {

                abort(
                    403,
                    'No tienes permiso para administrar este punto de venta.'
                );
            }


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | OTROS
        |--------------------------------------------------------------------------
        */

        abort(
            403,
            'No tienes permiso para administrar puntos de venta.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDAR SUPERVISOR
    |--------------------------------------------------------------------------
    */

    private function validarSupervisor(
        int $idSupervisor
    ): Usuario {

        $supervisor = Usuario::with(
            'rol'
        )
            ->where(
                'id_usuario',
                $idSupervisor
            )
            ->where(
                'activo',
                1
            )
            ->first();


        if (
            !$supervisor ||
            strtoupper(
                $supervisor->rol?->nombre ?? ''
            ) !== 'SUPERVISOR'
        ) {

            throw ValidationException::withMessages([

                'id_supervisor' =>
                    'El usuario seleccionado no es un supervisor válido.'

            ]);
        }


        return $supervisor;
    }


    /*
    |--------------------------------------------------------------------------
    | OBTENER SUPERVISORES
    |--------------------------------------------------------------------------
    */

    private function obtenerSupervisores()
    {
        return Usuario::query()
            ->where(
                'activo',
                1
            )
            ->whereHas(
                'rol',
                function ($query) {

                    $query->where(
                        'nombre',
                        'SUPERVISOR'
                    );
                }
            )
            ->orderBy(
                'nombre'
            )
            ->orderBy(
                'apellido'
            )
            ->get();
    }
}
