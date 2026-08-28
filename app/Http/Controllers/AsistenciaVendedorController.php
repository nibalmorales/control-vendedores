<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Asistencia;
use App\Models\EstadoAsistencia;
use App\Models\PuntoVenta;
use App\Models\VisitaSupervisor;
use App\Models\UbicacionSupervisor;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AsistenciaVendedorController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MI JORNADA
    |--------------------------------------------------------------------------
    */

    public function miJornada()
    {
        $usuario = auth()->user();
        $vendedor = $usuario->vendedor;

        if (!$vendedor || !$vendedor->activo) {
            abort(
                403,
                'El usuario no tiene un perfil de campo activo asociado.'
            );
        }

        $esSupervisor =
            strtoupper($usuario->rol?->nombre ?? '') === 'SUPERVISOR';

        $this->regularizarAsistenciasSinSalida(
            $vendedor->id_vendedor
        );

        $hoy = now()->toDateString();
        $diaActual = $this->obtenerCampoDia(now());

        /*
        |--------------------------------------------------------------------------
        | ASIGNACIÓN DEL DÍA
        |--------------------------------------------------------------------------
        */

        $asignacion = Asignacion::with([
            'puntoVenta',
            'horario'
        ])
        ->where(
            'id_vendedor',
            $vendedor->id_vendedor
        )
        ->where('activo', 1)
        ->where('fecha_inicio', '<=', $hoy)
        ->where(function ($query) use ($hoy) {
            $query
                ->whereNull('fecha_fin')
                ->orWhere('fecha_fin', '>=', $hoy);
        })
        ->where($diaActual, 1)
        ->orderByDesc('id_asignacion')
        ->first();

        /*
        |--------------------------------------------------------------------------
        | PUNTOS DEL SUPERVISOR
        |--------------------------------------------------------------------------
        */

        $puntosSupervisor = collect();

        if ($esSupervisor) {
            $puntosSupervisor = PuntoVenta::where(
                'id_supervisor',
                $usuario->id_usuario
            )
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | ASISTENCIA
        |--------------------------------------------------------------------------
        */

        $asistencia = null;

        if ($asignacion) {
            $asistencia = Asistencia::with([
                'estado',
                'puntoLlegada',
                'puntoSalida'
            ])
            ->where(
                'id_asignacion',
                $asignacion->id_asignacion
            )
            ->where('fecha', $hoy)
            ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | VISITAS DEL SUPERVISOR
        |--------------------------------------------------------------------------
        */

        $visitaAbierta = null;
        $visitasHoy = collect();

        if ($esSupervisor && $asistencia) {

            $visitaAbierta = VisitaSupervisor::with('puntoVenta')
                ->where(
                    'id_supervisor',
                    $usuario->id_usuario
                )
                ->where(
                    'id_asistencia',
                    $asistencia->id_asistencia
                )
                ->whereNull('hora_salida')
                ->orderByDesc('id_visita')
                ->first();

            $visitasHoy = VisitaSupervisor::with('puntoVenta')
                ->where(
                    'id_supervisor',
                    $usuario->id_usuario
                )
                ->where(
                    'id_asistencia',
                    $asistencia->id_asistencia
                )
                ->whereDate('fecha', $hoy)
                ->orderByDesc('hora_llegada')
                ->get();
        }

        return view(
            'vendedor.mi-jornada',
            compact(
                'vendedor',
                'asignacion',
                'asistencia',
                'esSupervisor',
                'puntosSupervisor',
                'visitaAbierta',
                'visitasHoy'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REGISTRAR LLEGADA
    |--------------------------------------------------------------------------
    */

    public function registrarLlegada(Request $request)
    {
        $request->validate([
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

            'precision' => [
                'required',
                'numeric',
                'min:0',
                'max:10000',
            ],
        ]);

        $usuario = auth()->user();
        $vendedor = $usuario->vendedor;

        if (!$vendedor || !$vendedor->activo) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'No tienes un perfil de campo activo asociado.'
            ]);
        }

        $esSupervisor =
            strtoupper($usuario->rol?->nombre ?? '') === 'SUPERVISOR';

        $this->regularizarAsistenciasSinSalida(
            $vendedor->id_vendedor
        );

        $ahora = now();
        $hoy = $ahora->toDateString();
        $diaActual = $this->obtenerCampoDia($ahora);

        /*
        |--------------------------------------------------------------------------
        | ASIGNACIÓN
        |--------------------------------------------------------------------------
        */

        $asignacion = Asignacion::with([
            'puntoVenta',
            'horario'
        ])
        ->where(
            'id_vendedor',
            $vendedor->id_vendedor
        )
        ->where('activo', 1)
        ->where('fecha_inicio', '<=', $hoy)
        ->where(function ($query) use ($hoy) {
            $query
                ->whereNull('fecha_fin')
                ->orWhere('fecha_fin', '>=', $hoy);
        })
        ->where($diaActual, 1)
        ->orderByDesc('id_asignacion')
        ->first();

        if (!$asignacion) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'No tienes una asignación válida para hoy.'
            ]);
        }

        if (!$asignacion->horario) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'La asignación no tiene un horario configurado.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | EVITAR DOBLE LLEGADA
        |--------------------------------------------------------------------------
        */

        $yaExiste = Asistencia::where(
            'id_asignacion',
            $asignacion->id_asignacion
        )
        ->where('fecha', $hoy)
        ->exists();

        if ($yaExiste) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'La llegada de hoy ya fue registrada.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | DETERMINAR PUNTO
        |--------------------------------------------------------------------------
        */

        $puntoLlegada = null;
        $distancia = null;

        if ($esSupervisor) {

            $resultado = $this->buscarPuntoSupervisor(
                $usuario->id_usuario,
                (float) $request->latitud,
                (float) $request->longitud
            );

            if (!$resultado) {
                throw ValidationException::withMessages([
                    'ubicacion' =>
                        'No tienes puntos de venta activos asignados para supervisar.'
                ]);
            }

            if (!$resultado['dentro']) {
                throw ValidationException::withMessages([
                    'ubicacion' =>
                        'No puedes registrar tu llegada. No te encuentras dentro del radio permitido de ninguno de tus puntos de venta. El punto más cercano es "' .
                        $resultado['punto']->nombre .
                        '" a ' .
                        number_format($resultado['distancia'], 0) .
                        ' metros.'
                ]);
            }

            $puntoLlegada = $resultado['punto'];
            $distancia = $resultado['distancia'];

        } else {

            if (
                !$asignacion->puntoVenta ||
                !$asignacion->puntoVenta->activo
            ) {
                throw ValidationException::withMessages([
                    'ubicacion' =>
                        'El punto de venta asignado no está disponible.'
                ]);
            }

            $puntoLlegada = $asignacion->puntoVenta;

            $distancia = $this->calcularDistanciaMetros(
                (float) $request->latitud,
                (float) $request->longitud,
                (float) $puntoLlegada->latitud,
                (float) $puntoLlegada->longitud
            );

            $radioPermitido =
                (float) $puntoLlegada->radio_permitido_metros;

            if ($distancia > $radioPermitido) {
                throw ValidationException::withMessages([
                    'ubicacion' =>
                        'No puedes registrar tu llegada. Te encuentras a ' .
                        number_format($distancia, 0) .
                        ' metros del punto de venta y el radio permitido es de ' .
                        number_format($radioPermitido, 0) .
                        ' metros.'
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PRESENTE / TARDE
        |--------------------------------------------------------------------------
        */

        $horaEntrada = Carbon::parse(
            $hoy . ' ' .
            $asignacion->horario->hora_entrada
        );

        $limiteTolerancia = $horaEntrada
            ->copy()
            ->addMinutes(
                $asignacion->horario->tolerancia_minutos
            );

        $nombreEstado =
            $ahora->lte($limiteTolerancia)
                ? 'PRESENTE'
                : 'TARDE';

        $estado = EstadoAsistencia::where(
            'nombre',
            $nombreEstado
        )
        ->where('activo', 1)
        ->first();

        if (!$estado) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'No se encontró el estado de asistencia requerido.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | GUARDAR
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $asignacion,
            $estado,
            $puntoLlegada,
            $hoy,
            $ahora,
            $request,
            $distancia
        ) {

            Asistencia::create([
                'id_asignacion' =>
                    $asignacion->id_asignacion,

                'id_punto_llegada' =>
                    $puntoLlegada->id_punto_venta,

                'id_punto_salida' =>
                    null,

                'id_estado_asistencia' =>
                    $estado->id_estado_asistencia,

                'fecha' =>
                    $hoy,

                'hora_llegada' =>
                    $ahora,

                'latitud_llegada' =>
                    $request->latitud,

                'longitud_llegada' =>
                    $request->longitud,

                'precision_llegada_metros' =>
                    $request->precision,

                'distancia_llegada_metros' =>
                    round($distancia, 2),
            ]);
        });

        return redirect()
            ->route('vendedor.jornada')
            ->with(
                'success',
                'Llegada registrada correctamente en ' .
                $puntoLlegada->nombre .
                '.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | INICIAR VISITA DEL SUPERVISOR
    |--------------------------------------------------------------------------
    */

    public function iniciarVisita(Request $request)
    {
        $request->validate([
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

            'precision' => [
                'required',
                'numeric',
                'min:0',
                'max:10000',
            ],

            'observaciones' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $usuario = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | SOLO SUPERVISOR
        |--------------------------------------------------------------------------
        */

        if (
            strtoupper($usuario->rol?->nombre ?? '') !==
            'SUPERVISOR'
        ) {
            abort(
                403,
                'Solo los supervisores pueden registrar visitas.'
            );
        }

        $vendedor = $usuario->vendedor;

        if (!$vendedor || !$vendedor->activo) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'No tienes un perfil de campo activo asociado.'
            ]);
        }

        $ahora = now();
        $hoy = $ahora->toDateString();

        /*
        |--------------------------------------------------------------------------
        | OBTENER ASISTENCIA ACTIVA
        |--------------------------------------------------------------------------
        */

        $asistencia = $this->obtenerAsistenciaActual(
            $vendedor->id_vendedor,
            $hoy
        );

        if (!$asistencia || !$asistencia->hora_llegada) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'Primero debes iniciar tu jornada antes de registrar una visita.'
            ]);
        }

        if ($asistencia->hora_salida) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'Tu jornada ya fue finalizada. No puedes registrar nuevas visitas.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | EVITAR DOS VISITAS ABIERTAS
        |--------------------------------------------------------------------------
        */

        $visitaAbierta = VisitaSupervisor::with('puntoVenta')
            ->where(
                'id_supervisor',
                $usuario->id_usuario
            )
            ->where(
                'id_asistencia',
                $asistencia->id_asistencia
            )
            ->whereNull('hora_salida')
            ->first();

        if ($visitaAbierta) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'Ya tienes una visita activa en "' .
                    ($visitaAbierta->puntoVenta?->nombre ?? 'un punto de venta') .
                    '". Debes finalizarla antes de iniciar otra.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | DETECTAR PUNTO
        |--------------------------------------------------------------------------
        */

        $resultado = $this->buscarPuntoSupervisor(
            $usuario->id_usuario,
            (float) $request->latitud,
            (float) $request->longitud
        );

        if (!$resultado) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'No tienes puntos de venta activos asignados para supervisar.'
            ]);
        }

        if (!$resultado['dentro']) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'No puedes iniciar la visita porque estás fuera del radio permitido. El punto más cercano es "' .
                    $resultado['punto']->nombre .
                    '" a ' .
                    number_format(
                        $resultado['distancia'],
                        0
                    ) .
                    ' metros.'
            ]);
        }

        $punto = $resultado['punto'];

        /*
        |--------------------------------------------------------------------------
        | CREAR VISITA
        |--------------------------------------------------------------------------
        */

        VisitaSupervisor::create([
            'id_supervisor' =>
                $usuario->id_usuario,

            'id_punto_venta' =>
                $punto->id_punto_venta,

            'id_asistencia' =>
                $asistencia->id_asistencia,

            'fecha' =>
                $hoy,

            'hora_llegada' =>
                $ahora,

            'hora_salida' =>
                null,

            'latitud_llegada' =>
                $request->latitud,

            'longitud_llegada' =>
                $request->longitud,

            'precision_llegada_metros' =>
                $request->precision,

            'distancia_llegada_metros' =>
                round(
                    $resultado['distancia'],
                    2
                ),

            'observaciones' =>
                $request->filled('observaciones')
                    ? trim($request->observaciones)
                    : null,
        ]);

        return redirect()
            ->route('vendedor.jornada')
            ->with(
                'success',
                'Visita iniciada correctamente en ' .
                $punto->nombre .
                '.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | FINALIZAR VISITA DEL SUPERVISOR
    |--------------------------------------------------------------------------
    */

    public function finalizarVisita(Request $request)
    {
        $request->validate([
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

            'precision' => [
                'required',
                'numeric',
                'min:0',
                'max:10000',
            ],

            'observaciones' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $usuario = auth()->user();

        if (
            strtoupper($usuario->rol?->nombre ?? '') !==
            'SUPERVISOR'
        ) {
            abort(
                403,
                'Solo los supervisores pueden finalizar visitas.'
            );
        }

        $vendedor = $usuario->vendedor;

        if (!$vendedor || !$vendedor->activo) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'No tienes un perfil de campo activo asociado.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | BUSCAR VISITA ABIERTA
        |--------------------------------------------------------------------------
        */

        $visita = VisitaSupervisor::with('puntoVenta')
            ->where(
                'id_supervisor',
                $usuario->id_usuario
            )
            ->whereNull('hora_salida')
            ->orderByDesc('id_visita')
            ->first();

        if (!$visita) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'No tienes ninguna visita activa para finalizar.'
            ]);
        }

        $punto = $visita->puntoVenta;

        if (!$punto || !$punto->activo) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'El punto correspondiente a esta visita ya no está disponible.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CALCULAR DISTANCIA AL MISMO PUNTO DE LA VISITA
        |--------------------------------------------------------------------------
        */

        $distancia = $this->calcularDistanciaMetros(
            (float) $request->latitud,
            (float) $request->longitud,
            (float) $punto->latitud,
            (float) $punto->longitud
        );

        $radioPermitido =
            (float) $punto->radio_permitido_metros;

        /*
        |--------------------------------------------------------------------------
        | PARA CERRAR VISITA DEBE ESTAR EN EL MISMO PUNTO
        |--------------------------------------------------------------------------
        */

        if ($distancia > $radioPermitido) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'No puedes finalizar esta visita porque ya no te encuentras dentro del radio de "' .
                    $punto->nombre .
                    '". Estás aproximadamente a ' .
                    number_format($distancia, 0) .
                    ' metros.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | OBSERVACIONES
        |--------------------------------------------------------------------------
        */

        $observaciones = $visita->observaciones;

        if ($request->filled('observaciones')) {

            $comentarioSalida =
                trim($request->observaciones);

            if ($observaciones) {
                $observaciones .=
                    ' | Salida: ' .
                    $comentarioSalida;
            } else {
                $observaciones =
                    'Salida: ' .
                    $comentarioSalida;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | GUARDAR SALIDA DE VISITA
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $visita,
            $request,
            $distancia,
            $observaciones
        ) {

            $visita->hora_salida =
                now();

            $visita->latitud_salida =
                $request->latitud;

            $visita->longitud_salida =
                $request->longitud;

            $visita->precision_salida_metros =
                $request->precision;

            $visita->distancia_salida_metros =
                round($distancia, 2);

            $visita->observaciones =
                $observaciones;

            $visita->save();
        });

        return redirect()
            ->route('vendedor.jornada')
            ->with(
                'success',
                'Visita finalizada correctamente en ' .
                $punto->nombre .
                '.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | REGISTRAR SALIDA DE JORNADA
    |--------------------------------------------------------------------------
    */

    public function registrarSalida(Request $request)
    {
        $request->validate([
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

            'precision' => [
                'required',
                'numeric',
                'min:0',
                'max:10000',
            ],

            'motivo_salida' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $usuario = auth()->user();
        $vendedor = $usuario->vendedor;

        if (!$vendedor || !$vendedor->activo) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'No tienes un perfil de campo activo asociado.'
            ]);
        }

        $esSupervisor =
            strtoupper($usuario->rol?->nombre ?? '') === 'SUPERVISOR';

        $ahora = now();
        $hoy = $ahora->toDateString();
        $diaActual = $this->obtenerCampoDia($ahora);

        /*
        |--------------------------------------------------------------------------
        | ASIGNACIÓN
        |--------------------------------------------------------------------------
        */

        $asignacion = Asignacion::with([
            'puntoVenta',
            'horario'
        ])
        ->where(
            'id_vendedor',
            $vendedor->id_vendedor
        )
        ->where('activo', 1)
        ->where('fecha_inicio', '<=', $hoy)
        ->where(function ($query) use ($hoy) {
            $query
                ->whereNull('fecha_fin')
                ->orWhere('fecha_fin', '>=', $hoy);
        })
        ->where($diaActual, 1)
        ->orderByDesc('id_asignacion')
        ->first();

        if (!$asignacion) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'No tienes una asignación válida para hoy.'
            ]);
        }

        if (!$asignacion->horario) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'La asignación no tiene un horario configurado.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ASISTENCIA
        |--------------------------------------------------------------------------
        */

        $asistencia = Asistencia::where(
            'id_asignacion',
            $asignacion->id_asignacion
        )
        ->where('fecha', $hoy)
        ->first();

        if (
            !$asistencia ||
            !$asistencia->hora_llegada
        ) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'Primero debes registrar tu llegada.'
            ]);
        }

        if ($asistencia->hora_salida) {
            throw ValidationException::withMessages([
                'ubicacion' =>
                    'La salida de hoy ya fue registrada.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SUPERVISOR NO PUEDE CERRAR JORNADA CON VISITA ABIERTA
        |--------------------------------------------------------------------------
        */

        if ($esSupervisor) {

            $visitaAbierta = VisitaSupervisor::with('puntoVenta')
                ->where(
                    'id_supervisor',
                    $usuario->id_usuario
                )
                ->where(
                    'id_asistencia',
                    $asistencia->id_asistencia
                )
                ->whereNull('hora_salida')
                ->first();

            if ($visitaAbierta) {
                throw ValidationException::withMessages([
                    'ubicacion' =>
                        'Tienes una visita activa en "' .
                        ($visitaAbierta->puntoVenta?->nombre ?? 'un punto de venta') .
                        '". Finaliza primero la visita antes de cerrar tu jornada.'
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DETERMINAR PUNTO DE SALIDA
        |--------------------------------------------------------------------------
        */

        $puntoSalida = null;
        $distancia = null;
        $salidaFuera = false;
        $puntoReferencia = null;

        if ($esSupervisor) {

            $resultado = $this->buscarPuntoSupervisor(
                $usuario->id_usuario,
                (float) $request->latitud,
                (float) $request->longitud
            );

            if (!$resultado) {
                throw ValidationException::withMessages([
                    'ubicacion' =>
                        'No tienes puntos de venta activos asignados para supervisar.'
                ]);
            }

            $distancia =
                $resultado['distancia'];

            $puntoReferencia =
                $resultado['punto'];

            if ($resultado['dentro']) {

                $puntoSalida =
                    $resultado['punto'];

                $salidaFuera = false;

            } else {

                $puntoSalida = null;
                $salidaFuera = true;
            }

        } else {

            if (
                !$asignacion->puntoVenta ||
                !$asignacion->puntoVenta->activo
            ) {
                throw ValidationException::withMessages([
                    'ubicacion' =>
                        'El punto de venta asignado no está disponible.'
                ]);
            }

            $puntoReferencia =
                $asignacion->puntoVenta;

            $distancia = $this->calcularDistanciaMetros(
                (float) $request->latitud,
                (float) $request->longitud,
                (float) $puntoReferencia->latitud,
                (float) $puntoReferencia->longitud
            );

            $salidaFuera =
                $distancia >
                (float) $puntoReferencia->radio_permitido_metros;

            if (!$salidaFuera) {
                $puntoSalida =
                    $puntoReferencia;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SALIDA ANTICIPADA
        |--------------------------------------------------------------------------
        */

        $horaSalidaProgramada = Carbon::parse(
            $hoy . ' ' .
            $asignacion->horario->hora_salida
        );

        $salidaAnticipada =
            $ahora->lt($horaSalidaProgramada);

        /*
        |--------------------------------------------------------------------------
        | FUERA DE TODOS LOS PUNTOS
        |--------------------------------------------------------------------------
        */

        if (
            $salidaFuera &&
            blank($request->motivo_salida)
        ) {
            throw ValidationException::withMessages([
                'motivo_salida' =>
                    'Estás fuera de un punto permitido. El punto más cercano es "' .
                    ($puntoReferencia?->nombre ?? 'N/D') .
                    '" a ' .
                    number_format($distancia, 0) .
                    ' metros. Debes indicar el motivo para registrar la salida.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | OBSERVACIONES
        |--------------------------------------------------------------------------
        */

        $observaciones = [];

        if ($salidaAnticipada) {
            $observaciones[] =
                'SALIDA ANTICIPADA. Hora programada: ' .
                $horaSalidaProgramada->format('H:i') .
                '. Hora registrada: ' .
                $ahora->format('H:i') .
                '.';
        }

        if ($salidaFuera) {
            $observaciones[] =
                'SALIDA FUERA DE PUNTO AUTORIZADO. Punto más cercano: ' .
                ($puntoReferencia?->nombre ?? 'N/D') .
                '. Distancia: ' .
                number_format($distancia, 2) .
                ' m.';
        }

        if ($request->filled('motivo_salida')) {
            $observaciones[] =
                'Comentario: ' .
                trim($request->motivo_salida);
        }

        $observacionFinal =
            count($observaciones)
                ? implode(' ', $observaciones)
                : null;

        /*
        |--------------------------------------------------------------------------
        | GUARDAR SALIDA
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $asistencia,
            $puntoSalida,
            $request,
            $ahora,
            $distancia,
            $observacionFinal
        ) {

            $asistencia->id_punto_salida =
                $puntoSalida?->id_punto_venta;

            $asistencia->hora_salida =
                $ahora;

            $asistencia->latitud_salida =
                $request->latitud;

            $asistencia->longitud_salida =
                $request->longitud;

            $asistencia->precision_salida_metros =
                $request->precision;

            $asistencia->distancia_salida_metros =
                round($distancia, 2);

            $asistencia->observaciones =
                $observacionFinal;

            $asistencia->save();
        });

        /*
        |--------------------------------------------------------------------------
        | MENSAJE
        |--------------------------------------------------------------------------
        */

        if (
            $salidaFuera &&
            $salidaAnticipada
        ) {
            $mensaje =
                'Salida anticipada y fuera de un punto autorizado registrada correctamente.';

        } elseif ($salidaFuera) {

            $mensaje =
                'Salida fuera de un punto autorizado registrada correctamente.';

        } elseif ($salidaAnticipada) {

            $mensaje =
                'Salida anticipada registrada correctamente.';

        } else {

            $mensaje =
                'Salida registrada correctamente.';
        }

        return redirect()
            ->route('vendedor.jornada')
            ->with('success', $mensaje);
    }


    /*
    |--------------------------------------------------------------------------
    | REGULARIZAR JORNADAS ANTERIORES SIN SALIDA
    |--------------------------------------------------------------------------
    */

    private function regularizarAsistenciasSinSalida(
        int $idVendedor
    ): void {

        $estadoSinSalida = EstadoAsistencia::where(
            'nombre',
            'SIN_SALIDA'
        )
        ->where('activo', 1)
        ->first();

        if (!$estadoSinSalida) {
            return;
        }

        $idsAsignaciones = Asignacion::where(
            'id_vendedor',
            $idVendedor
        )
        ->pluck('id_asignacion');

        if ($idsAsignaciones->isEmpty()) {
            return;
        }

        Asistencia::whereIn(
            'id_asignacion',
            $idsAsignaciones
        )
        ->whereDate(
            'fecha',
            '<',
            now()->toDateString()
        )
        ->whereNotNull('hora_llegada')
        ->whereNull('hora_salida')
        ->where(
            'id_estado_asistencia',
            '!=',
            $estadoSinSalida->id_estado_asistencia
        )
        ->update([
            'id_estado_asistencia' =>
                $estadoSinSalida->id_estado_asistencia,
            'updated_at' => now(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | OBTENER ASISTENCIA ACTUAL
    |--------------------------------------------------------------------------
    */

    private function obtenerAsistenciaActual(
        int $idVendedor,
        string $hoy
    ): ?Asistencia {

        $idsAsignaciones = Asignacion::where(
            'id_vendedor',
            $idVendedor
        )
        ->pluck('id_asignacion');

        return Asistencia::whereIn(
            'id_asignacion',
            $idsAsignaciones
        )
        ->whereDate('fecha', $hoy)
        ->orderByDesc('id_asistencia')
        ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | BUSCAR PUNTO DEL SUPERVISOR
    |--------------------------------------------------------------------------
    */

    private function buscarPuntoSupervisor(
        int $idSupervisor,
        float $latitud,
        float $longitud
    ): ?array {

        $puntos = PuntoVenta::where(
            'id_supervisor',
            $idSupervisor
        )
        ->where('activo', 1)
        ->get();

        if ($puntos->isEmpty()) {
            return null;
        }

        $mejorResultado = null;

        foreach ($puntos as $punto) {

            $distancia =
                $this->calcularDistanciaMetros(
                    $latitud,
                    $longitud,
                    (float) $punto->latitud,
                    (float) $punto->longitud
                );

            if (
                $mejorResultado === null ||
                $distancia <
                $mejorResultado['distancia']
            ) {
                $mejorResultado = [
                    'punto' =>
                        $punto,

                    'distancia' =>
                        $distancia,

                    'dentro' =>
                        $distancia <=
                        (float) $punto->radio_permitido_metros,
                ];
            }
        }

        return $mejorResultado;
    }


    /*
    |--------------------------------------------------------------------------
    | CAMPO DEL DÍA
    |--------------------------------------------------------------------------
    */

    private function obtenerCampoDia(
        Carbon $fecha
    ): string {

        return match ($fecha->dayOfWeekIso) {
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            7 => 'domingo',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | HAVERSINE
    |--------------------------------------------------------------------------
    */

    private function calcularDistanciaMetros(
        float $latitud1,
        float $longitud1,
        float $latitud2,
        float $longitud2
    ): float {

        $radioTierra = 6371000;

        $lat1 =
            deg2rad($latitud1);

        $lat2 =
            deg2rad($latitud2);

        $deltaLatitud =
            deg2rad(
                $latitud2 -
                $latitud1
            );

        $deltaLongitud =
            deg2rad(
                $longitud2 -
                $longitud1
            );

        $a =
            sin($deltaLatitud / 2) ** 2
            +
            cos($lat1)
            *
            cos($lat2)
            *
            sin($deltaLongitud / 2) ** 2;

        $c =
            2 *
            atan2(
                sqrt($a),
                sqrt(1 - $a)
            );

        return
            $radioTierra *
            $c;
    }


    /*
    |--------------------------------------------------------------------------
    | MI ASISTENCIA
    |--------------------------------------------------------------------------
    */

    public function miAsistencia()
    {
        $usuario = auth()->user();
        $vendedor = $usuario->vendedor;

        if (!$vendedor || !$vendedor->activo) {
            abort(
                403,
                'El usuario no tiene un perfil de campo activo asociado.'
            );
        }

        $this->regularizarAsistenciasSinSalida(
            $vendedor->id_vendedor
        );

        $hoy = now()->toDateString();

        $diaActual =
            $this->obtenerCampoDia(now());

        $asignacion = Asignacion::with([
            'puntoVenta',
            'horario'
        ])
        ->where(
            'id_vendedor',
            $vendedor->id_vendedor
        )
        ->where('activo', 1)
        ->where('fecha_inicio', '<=', $hoy)
        ->where(function ($query) use ($hoy) {
            $query
                ->whereNull('fecha_fin')
                ->orWhere('fecha_fin', '>=', $hoy);
        })
        ->where($diaActual, 1)
        ->orderByDesc('id_asignacion')
        ->first();

        $asistencia = null;

        if ($asignacion) {
            $asistencia = Asistencia::with([
                'estado',
                'puntoLlegada',
                'puntoSalida'
            ])
            ->where(
                'id_asignacion',
                $asignacion->id_asignacion
            )
            ->where('fecha', $hoy)
            ->first();
        }

        return view(
            'mi-asistencia.index',
            compact(
                'vendedor',
                'asignacion',
                'asistencia'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MI HISTORIAL
    |--------------------------------------------------------------------------
    */

    public function miHistorial(Request $request)
    {
        $usuario = auth()->user();
        $vendedor = $usuario->vendedor;

        if (!$vendedor || !$vendedor->activo) {
            abort(
                403,
                'El usuario no tiene un perfil de campo activo asociado.'
            );
        }

        $this->regularizarAsistenciasSinSalida(
            $vendedor->id_vendedor
        );

        $idsAsignaciones = Asignacion::where(
            'id_vendedor',
            $vendedor->id_vendedor
        )
        ->pluck('id_asignacion');

        $query = Asistencia::with([
            'estado',
            'puntoLlegada',
            'puntoSalida'
        ])
        ->whereIn(
            'id_asignacion',
            $idsAsignaciones
        );

        if ($request->filled('desde')) {
            $query->whereDate(
                'fecha',
                '>=',
                $request->desde
            );
        }

        if ($request->filled('hasta')) {
            $query->whereDate(
                'fecha',
                '<=',
                $request->hasta
            );
        }

        $asistencias = $query
            ->orderByDesc('fecha')
            ->orderByDesc('id_asistencia')
            ->paginate(15)
            ->withQueryString();

        $idsPagina = $asistencias
            ->getCollection()
            ->pluck('id_asignacion')
            ->unique();

        $asignaciones = Asignacion::with([
            'puntoVenta',
            'horario'
        ])
        ->whereIn(
            'id_asignacion',
            $idsPagina
        )
        ->get()
        ->keyBy('id_asignacion');

        return view(
            'mi-historial.index',
            compact(
                'vendedor',
                'asistencias',
                'asignaciones'
            )
        );
    }


    /*
|--------------------------------------------------------------------------
| ACTUALIZAR UBICACIÓN DEL SUPERVISOR
|--------------------------------------------------------------------------
*/

public function actualizarUbicacionSupervisor(Request $request)
{
    $request->validate([
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

        'precision' => [
            'required',
            'numeric',
            'min:0',
            'max:10000',
        ],
    ]);


    $usuario = auth()->user();


    /*
    |--------------------------------------------------------------------------
    | SOLO SUPERVISOR
    |--------------------------------------------------------------------------
    */

    if (
        strtoupper($usuario->rol?->nombre ?? '') !==
        'SUPERVISOR'
    ) {

        abort(
            403,
            'Solo los supervisores pueden actualizar esta ubicación.'
        );
    }


    $vendedor = $usuario->vendedor;


    if (!$vendedor || !$vendedor->activo) {

        return response()->json([
            'ok' => false,
            'mensaje' =>
                'No tienes un perfil de campo activo.'
        ], 403);
    }


    /*
    |--------------------------------------------------------------------------
    | JORNADA ACTIVA
    |--------------------------------------------------------------------------
    */

    $hoy = now()->toDateString();


    $idsAsignaciones = Asignacion::where(
        'id_vendedor',
        $vendedor->id_vendedor
    )
    ->pluck('id_asignacion');


    $asistencia = Asistencia::whereIn(
        'id_asignacion',
        $idsAsignaciones
    )
    ->whereDate(
        'fecha',
        $hoy
    )
    ->whereNotNull(
        'hora_llegada'
    )
    ->whereNull(
        'hora_salida'
    )
    ->orderByDesc(
        'id_asistencia'
    )
    ->first();


    if (!$asistencia) {

        return response()->json([
            'ok' => false,
            'mensaje' =>
                'No tienes una jornada activa.'
        ], 422);
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR ÚNICA UBICACIÓN
    |--------------------------------------------------------------------------
    */

    UbicacionSupervisor::updateOrCreate(

        [
            'id_supervisor' =>
                $usuario->id_usuario
        ],

        [
            'latitud' =>
                $request->latitud,

            'longitud' =>
                $request->longitud,

            'precision_metros' =>
                $request->precision,

            'fecha_hora' =>
                now(),
        ]

    );


    return response()->json([
        'ok' => true,
        'fecha_hora' =>
            now()->format('Y-m-d H:i:s')
    ]);
}


}
