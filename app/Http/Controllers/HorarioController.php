<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    public function index()
    {
        $horarios = Horario::orderBy('hora_entrada')
            ->paginate(15);

        return view('horarios.index', compact('horarios'));
    }

    public function create()
    {
        return view('horarios.create');
    }

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

        $datos['activo'] = $request->boolean('activo');

        Horario::create($datos);

        return redirect()
            ->route('horarios.index')
            ->with(
                'success',
                'Horario registrado correctamente.'
            );
    }
}
