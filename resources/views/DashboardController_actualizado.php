<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Asistencia;
use App\Models\PuntoVenta;
use App\Models\UbicacionSupervisor;
use App\Models\Vendedor;
use App\Models\VisitaSupervisor;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $ahora = now();
        $hoy = $ahora->toDateString();

        $diaActual = match ($ahora->dayOfWeekIso) {
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            7 => 'domingo',
        };

        $vendedoresActivos = Vendedor::where('activo', 1)->count();
        $puntosActivos = PuntoVenta::where('activo', 1)->count();

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
            ->filter(fn ($a) => $a->estado?->nombre === 'PRESENTE')
            ->count();

        $tardes = $asistenciasHoy
            ->filter(fn ($a) => $a->estado?->nombre === 'TARDE')
            ->count();

        $jornadasAbiertas = $asistenciasHoy
            ->whereNull('hora_salida')
            ->count();

        $salidasAnticipadas = $asistenciasHoy
            ->filter(function ($asistencia) {
                if (!$asistencia->hora_salida || !$asistencia->asignacion?->horario) {
                    return false;
                }

                $horaProgramada = Carbon::parse(
                    $asistencia->fecha->format('Y-m-d')
                    . ' '
                    . $asistencia->asignacion->horario->hora_salida
                );

                return $asistencia->hora_salida->lt($horaProgramada);
            })
            ->count();

        $salidasFuera = $asistenciasHoy
            ->filter(function ($asistencia) {
                $punto = $asistencia->asignacion?->puntoVenta;

                if (!$punto || $asistencia->distancia_salida_metros === null) {
                    return false;
                }

                return $asistencia->distancia_salida_metros >
                    $punto->radio_permitido_metros;
            })
            ->count();

        $asignacionesHoy = Asignacion::with([
            'vendedor.usuario',
            'puntoVenta',
            'horario',
        ])
        ->where('activo', 1)
        ->where($diaActual, 1)
        ->where('fecha_inicio', '<=', $hoy)
        ->where(function ($query) use ($hoy) {
            $query->whereNull('fecha_fin')
                ->orWhere('fecha_fin', '>=', $hoy);
        })
        ->whereHas('vendedor', fn ($q) => $q->where('activo', 1))
        ->whereHas('puntoVenta', fn ($q) => $q->where('activo', 1))
        ->whereHas('horario', fn ($q) => $q->where('activo', 1))
        ->orderBy('id_vendedor')
        ->get();

        $idsConAsistencia = $asistenciasHoy
            ->pluck('id_asignacion')
            ->filter()
            ->unique()
            ->values();

        $totalAsignadosHoy = $asignacionesHoy->count();

        $totalRegistradosHoy = $asignacionesHoy
            ->filter(fn ($a) => $idsConAsistencia->contains($a->id_asignacion))
            ->count();

        $pendientesHoy = $asignacionesHoy
            ->reject(fn ($a) => $idsConAsistencia->contains($a->id_asignacion))
            ->values();

        $totalPendientesHoy = $pendientesHoy->count();

        $porcentajeCobertura = $totalAsignadosHoy > 0
            ? round(($totalRegistradosHoy / $totalAsignadosHoy) * 100)
            : 0;

        $coberturaHoy = $asignacionesHoy
            ->map(function ($asignacion) use ($asistenciasHoy) {
                return [
                    'asignacion' => $asignacion,
                    'asistencia' => $asistenciasHoy->firstWhere(
                        'id_asignacion',
                        $asignacion->id_asignacion
                    ),
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | MAPA DE VENDEDORES
        |--------------------------------------------------------------------------
        */

        $mapaVendedores = $asignacionesHoy
            ->map(function ($asignacion) use ($asistenciasHoy) {
                $asistencia = $asistenciasHoy->firstWhere(
                    'id_asignacion',
                    $asignacion->id_asignacion
                );

                $usuario = $asignacion->vendedor?->usuario;
                $punto = $asignacion->puntoVenta;

                $latitud = null;
                $longitud = null;
                $tipoUbicacion = 'PUNTO_ASIGNADO';
                $ubicacionReal = false;
                $horaUbicacion = null;
                $distancia = null;

                if (
                    $asistencia &&
                    $asistencia->hora_salida &&
                    $asistencia->latitud_salida !== null &&
                    $asistencia->longitud_salida !== null
                ) {
                    $latitud = (float) $asistencia->latitud_salida;
                    $longitud = (float) $asistencia->longitud_salida;
                    $tipoUbicacion = 'SALIDA';
                    $ubicacionReal = true;
                    $horaUbicacion = $asistencia->hora_salida->format('H:i:s');
                    $distancia = $asistencia->distancia_salida_metros;
                } elseif (
                    $asistencia &&
                    $asistencia->hora_llegada &&
                    $asistencia->latitud_llegada !== null &&
                    $asistencia->longitud_llegada !== null
                ) {
                    $latitud = (float) $asistencia->latitud_llegada;
                    $longitud = (float) $asistencia->longitud_llegada;
                    $tipoUbicacion = 'LLEGADA';
                    $ubicacionReal = true;
                    $horaUbicacion = $asistencia->hora_llegada->format('H:i:s');
                    $distancia = $asistencia->distancia_llegada_metros;
                } elseif (
                    $punto &&
                    $punto->latitud !== null &&
                    $punto->longitud !== null
                ) {
                    $latitud = (float) $punto->latitud;
                    $longitud = (float) $punto->longitud;
                }

                $estadoJornada = !$asistencia
                    ? 'PENDIENTE'
                    : ($asistencia->hora_salida ? 'FINALIZADA' : 'EN_JORNADA');

                return [
                    'tipo' => 'VENDEDOR',
                    'id_asignacion' => $asignacion->id_asignacion,
                    'vendedor' => trim(
                        ($usuario?->nombre ?? 'Vendedor') . ' ' .
                        ($usuario?->apellido ?? '')
                    ),
                    'codigo' => $asignacion->vendedor?->codigo_empleado ?? '-',
                    'punto' => $punto?->nombre ?? 'Sin punto',
                    'direccion' => $punto?->direccion ?? '',
                    'latitud' => $latitud,
                    'longitud' => $longitud,
                    'tipo_ubicacion' => $tipoUbicacion,
                    'ubicacion_real' => $ubicacionReal,
                    'hora_ubicacion' => $horaUbicacion,
                    'estado' => $asistencia?->estado?->nombre ?? 'PENDIENTE',
                    'estado_jornada' => $estadoJornada,
                    'hora_llegada' => $asistencia?->hora_llegada?->format('H:i:s'),
                    'hora_salida' => $asistencia?->hora_salida?->format('H:i:s'),
                    'distancia' => $distancia !== null
                        ? round((float) $distancia, 0)
                        : null,
                    'radio_permitido' => $punto?->radio_permitido_metros,
                ];
            })
            ->filter(fn ($item) =>
                $item['latitud'] !== null &&
                $item['longitud'] !== null
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | SUPERVISORES: ÚLTIMA UBICACIÓN + VISITAS DE HOY
        |--------------------------------------------------------------------------
        */

        $ubicacionesSupervisor = UbicacionSupervisor::with('supervisor')
            ->get();

        $visitasSupervisorHoy = VisitaSupervisor::with([
            'supervisor',
            'puntoVenta',
        ])
        ->whereDate('fecha', $hoy)
        ->orderBy('hora_llegada')
        ->get();

        $mapaSupervisores = $ubicacionesSupervisor
            ->map(function ($ubicacion) use ($visitasSupervisorHoy, $hoy) {
                $usuario = $ubicacion->supervisor;

                if (!$usuario) {
                    return null;
                }

                $visitas = $visitasSupervisorHoy
                    ->where('id_supervisor', $ubicacion->id_supervisor)
                    ->values();

                $visitaAbierta = $visitas
                    ->first(fn ($v) => $v->hora_salida === null);

                $visitasFinalizadas = $visitas
                    ->filter(fn ($v) => $v->hora_salida !== null)
                    ->values();

                $fechaUbicacion = $ubicacion->fecha_hora;

                $ubicacionEsDeHoy = $fechaUbicacion
                    ? $fechaUbicacion->toDateString() === $hoy
                    : false;

                return [
                    'tipo' => 'SUPERVISOR',
                    'id_supervisor' => $ubicacion->id_supervisor,
                    'supervisor' => trim(
                        ($usuario->nombre ?? 'Supervisor') . ' ' .
                        ($usuario->apellido ?? '')
                    ),
                    'latitud' => (float) $ubicacion->latitud,
                    'longitud' => (float) $ubicacion->longitud,
                    'precision' => $ubicacion->precision_metros !== null
                        ? round((float) $ubicacion->precision_metros, 0)
                        : null,
                    'fecha_hora' => $fechaUbicacion
                        ? $fechaUbicacion->format('d/m/Y H:i:s')
                        : null,
                    'hora_ubicacion' => $fechaUbicacion
                        ? $fechaUbicacion->format('H:i:s')
                        : null,
                    'ubicacion_hoy' => $ubicacionEsDeHoy,
                    'visita_actual' => $visitaAbierta
                        ? [
                            'punto' => $visitaAbierta->puntoVenta?->nombre
                                ?? 'Punto no identificado',
                            'hora_llegada' => $visitaAbierta->hora_llegada
                                ?->format('H:i:s'),
                        ]
                        : null,
                    'visitas' => $visitasFinalizadas
                        ->map(function ($visita) {
                            return [
                                'punto' => $visita->puntoVenta?->nombre
                                    ?? 'Punto no identificado',
                                'hora_llegada' => $visita->hora_llegada
                                    ?->format('H:i:s'),
                                'hora_salida' => $visita->hora_salida
                                    ?->format('H:i:s'),
                                'latitud' => $visita->puntoVenta?->latitud !== null
                                    ? (float) $visita->puntoVenta->latitud
                                    : null,
                                'longitud' => $visita->puntoVenta?->longitud !== null
                                    ? (float) $visita->puntoVenta->longitud
                                    : null,
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->filter()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | PUNTOS VISITADOS PARA DIBUJAR HISTORIAL EN EL MAPA
        |--------------------------------------------------------------------------
        */

        $mapaVisitasSupervisor = $visitasSupervisorHoy
            ->map(function ($visita) {
                $punto = $visita->puntoVenta;
                $usuario = $visita->supervisor;

                if (
                    !$punto ||
                    $punto->latitud === null ||
                    $punto->longitud === null
                ) {
                    return null;
                }

                return [
                    'id_visita' => $visita->id_visita,
                    'id_supervisor' => $visita->id_supervisor,
                    'supervisor' => trim(
                        ($usuario?->nombre ?? 'Supervisor') . ' ' .
                        ($usuario?->apellido ?? '')
                    ),
                    'punto' => $punto->nombre ?? 'Punto',
                    'latitud' => (float) $punto->latitud,
                    'longitud' => (float) $punto->longitud,
                    'hora_llegada' => $visita->hora_llegada?->format('H:i:s'),
                    'hora_salida' => $visita->hora_salida?->format('H:i:s'),
                    'estado' => $visita->hora_salida
                        ? 'VISITADO'
                        : 'EN_VISITA',
                ];
            })
            ->filter()
            ->values();

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
                'mapaVendedores',
                'mapaSupervisores',
                'mapaVisitasSupervisor'
            )
        );
    }
}
