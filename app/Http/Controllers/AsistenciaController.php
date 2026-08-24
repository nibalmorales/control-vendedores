<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\EstadoAsistencia;
use App\Models\PuntoVenta;
use App\Models\Vendedor;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTADO
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $usuarioActual = auth()->user();

        $fechaDesde =
            $request->input('fecha_desde');

        $fechaHasta =
            $request->input('fecha_hasta');

        $idVendedor =
            $request->input('id_vendedor');

        $idPuntoVenta =
            $request->input('id_punto_venta');

        $idEstado =
            $request->input('id_estado_asistencia');


        /*
        |--------------------------------------------------------------------------
        | CONSULTA
        |--------------------------------------------------------------------------
        */

        $asistencias = Asistencia::with([
            'estado',
            'asignacion.vendedor.usuario',
            'asignacion.puntoVenta',
            'asignacion.horario',
        ])


        /*
        |--------------------------------------------------------------------------
        | SUPERVISOR: SOLO ASISTENCIAS DE SU EQUIPO
        |--------------------------------------------------------------------------
        */

        ->when(
            (int) $usuarioActual->id_rol === 2,
            function ($query) use ($usuarioActual) {

                $query->whereHas(
                    'asignacion.vendedor',
                    function ($vendedor) use ($usuarioActual) {

                        $vendedor->where(
                            'id_supervisor',
                            $usuarioActual->id_usuario
                        );
                    }
                );
            }
        )


        /*
        |--------------------------------------------------------------------------
        | FECHA DESDE
        |--------------------------------------------------------------------------
        */

        ->when(
            $fechaDesde,
            function ($query) use ($fechaDesde) {

                $query->whereDate(
                    'fecha',
                    '>=',
                    $fechaDesde
                );
            }
        )


        /*
        |--------------------------------------------------------------------------
        | FECHA HASTA
        |--------------------------------------------------------------------------
        */

        ->when(
            $fechaHasta,
            function ($query) use ($fechaHasta) {

                $query->whereDate(
                    'fecha',
                    '<=',
                    $fechaHasta
                );
            }
        )


        /*
        |--------------------------------------------------------------------------
        | VENDEDOR
        |--------------------------------------------------------------------------
        */

        ->when(
            $idVendedor,
            function ($query) use ($idVendedor) {

                $query->whereHas(
                    'asignacion',
                    function ($subquery) use ($idVendedor) {

                        $subquery->where(
                            'id_vendedor',
                            $idVendedor
                        );
                    }
                );
            }
        )


        /*
        |--------------------------------------------------------------------------
        | PUNTO
        |--------------------------------------------------------------------------
        */

        ->when(
            $idPuntoVenta,
            function ($query) use ($idPuntoVenta) {

                $query->whereHas(
                    'asignacion',
                    function ($subquery) use ($idPuntoVenta) {

                        $subquery->where(
                            'id_punto_venta',
                            $idPuntoVenta
                        );
                    }
                );
            }
        )


        /*
        |--------------------------------------------------------------------------
        | ESTADO
        |--------------------------------------------------------------------------
        */

        ->when(
            $idEstado,
            function ($query) use ($idEstado) {

                $query->where(
                    'id_estado_asistencia',
                    $idEstado
                );
            }
        )

        ->orderByDesc('fecha')
        ->orderByDesc('hora_llegada')
        ->paginate(20)
        ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | VENDEDORES PARA FILTRO
        |--------------------------------------------------------------------------
        */

        $vendedores = Vendedor::with('usuario')
            ->where('activo', 1)

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
        | PUNTOS
        |--------------------------------------------------------------------------
        */

        $puntos = PuntoVenta::where('activo', 1)
            ->orderBy('nombre')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | ESTADOS
        |--------------------------------------------------------------------------
        */

        $estados = EstadoAsistencia::where('activo', 1)
            ->orderBy('nombre')
            ->get();


        return view(
            'asistencias.index',
            compact(
                'asistencias',
                'vendedores',
                'puntos',
                'estados',
                'fechaDesde',
                'fechaHasta',
                'idVendedor',
                'idPuntoVenta',
                'idEstado'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DETALLE
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $usuarioActual = auth()->user();


        $query = Asistencia::with([
            'estado',
            'asignacion.vendedor.usuario',
            'asignacion.puntoVenta',
            'asignacion.horario',
        ]);


        /*
        |--------------------------------------------------------------------------
        | SUPERVISOR: PROTEGER ACCESO DIRECTO POR URL
        |--------------------------------------------------------------------------
        */

        if ((int) $usuarioActual->id_rol === 2) {

            $query->whereHas(
                'asignacion.vendedor',
                function ($vendedor) use ($usuarioActual) {

                    $vendedor->where(
                        'id_supervisor',
                        $usuarioActual->id_usuario
                    );
                }
            );
        }


        $asistencia = $query
            ->findOrFail($id);


        return view(
            'asistencias.show',
            compact('asistencia')
        );
    }
}
