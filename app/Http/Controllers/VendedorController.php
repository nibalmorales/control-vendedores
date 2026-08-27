<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Vendedor;
use App\Models\TokenPassword;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VendedorController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTADO DE VENDEDORES
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $q = trim(
            (string) $request->input('q')
        );

        $estado = $request->input('estado');

        $idSupervisor = $request->input(
            'id_supervisor'
        );

        $usuarioActual = auth()->user();


        /*
        |--------------------------------------------------------------------------
        | CONSULTA PRINCIPAL
        |--------------------------------------------------------------------------
        */

        $vendedores = Vendedor::with([
            'usuario',
            'supervisor'
        ])

        /*
        |--------------------------------------------------------------------------
        | SOLO USUARIOS CON ROL VENDEDOR
        |--------------------------------------------------------------------------
        |
        | tb_vendedores también puede contener perfiles operativos
        | utilizados por supervisores.
        |
        | Por eso esta pantalla muestra únicamente usuarios cuyo rol
        | sea VENDEDOR.
        |
        */

        ->whereHas(
            'usuario',
            function ($query) {

                $query->whereHas(
                    'rol',
                    function ($rol) {

                        $rol->where(
                            'nombre',
                            'VENDEDOR'
                        );
                    }
                );
            }
        )

        /*
        |--------------------------------------------------------------------------
        | SUPERVISOR SOLO VE SU EQUIPO
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

        /*
        |--------------------------------------------------------------------------
        | FILTRO POR ESTADO
        |--------------------------------------------------------------------------
        |
        | 1 = Activos
        | 0 = Inactivos
        | vacío = Todos
        |
        */

        ->when(
            $estado !== null &&
            $estado !== '',
            function ($query) use ($estado) {

                $query->where(
                    'activo',
                    (int) $estado
                );
            }
        )

        /*
        |--------------------------------------------------------------------------
        | FILTRO POR SUPERVISOR
        |--------------------------------------------------------------------------
        |
        | Este filtro solamente puede ser utilizado por ADMIN.
        |
        | El SUPERVISOR ya está limitado automáticamente a su equipo.
        |
        */

        ->when(
            (int) $usuarioActual->id_rol === 1 &&
            $idSupervisor !== null &&
            $idSupervisor !== '',
            function ($query) use ($idSupervisor) {

                $query->where(
                    'id_supervisor',
                    (int) $idSupervisor
                );
            }
        )

        /*
        |--------------------------------------------------------------------------
        | BUSCADOR
        |--------------------------------------------------------------------------
        */

        ->when(
            $q,
            function ($query) use ($q) {

                $query->where(
                    function ($subquery) use ($q) {

                        $subquery
                            ->where(
                                'codigo_empleado',
                                'like',
                                "%{$q}%"
                            )

                            ->orWhere(
                                'telefono',
                                'like',
                                "%{$q}%"
                            )

                            ->orWhere(
                                'dpi',
                                'like',
                                "%{$q}%"
                            )

                            ->orWhereHas(
                                'usuario',
                                function ($usuario) use ($q) {

                                    $usuario
                                        ->where(
                                            'nombre',
                                            'like',
                                            "%{$q}%"
                                        )

                                        ->orWhere(
                                            'apellido',
                                            'like',
                                            "%{$q}%"
                                        )

                                        ->orWhere(
                                            'correo',
                                            'like',
                                            "%{$q}%"
                                        );
                                }
                            )

                            /*
                            |--------------------------------------------------------------------------
                            | BUSCAR POR NOMBRE DEL SUPERVISOR
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
        )

        ->orderByDesc(
            'id_vendedor'
        )

        ->paginate(15)

        ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | LISTA DE SUPERVISORES PARA FILTRO
        |--------------------------------------------------------------------------
        |
        | Solo ADMIN necesita esta información.
        |
        */

        $supervisores = collect();


        if ((int) $usuarioActual->id_rol === 1) {

            $supervisores = Usuario::query()

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


        return view(
            'vendedores.index',
            compact(
                'vendedores',
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
        | ADMIN: CARGAR SUPERVISORES
        |--------------------------------------------------------------------------
        */

        if ((int) $usuarioActual->id_rol === 1) {

            $supervisores = Usuario::query()

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


        return view(
            'vendedores.create',
            compact(
                'supervisores'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREAR VENDEDOR
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $usuarioActual = auth()->user();


        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN
        |--------------------------------------------------------------------------
        */

        $reglas = [

            'nombre' => [
                'required',
                'string',
                'max:100'
            ],

            'apellido' => [
                'required',
                'string',
                'max:100'
            ],

            'correo' => [
                'required',
                'email',
                'max:150',
                'unique:tb_usuarios,correo'
            ],

            'codigo_empleado' => [
                'nullable',
                'string',
                'max:50',
                'unique:tb_vendedores,codigo_empleado'
            ],

            'telefono' => [
                'nullable',
                'string',
                'max:30'
            ],

            'dpi' => [
                'nullable',
                'string',
                'max:20'
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
                'exists:tb_usuarios,id_usuario'
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
            | SUPERVISOR CREANDO VENDEDOR
            |--------------------------------------------------------------------------
            */

            $idSupervisor =
                $usuarioActual->id_usuario;

        } elseif ((int) $usuarioActual->id_rol === 1) {

            /*
            |--------------------------------------------------------------------------
            | ADMIN CREANDO VENDEDOR
            |--------------------------------------------------------------------------
            */

            $idSupervisor =
                (int) $datos['id_supervisor'];


            /*
            |--------------------------------------------------------------------------
            | VALIDAR QUE SEA SUPERVISOR ACTIVO
            |--------------------------------------------------------------------------
            */

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

        } else {

            abort(
                403,
                'No tienes permiso para crear vendedores.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CREACIÓN
        |--------------------------------------------------------------------------
        */

        $resultado = DB::transaction(
            function () use (
                $datos,
                $idSupervisor
            ) {

                /*
                |--------------------------------------------------------------------------
                | BUSCAR ROL VENDEDOR
                |--------------------------------------------------------------------------
                */

                $rolVendedor =
                    DB::table(
                        'tb_roles'
                    )

                        ->where(
                            'nombre',
                            'VENDEDOR'
                        )

                        ->where(
                            'activo',
                            1
                        )

                        ->first();


                if (!$rolVendedor) {

                    throw new \Exception(
                        'No existe un rol VENDEDOR activo.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | CREAR USUARIO
                |--------------------------------------------------------------------------
                */

                $usuario = Usuario::create([

                    'id_rol' =>
                        $rolVendedor->id_rol,

                    'nombre' =>
                        $datos['nombre'],

                    'apellido' =>
                        $datos['apellido'],

                    'correo' =>
                        strtolower(
                            $datos['correo']
                        ),

                    'google_id' =>
                        null,

                    'password' =>
                        null,

                    'activo' =>
                        1,

                ]);


                /*
                |--------------------------------------------------------------------------
                | CREAR PERFIL VENDEDOR
                |--------------------------------------------------------------------------
                */

                $vendedor = Vendedor::create([

                    'id_usuario' =>
                        $usuario->id_usuario,

                    'id_supervisor' =>
                        $idSupervisor,

                    'codigo_empleado' =>
                        $datos['codigo_empleado']
                        ?? null,

                    'telefono' =>
                        $datos['telefono']
                        ?? null,

                    'dpi' =>
                        $datos['dpi']
                        ?? null,

                    'activo' =>
                        1,

                ]);


                /*
                |--------------------------------------------------------------------------
                | TOKEN DE INVITACIÓN
                |--------------------------------------------------------------------------
                */

                $tokenPlano =
                    Str::random(64);


                TokenPassword::create([

                    'id_usuario' =>
                        $usuario->id_usuario,

                    'token' =>
                        hash(
                            'sha256',
                            $tokenPlano
                        ),

                    'tipo' =>
                        'INVITACION',

                    'fecha_expiracion' =>
                        now()->addHours(24),

                    'usado' =>
                        0,

                ]);


                return [

                    'vendedor' =>
                        $vendedor,

                    'token' =>
                        $tokenPlano,

                ];
            }
        );


        /*
        |--------------------------------------------------------------------------
        | ENLACE DE INVITACIÓN
        |--------------------------------------------------------------------------
        */

        $enlace = route(
            'password.create',
            [
                'token' =>
                    $resultado['token']
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | REDIRECCIÓN
        |--------------------------------------------------------------------------
        */

        return redirect()

            ->route(
                'vendedores.index'
            )

            ->with(
                'success',
                'Vendedor creado y asignado al supervisor correctamente.'
            )

            ->with(
                'enlace_invitacion',
                $enlace
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


        $vendedor = Vendedor::with([
            'usuario',
            'supervisor'
        ])

            ->where(
                'id_vendedor',
                $id
            )

            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | VERIFICAR ROL
        |--------------------------------------------------------------------------
        */

        $this->validarRolVendedor(
            $vendedor
        );


        /*
        |--------------------------------------------------------------------------
        | CONTROL DE ACCESO
        |--------------------------------------------------------------------------
        */

        $this->validarAccesoVendedor(
            $vendedor,
            $usuarioActual
        );


        /*
        |--------------------------------------------------------------------------
        | SUPERVISORES PARA ADMIN
        |--------------------------------------------------------------------------
        */

        $supervisores = collect();


        if ((int) $usuarioActual->id_rol === 1) {

            $supervisores = Usuario::query()

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


        return view(
            'vendedores.edit',
            compact(
                'vendedor',
                'supervisores'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR VENDEDOR
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id
    ) {
        $usuarioActual = auth()->user();


        $vendedor = Vendedor::with([
            'usuario',
            'supervisor'
        ])

            ->where(
                'id_vendedor',
                $id
            )

            ->firstOrFail();


        $this->validarRolVendedor(
            $vendedor
        );


        $this->validarAccesoVendedor(
            $vendedor,
            $usuarioActual
        );


        $usuarioVendedor =
            $vendedor->usuario;


        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN
        |--------------------------------------------------------------------------
        */

        $reglas = [

            'nombre' => [
                'required',
                'string',
                'max:100'
            ],

            'apellido' => [
                'required',
                'string',
                'max:100'
            ],

            'correo' => [
                'required',
                'email',
                'max:150',

                Rule::unique(
                    'tb_usuarios',
                    'correo'
                )->ignore(
                    $usuarioVendedor->id_usuario,
                    'id_usuario'
                )
            ],

            'codigo_empleado' => [
                'nullable',
                'string',
                'max:50',

                Rule::unique(
                    'tb_vendedores',
                    'codigo_empleado'
                )->ignore(
                    $vendedor->id_vendedor,
                    'id_vendedor'
                )
            ],

            'telefono' => [
                'nullable',
                'string',
                'max:30'
            ],

            'dpi' => [
                'nullable',
                'string',
                'max:20'
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
                'exists:tb_usuarios,id_usuario'
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

        } else {

            /*
            |--------------------------------------------------------------------------
            | SUPERVISOR NO PUEDE REASIGNAR
            |--------------------------------------------------------------------------
            */

            $idSupervisor =
                $usuarioActual->id_usuario;
        }


        /*
        |--------------------------------------------------------------------------
        | ACTUALIZACIÓN
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $datos,
                $vendedor,
                $usuarioVendedor,
                $idSupervisor
            ) {

                /*
                |--------------------------------------------------------------------------
                | USUARIO
                |--------------------------------------------------------------------------
                */

                $usuarioVendedor->nombre =
                    $datos['nombre'];

                $usuarioVendedor->apellido =
                    $datos['apellido'];

                $usuarioVendedor->correo =
                    strtolower(
                        $datos['correo']
                    );

                $usuarioVendedor->save();


                /*
                |--------------------------------------------------------------------------
                | PERFIL VENDEDOR
                |--------------------------------------------------------------------------
                */

                $vendedor->id_supervisor =
                    $idSupervisor;

                $vendedor->codigo_empleado =
                    $datos['codigo_empleado']
                    ?? null;

                $vendedor->telefono =
                    $datos['telefono']
                    ?? null;

                $vendedor->dpi =
                    $datos['dpi']
                    ?? null;

                $vendedor->save();
            }
        );


        return redirect()

            ->route(
                'vendedores.index'
            )

            ->with(
                'success',
                'Vendedor actualizado correctamente.'
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


        $vendedor = Vendedor::with([
            'usuario'
        ])

            ->where(
                'id_vendedor',
                $id
            )

            ->firstOrFail();


        $this->validarRolVendedor(
            $vendedor
        );


        $this->validarAccesoVendedor(
            $vendedor,
            $usuarioActual
        );


        /*
        |--------------------------------------------------------------------------
        | NUEVO ESTADO
        |--------------------------------------------------------------------------
        */

        $nuevoEstado =
            $vendedor->activo
                ? 0
                : 1;


        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR AMBAS TABLAS
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $vendedor,
                $nuevoEstado
            ) {

                /*
                |--------------------------------------------------------------------------
                | PERFIL VENDEDOR
                |--------------------------------------------------------------------------
                */

                $vendedor->activo =
                    $nuevoEstado;

                $vendedor->save();


                /*
                |--------------------------------------------------------------------------
                | USUARIO
                |--------------------------------------------------------------------------
                */

                if ($vendedor->usuario) {

                    $vendedor->usuario->activo =
                        $nuevoEstado;

                    $vendedor->usuario->save();
                }
            }
        );


        $mensaje =
            $nuevoEstado
                ? 'Vendedor activado correctamente.'
                : 'Vendedor desactivado correctamente.';


        return redirect()

            ->route(
                'vendedores.index'
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

    private function validarAccesoVendedor(
        Vendedor $vendedor,
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
                (int) $vendedor->id_supervisor !==
                (int) $usuarioActual->id_usuario
            ) {

                abort(
                    403,
                    'No tienes permiso para administrar este vendedor.'
                );
            }


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | OTROS ROLES
        |--------------------------------------------------------------------------
        */

        abort(
            403,
            'No tienes permiso para administrar vendedores.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDAR QUE SEA VENDEDOR
    |--------------------------------------------------------------------------
    */

    private function validarRolVendedor(
        Vendedor $vendedor
    ): void {

        $vendedor->loadMissing(
            'usuario.rol'
        );


        $rol =
            strtoupper(
                $vendedor
                    ->usuario
                    ?->rol
                    ?->nombre
                    ?? ''
            );


        if ($rol !== 'VENDEDOR') {

            abort(
                404,
                'El registro solicitado no corresponde a un vendedor.'
            );
        }
    }
}
