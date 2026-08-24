<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Asistencia;
use App\Models\PuntoVenta;
use App\Models\Vendedor;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $ahora = now();
        $hoy = $ahora->toDateString();

        /*
        |--------------------------------------------------------------------------
        | DÍA ACTUAL
        |--------------------------------------------------------------------------
        */

        $diaActual = match ($ahora->dayOfWeekIso) {
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            7 => 'domingo',
        };


        /*
        |--------------------------------------------------------------------------
        | KPI GENERALES
        |--------------------------------------------------------------------------
        */

        $vendedoresActivos = Vendedor::where('activo', 1)
            ->count();

        $puntosActivos = PuntoVenta::where('activo', 1)
            ->count();


        /*
        |--------------------------------------------------------------------------
        | ASISTENCIAS DE HOY
        |--------------------------------------------------------------------------
        */

        $asistenciasHoy = Asistencia::with([
            'estado',
            'asignacion.vendedor.usuario',
            'asignacion.puntoVenta',
            'asignacion.horario',
        ])
        ->whereDate('fecha', $hoy)
        ->orderByDesc('hora_llegada')
        ->get();


        $totalAsistenciasHoy = $asistenciasHoy->count();


        $presentes = $asistenciasHoy
            ->filter(function ($asistencia) {
                return $asistencia->estado?->nombre === 'PRESENTE';
            })
            ->count();


        $tardes = $asistenciasHoy
            ->filter(function ($asistencia) {
                return $asistencia->estado?->nombre === 'TARDE';
            })
            ->count();


        $jornadasAbiertas = $asistenciasHoy
            ->whereNull('hora_salida')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | SALIDAS ANTICIPADAS
        |--------------------------------------------------------------------------
        */

        $salidasAnticipadas = $asistenciasHoy
            ->filter(function ($asistencia) {

                if (
                    !$asistencia->hora_salida ||
                    !$asistencia->asignacion?->horario
                ) {
                    return false;
                }

                $horaProgramada = Carbon::parse(
                    $asistencia->fecha->format('Y-m-d')
                    . ' '
                    . $asistencia->asignacion->horario->hora_salida
                );

                return $asistencia->hora_salida
                    ->lt($horaProgramada);
            })
            ->count();


        /*
        |--------------------------------------------------------------------------
        | SALIDAS FUERA DEL PUNTO
        |--------------------------------------------------------------------------
        */

        $salidasFuera = $asistenciasHoy
            ->filter(function ($asistencia) {

                $punto = $asistencia->asignacion?->puntoVenta;

                if (
                    !$punto ||
                    $asistencia->distancia_salida_metros === null
                ) {
                    return false;
                }

                return
                    $asistencia->distancia_salida_metros >
                    $punto->radio_permitido_metros;
            })
            ->count();


        /*
        |--------------------------------------------------------------------------
        | ASIGNACIONES QUE DEBEN TRABAJAR HOY
        |--------------------------------------------------------------------------
        */

        $asignacionesHoy = Asignacion::with([
            'vendedor.usuario',
            'puntoVenta',
            'horario',
        ])
        ->where('activo', 1)
        ->where($diaActual, 1)
        ->where('fecha_inicio', '<=', $hoy)
        ->where(function ($query) use ($hoy) {

            $query
                ->whereNull('fecha_fin')
                ->orWhere('fecha_fin', '>=', $hoy);

        })
        ->whereHas('vendedor', function ($query) {

            $query->where('activo', 1);

        })
        ->whereHas('puntoVenta', function ($query) {

            $query->where('activo', 1);

        })
        ->whereHas('horario', function ($query) {

            $query->where('activo', 1);

        })
        ->orderBy('id_vendedor')
        ->get();


        /*
        |--------------------------------------------------------------------------
        | IDS DE ASIGNACIONES QUE YA REGISTRARON
        |--------------------------------------------------------------------------
        */

        $idsConAsistencia = $asistenciasHoy
            ->pluck('id_asignacion')
            ->filter()
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | COBERTURA
        |--------------------------------------------------------------------------
        */

        $totalAsignadosHoy = $asignacionesHoy->count();


        $asignacionesConRegistro = $asignacionesHoy
            ->filter(function ($asignacion) use ($idsConAsistencia) {

                return $idsConAsistencia
                    ->contains($asignacion->id_asignacion);

            });


        $totalRegistradosHoy =
            $asignacionesConRegistro->count();


        $pendientesHoy = $asignacionesHoy
            ->reject(function ($asignacion) use ($idsConAsistencia) {

                return $idsConAsistencia
                    ->contains($asignacion->id_asignacion);

            })
            ->values();


        $totalPendientesHoy =
            $pendientesHoy->count();


        $porcentajeCobertura =
            $totalAsignadosHoy > 0
                ? round(
                    ($totalRegistradosHoy / $totalAsignadosHoy) * 100
                )
                : 0;


        /*
        |--------------------------------------------------------------------------
        | DETALLE DE COBERTURA
        |--------------------------------------------------------------------------
        */

        $coberturaHoy = $asignacionesHoy
            ->map(function ($asignacion) use (
                $asistenciasHoy
            ) {

                $asistencia = $asistenciasHoy
                    ->firstWhere(
                        'id_asignacion',
                        $asignacion->id_asignacion
                    );

                return [
                    'asignacion' => $asignacion,
                    'asistencia' => $asistencia,
                ];

            });

/*
|--------------------------------------------------------------------------
| MAPA DE VENDEDORES
|--------------------------------------------------------------------------
|
| Si el vendedor ya registró salida:
|     usamos las coordenadas de salida.
|
| Si tiene jornada abierta:
|     usamos las coordenadas de llegada.
|
| Si todavía no registra asistencia:
|     mostramos solamente el punto asignado.
|
*/

$mapaVendedores = $asignacionesHoy
    ->map(function ($asignacion) use ($asistenciasHoy) {

        $asistencia = $asistenciasHoy
            ->firstWhere(
                'id_asignacion',
                $asignacion->id_asignacion
            );

        $usuario = $asignacion
            ->vendedor?->usuario;

        $punto = $asignacion
            ->puntoVenta;

        /*
        |--------------------------------------------------------------------------
        | VALORES INICIALES
        |--------------------------------------------------------------------------
        */

        $latitud = null;
        $longitud = null;

        $tipoUbicacion = 'PUNTO_ASIGNADO';
        $ubicacionReal = false;

        $horaUbicacion = null;
        $distancia = null;


        /*
        |--------------------------------------------------------------------------
        | ÚLTIMA UBICACIÓN REGISTRADA
        |--------------------------------------------------------------------------
        */

        if (
            $asistencia &&
            $asistencia->hora_salida &&
            $asistencia->latitud_salida !== null &&
            $asistencia->longitud_salida !== null
        ) {

            $latitud =
                (float) $asistencia->latitud_salida;

            $longitud =
                (float) $asistencia->longitud_salida;

            $tipoUbicacion =
                'SALIDA';

            $ubicacionReal =
                true;

            $horaUbicacion =
                $asistencia->hora_salida->format('H:i:s');

            $distancia =
                $asistencia->distancia_salida_metros;

        } elseif (
            $asistencia &&
            $asistencia->hora_llegada &&
            $asistencia->latitud_llegada !== null &&
            $asistencia->longitud_llegada !== null
        ) {

            $latitud =
                (float) $asistencia->latitud_llegada;

            $longitud =
                (float) $asistencia->longitud_llegada;

            $tipoUbicacion =
                'LLEGADA';

            $ubicacionReal =
                true;

            $horaUbicacion =
                $asistencia->hora_llegada->format('H:i:s');

            $distancia =
                $asistencia->distancia_llegada_metros;

        } elseif (
            $punto &&
            $punto->latitud !== null &&
            $punto->longitud !== null
        ) {

            /*
             * No representa la ubicación real del vendedor.
             * Es únicamente el punto donde está asignado.
             */

            $latitud =
                (float) $punto->latitud;

            $longitud =
                (float) $punto->longitud;
        }


        /*
        |--------------------------------------------------------------------------
        | ESTADO DE JORNADA
        |--------------------------------------------------------------------------
        */

        if (!$asistencia) {

            $estadoJornada =
                'PENDIENTE';

        } elseif ($asistencia->hora_salida) {

            $estadoJornada =
                'FINALIZADA';

        } else {

            $estadoJornada =
                'EN_JORNADA';
        }


        /*
        |--------------------------------------------------------------------------
        | ESTADO DE ASISTENCIA
        |--------------------------------------------------------------------------
        */

            $estadoAsistencia =
                $asistencia?->estado?->nombre
                ?? 'PENDIENTE';


            return [

                'id_asignacion' =>
                    $asignacion->id_asignacion,

                'vendedor' =>
                    trim(
                        ($usuario?->nombre ?? 'Vendedor')
                        . ' '
                        . ($usuario?->apellido ?? '')
                    ),

                'codigo' =>
                    $asignacion
                        ->vendedor?->codigo_empleado
                    ?? '-',

                'punto' =>
                    $punto?->nombre
                    ?? 'Sin punto',

                'direccion' =>
                    $punto?->direccion
                    ?? '',

                'latitud' =>
                    $latitud,

                'longitud' =>
                    $longitud,

                'tipo_ubicacion' =>
                    $tipoUbicacion,

                'ubicacion_real' =>
                    $ubicacionReal,

                'hora_ubicacion' =>
                    $horaUbicacion,

                'estado' =>
                    $estadoAsistencia,

                'estado_jornada' =>
                    $estadoJornada,

                'hora_llegada' =>
                    $asistencia?->hora_llegada
                        ?->format('H:i:s'),

                'hora_salida' =>
                    $asistencia?->hora_salida
                        ?->format('H:i:s'),

                'distancia' =>
                    $distancia !== null
                        ? round((float) $distancia, 0)
                        : null,

                'radio_permitido' =>
                    $punto?->radio_permitido_metros,

            ];

        })
        ->filter(function ($item) {

            return
                $item['latitud'] !== null &&
                $item['longitud'] !== null;

        })
        ->values();

        /*
        |--------------------------------------------------------------------------
        | ACTIVIDAD RECIENTE
        |--------------------------------------------------------------------------
        */

        $actividadHoy = $asistenciasHoy->take(10);


        return view(
            'dashboard',
            compact(
        'vendedoresActivos',
        'puntosActivos',
        'totalAsistenciasHoy',
        'presentes',
        'tardes',
        'jornadasAbiertas',
        'salidasAnticipadas',
        'salidasFuera',
        'actividadHoy',
        'totalAsignadosHoy',
        'totalRegistradosHoy',
        'totalPendientesHoy',
        'porcentajeCobertura',
        'pendientesHoy',
        'coberturaHoy',
        'mapaVendedores'
            )
        );
    }
}
