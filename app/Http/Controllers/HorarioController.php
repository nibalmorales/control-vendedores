<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTADO
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $estado = $request->input('estado');


        $query = Horario::query();


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
                        );
                }
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
        | RESULTADOS
        |--------------------------------------------------------------------------
        */

        $horarios = $query
            ->orderBy('hora_entrada')
            ->paginate(15)
            ->withQueryString();


        return view(
            'horarios.index',
            compact(
                'horarios',
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
        return view(
            'horarios.create'
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

            'nombre' => [
                'required',
                'string',
                'max:100',
            ],

            'hora_entrada' => [
                'required',
                'date_format:H:i',
            ],

            'hora_salida' => [
                'required',
                'date_format:H:i',
                'after:hora_entrada',
            ],

            'tolerancia_minutos' => [
                'required',
                'integer',
                'min:0',
                'max:120',
            ],

        ]);


        $datos['activo'] =
            $request->boolean('activo');


        Horario::create(
            $datos
        );


        return redirect()
            ->route('horarios.index')
            ->with(
                'success',
                'Horario registrado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EDITAR
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $horario = Horario::query()
            ->where(
                'id_horario',
                $id
            )
            ->firstOrFail();


        return view(
            'horarios.edit',
            compact('horario')
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

        $horario = Horario::query()
            ->where(
                'id_horario',
                $id
            )
            ->firstOrFail();


        $datos = $request->validate([

            'nombre' => [
                'required',
                'string',
                'max:100',
            ],

            'hora_entrada' => [
                'required',
                'date_format:H:i',
            ],

            'hora_salida' => [
                'required',
                'date_format:H:i',
                'after:hora_entrada',
            ],

            'tolerancia_minutos' => [
                'required',
                'integer',
                'min:0',
                'max:120',
            ],

        ]);


        $datos['activo'] =
            $request->boolean('activo');


        $horario->update(
            $datos
        );


        return redirect()
            ->route('horarios.index')
            ->with(
                'success',
                'Horario actualizado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIVAR / DESACTIVAR
    |--------------------------------------------------------------------------
    */

    public function cambiarEstado($id)
    {
        $horario = Horario::query()
            ->where(
                'id_horario',
                $id
            )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | CAMBIAR ESTADO
        |--------------------------------------------------------------------------
        |
        | No eliminamos el horario porque puede estar relacionado
        | con asignaciones y registros históricos.
        |
        */

        $horario->activo =
            !$horario->activo;

        $horario->save();


        $mensaje =
            $horario->activo
                ? 'Horario activado correctamente.'
                : 'Horario desactivado correctamente.';


        return redirect()
            ->route('horarios.index')
            ->with(
                'success',
                $mensaje
            );
    }
}
