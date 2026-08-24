<?php

namespace App\Exports;

use App\Models\Asistencia;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AsistenciasExport implements
    FromArray,
    WithHeadings,
    ShouldAutoSize
{
    protected $fechaDesde;
    protected $fechaHasta;
    protected $idVendedor;
    protected $idPuntoVenta;
    protected $idSupervisor;


    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct(
        $fechaDesde = null,
        $fechaHasta = null,
        $idVendedor = null,
        $idPuntoVenta = null,
        $idSupervisor = null
    ) {
        $this->fechaDesde = $fechaDesde;
        $this->fechaHasta = $fechaHasta;
        $this->idVendedor = $idVendedor;
        $this->idPuntoVenta = $idPuntoVenta;
        $this->idSupervisor = $idSupervisor;
    }


    /*
    |--------------------------------------------------------------------------
    | DATOS
    |--------------------------------------------------------------------------
    */

    public function array(): array
    {
        $query = Asistencia::with([
            'estado',
            'asignacion.vendedor.usuario',
            'asignacion.puntoVenta',
            'asignacion.horario',
        ]);


        /*
        |--------------------------------------------------------------------------
        | SEGURIDAD POR SUPERVISOR
        |--------------------------------------------------------------------------
        |
        | Si idSupervisor tiene valor, únicamente se exportarán
        | las asistencias de vendedores pertenecientes a ese supervisor.
        |
        | Para ADMIN llegará NULL y no se aplicará este filtro.
        |
        */

        $query->when(
            $this->idSupervisor,
            function ($query) {

                $query->whereHas(
                    'asignacion.vendedor',
                    function ($vendedor) {

                        $vendedor->where(
                            'id_supervisor',
                            $this->idSupervisor
                        );
                    }
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | FECHA DESDE
        |--------------------------------------------------------------------------
        */

        $query->when(
            $this->fechaDesde,
            function ($query) {

                $query->whereDate(
                    'fecha',
                    '>=',
                    $this->fechaDesde
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | FECHA HASTA
        |--------------------------------------------------------------------------
        */

        $query->when(
            $this->fechaHasta,
            function ($query) {

                $query->whereDate(
                    'fecha',
                    '<=',
                    $this->fechaHasta
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | VENDEDOR
        |--------------------------------------------------------------------------
        */

        $query->when(
            $this->idVendedor,
            function ($query) {

                $query->whereHas(
                    'asignacion',
                    function ($subquery) {

                        $subquery->where(
                            'id_vendedor',
                            $this->idVendedor
                        );
                    }
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | PUNTO DE VENTA
        |--------------------------------------------------------------------------
        */

        $query->when(
            $this->idPuntoVenta,
            function ($query) {

                $query->whereHas(
                    'asignacion',
                    function ($subquery) {

                        $subquery->where(
                            'id_punto_venta',
                            $this->idPuntoVenta
                        );
                    }
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | OBTENER ASISTENCIAS
        |--------------------------------------------------------------------------
        */

        $asistencias = $query
            ->orderBy('fecha')
            ->orderBy('hora_llegada')
            ->get();


        $rows = [];


        /*
        |--------------------------------------------------------------------------
        | CONSTRUIR FILAS
        |--------------------------------------------------------------------------
        */

        foreach ($asistencias as $asistencia) {

            $asignacion =
                $asistencia->asignacion;

            $vendedor =
                $asignacion?->vendedor;

            $usuario =
                $vendedor?->usuario;

            $punto =
                $asignacion?->puntoVenta;

            $horario =
                $asignacion?->horario;


            /*
            |--------------------------------------------------------------------------
            | SALIDA ANTICIPADA
            |--------------------------------------------------------------------------
            */

            $salidaAnticipada = false;


            if (
                $asistencia->hora_salida &&
                $horario
            ) {

                $horaProgramada = Carbon::parse(
                    $asistencia->fecha->format('Y-m-d')
                    . ' '
                    . $horario->hora_salida
                );


                $salidaAnticipada =
                    $asistencia
                        ->hora_salida
                        ->lt($horaProgramada);
            }


            /*
            |--------------------------------------------------------------------------
            | SALIDA FUERA DEL PUNTO
            |--------------------------------------------------------------------------
            */

            $salidaFuera = false;


            if (
                $asistencia->distancia_salida_metros !== null &&
                $punto
            ) {

                $salidaFuera =
                    $asistencia->distancia_salida_metros >
                    $punto->radio_permitido_metros;
            }


            /*
            |--------------------------------------------------------------------------
            | INCIDENCIAS
            |--------------------------------------------------------------------------
            */

            $incidencias = [];


            if ($salidaAnticipada) {

                $incidencias[] =
                    'Salida anticipada';
            }


            if ($salidaFuera) {

                $incidencias[] =
                    'Salida fuera del punto';
            }


            if (!$asistencia->hora_salida) {

                $incidencias[] =
                    'Jornada abierta';
            }


            if (empty($incidencias)) {

                $incidencias[] =
                    'Normal';
            }


            /*
            |--------------------------------------------------------------------------
            | FILA EXCEL
            |--------------------------------------------------------------------------
            */

            $rows[] = [

                /*
                | Fecha
                */

                $asistencia->fecha
                    ? $asistencia->fecha->format('d/m/Y')
                    : '',


                /*
                | Vendedor
                */

                trim(
                    ($usuario?->nombre ?? '')
                    . ' '
                    . ($usuario?->apellido ?? '')
                ),


                /*
                | Código
                */

                $vendedor?->codigo_empleado ?? '',


                /*
                | Punto
                */

                $punto?->nombre ?? '',


                /*
                | Entrada programada
                */

                $horario
                    ? Carbon::parse(
                        $horario->hora_entrada
                    )->format('H:i')
                    : '',


                /*
                | Salida programada
                */

                $horario
                    ? Carbon::parse(
                        $horario->hora_salida
                    )->format('H:i')
                    : '',


                /*
                | Entrada registrada
                */

                $asistencia->hora_llegada
                    ? $asistencia
                        ->hora_llegada
                        ->format('H:i:s')
                    : '',


                /*
                | Distancia entrada
                */

                $asistencia
                    ->distancia_llegada_metros,


                /*
                | Precisión entrada
                */

                $asistencia
                    ->precision_llegada_metros,


                /*
                | Salida registrada
                */

                $asistencia->hora_salida
                    ? $asistencia
                        ->hora_salida
                        ->format('H:i:s')
                    : '',


                /*
                | Distancia salida
                */

                $asistencia
                    ->distancia_salida_metros,


                /*
                | Precisión salida
                */

                $asistencia
                    ->precision_salida_metros,


                /*
                | Estado
                */

                str_replace(
                    '_',
                    ' ',
                    $asistencia
                        ->estado
                        ?->nombre ?? ''
                ),


                /*
                | Incidencias
                */

                implode(
                    ' / ',
                    $incidencias
                ),


                /*
                | Observaciones
                */

                $asistencia
                    ->observaciones ?? '',
            ];
        }


        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | ENCABEZADOS
    |--------------------------------------------------------------------------
    */

    public function headings(): array
    {
        return [
            'Fecha',
            'Vendedor',
            'Código',
            'Punto de venta',
            'Entrada programada',
            'Salida programada',
            'Entrada registrada',
            'Distancia entrada (m)',
            'Precisión entrada (m)',
            'Salida registrada',
            'Distancia salida (m)',
            'Precisión salida (m)',
            'Estado',
            'Incidencias',
            'Observaciones',
        ];
    }
}
