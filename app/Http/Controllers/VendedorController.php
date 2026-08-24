<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Vendedor;
use App\Models\TokenPassword;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

        $usuarioActual = auth()->user();


        $vendedores = Vendedor::with([
            'usuario',
            'supervisor'
        ])

        /*
        |--------------------------------------------------------------------------
        | FILTRO POR SUPERVISOR
        |--------------------------------------------------------------------------
        |
        | ADMIN:
        |   puede ver todos los vendedores.
        |
        | SUPERVISOR:
        |   únicamente puede ver los vendedores de su equipo.
        |
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
                            );
                    }
                );
            }
        )

        ->orderByDesc('id_vendedor')

        ->paginate(15)

        ->withQueryString();


        return view(
            'vendedores.index',
            compact(
                'vendedores',
                'q'
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
        |
        | El administrador podrá seleccionar a qué supervisor
        | pertenecerá el nuevo vendedor.
        |
        */

        if ((int) $usuarioActual->id_rol === 1) {

            $supervisores = Usuario::query()

                ->where('activo', 1)

                ->whereHas(
                    'rol',
                    function ($query) {

                        $query->where(
                            'nombre',
                            'SUPERVISOR'
                        );

                    }
                )

                ->orderBy('nombre')
                ->orderBy('apellido')

                ->get();
        }


        return view(
            'vendedores.create',
            compact('supervisores')
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


        $datos = $request->validate($reglas);


        /*
        |--------------------------------------------------------------------------
        | DETERMINAR SUPERVISOR
        |--------------------------------------------------------------------------
        */

        if ((int) $usuarioActual->id_rol === 2) {

            /*
            |--------------------------------------------------------------
            | SUPERVISOR CREANDO VENDEDOR
            |--------------------------------------------------------------
            |
            | El vendedor queda automáticamente bajo su supervisión.
            |
            */

            $idSupervisor =
                $usuarioActual->id_usuario;

        }

        elseif ((int) $usuarioActual->id_rol === 1) {

            /*
            |--------------------------------------------------------------
            | ADMIN CREANDO VENDEDOR
            |--------------------------------------------------------------
            |
            | Utilizamos el supervisor seleccionado en el formulario.
            |
            */

            $idSupervisor =
                (int) $datos['id_supervisor'];


            /*
            |--------------------------------------------------------------------------
            | VERIFICAR QUE REALMENTE SEA SUPERVISOR
            |--------------------------------------------------------------------------
            |
            | "exists" solo confirma que el usuario existe.
            | Aquí confirmamos además que su rol sea SUPERVISOR.
            |
            */

            $supervisor = Usuario::with('rol')

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

        }

        else {

            /*
            |--------------------------------------------------------------------------
            | OTROS ROLES
            |--------------------------------------------------------------------------
            */

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
                    DB::table('tb_roles')

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

                    /*
                    |----------------------------------------------------------
                    | RELACIÓN CON SUPERVISOR
                    |----------------------------------------------------------
                    */

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

            ->route('vendedores.index')

            ->with(
                'success',
                'Vendedor creado y asignado al supervisor correctamente.'
            )

            ->with(
                'enlace_invitacion',
                $enlace
            );
    }
}
