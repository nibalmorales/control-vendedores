<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\PuntoVenta;
use App\Models\Vendedor;

use Illuminate\Http\Request;

use App\Exports\AsistenciasExport;

use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | REPORTE DE ASISTENCIAS
    |--------------------------------------------------------------------------
    */

    public function asistencias(Request $request)
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


        /*
        |--------------------------------------------------------------------------
        | CONSULTA BASE
        |--------------------------------------------------------------------------
        */

        $query = Asistencia::with([
            'estado',
            'asignacion.vendedor.usuario',
            'asignacion.puntoVenta',
            'asignacion.horario',
        ]);


        /*
        |--------------------------------------------------------------------------
        | SUPERVISOR: SOLO SU EQUIPO
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


        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */

        $query
            ->when(
                $fechaDesde,
                function ($q) use ($fechaDesde) {

                    $q->whereDate(
                        'fecha',
                        '>=',
                        $fechaDesde
                    );
                }
            )

            ->when(
                $fechaHasta,
                function ($q) use ($fechaHasta) {

                    $q->whereDate(
                        'fecha',
                        '<=',
                        $fechaHasta
                    );
                }
            )

            ->when(
                $idVendedor,
                function ($q) use ($idVendedor) {

                    $q->whereHas(
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

            ->when(
                $idPuntoVenta,
                function ($q) use ($idPuntoVenta) {

                    $q->whereHas(
                        'asignacion',
                        function ($subquery) use ($idPuntoVenta) {

                            $subquery->where(
                                'id_punto_venta',
                                $idPuntoVenta
                            );
                        }
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | REGISTROS
        |--------------------------------------------------------------------------
        */

        $asistencias = (clone $query)
            ->orderByDesc('fecha')
            ->orderByDesc('hora_llegada')
            ->paginate(20)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | KPIs
        |--------------------------------------------------------------------------
        */

        $registros =
            (clone $query)->get();


        $total =
            $registros->count();


        $presentes = $registros
            ->filter(
                function ($asistencia) {

                    return
                        $asistencia->estado?->nombre ===
                        'PRESENTE';
                }
            )
            ->count();


        $tardes = $registros
            ->filter(
                function ($asistencia) {

                    return
                        $asistencia->estado?->nombre ===
                        'TARDE';
                }
            )
            ->count();


        $jornadasAbiertas = $registros
            ->whereNull('hora_salida')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | SALIDAS ANTICIPADAS
        |--------------------------------------------------------------------------
        */

        $salidasAnticipadas = $registros
            ->filter(
                function ($asistencia) {

                    if (
                        !$asistencia->hora_salida ||
                        !$asistencia->asignacion?->horario
                    ) {

                        return false;
                    }


                    $horaProgramada =
                        \Carbon\Carbon::parse(

                            $asistencia
                                ->fecha
                                ->format('Y-m-d')

                            . ' '

                            . $asistencia
                                ->asignacion
                                ->horario
                                ->hora_salida
                        );


                    return
                        $asistencia
                            ->hora_salida
                            ->lt($horaProgramada);
                }
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | SALIDAS FUERA DEL PUNTO
        |--------------------------------------------------------------------------
        */

        $salidasFuera = $registros
            ->filter(
                function ($asistencia) {

                    $punto =
                        $asistencia
                            ->asignacion
                            ?->puntoVenta;


                    if (
                        !$punto ||
                        $asistencia
                            ->distancia_salida_metros === null
                    ) {

                        return false;
                    }


                    return
                        $asistencia
                            ->distancia_salida_metros
                        >
                        $punto
                            ->radio_permitido_metros;
                }
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | VENDEDORES DEL FILTRO
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


        return view(
            'reportes.asistencias',
            compact(
                'asistencias',
                'vendedores',
                'puntos',
                'fechaDesde',
                'fechaHasta',
                'idVendedor',
                'idPuntoVenta',
                'total',
                'presentes',
                'tardes',
                'jornadasAbiertas',
                'salidasAnticipadas',
                'salidasFuera'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORTAR EXCEL
    |--------------------------------------------------------------------------
    */

    public function exportarAsistencias(Request $request)
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


        /*
        |--------------------------------------------------------------------------
        | SUPERVISOR
        |--------------------------------------------------------------------------
        */

        $idSupervisor = null;


        if ((int) $usuarioActual->id_rol === 2) {

            $idSupervisor =
                $usuarioActual->id_usuario;
        }


        /*
        |--------------------------------------------------------------------------
        | ARCHIVO
        |--------------------------------------------------------------------------
        */

        $nombreArchivo =
            'reporte_asistencias_' .
            now()->format('Y-m-d_His') .
            '.xlsx';


        return Excel::download(

            new AsistenciasExport(
                $fechaDesde,
                $fechaHasta,
                $idVendedor,
                $idPuntoVenta,
                $idSupervisor
            ),

            $nombreArchivo
        );
    }
}
