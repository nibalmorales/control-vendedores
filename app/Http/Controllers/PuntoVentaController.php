<?php

namespace App\Http\Controllers;

use App\Models\PuntoVenta;
use Illuminate\Http\Request;

class PuntoVentaController extends Controller
{
    public function index(Request $request)
    {
        $usuarioActual = auth()->user();

        $q = trim(
            (string) $request->input('q')
        );

        $puntos = PuntoVenta::query()

            /*
            |--------------------------------------------------------------------------
            | SUPERVISOR: SOLO SUS PUNTOS
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
                                    'nombre',
                                    'like',
                                    "%{$q}%"
                                )
                                ->orWhere(
                                    'direccion',
                                    'like',
                                    "%{$q}%"
                                )
                                ->orWhere(
                                    'departamento',
                                    'like',
                                    "%{$q}%"
                                )
                                ->orWhere(
                                    'municipio',
                                    'like',
                                    "%{$q}%"
                                );
                        }
                    );
                }
            )

            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();


        return view(
            'puntos-venta.index',
            compact(
                'puntos',
                'q'
            )
        );
    }


    public function create()
    {
        return view(
            'puntos-venta.create'
        );
    }


    public function store(Request $request)
    {
        $usuarioActual = auth()->user();


        $datos = $request->validate([

            'nombre' => [
                'required',
                'string',
                'max:150',
            ],

            'direccion' => [
                'required',
                'string',
                'max:255',
            ],

            'departamento' => [
                'required',
                'string',
                'max:100',
            ],

            'municipio' => [
                'required',
                'string',
                'max:100',
            ],

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

            'radio_permitido_metros' => [
                'required',
                'integer',
                'min:10',
                'max:5000',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | PROPIETARIO DEL PUNTO
        |--------------------------------------------------------------------------
        */

        if ((int) $usuarioActual->id_rol === 2) {

            /*
            | SUPERVISOR
            |
            | El punto queda automáticamente
            | asociado al supervisor autenticado.
            */

            $datos['id_supervisor'] =
                $usuarioActual->id_usuario;

        } else {

            /*
            | ADMIN
            |
            | Por ahora puede crear puntos globales.
            */

            $datos['id_supervisor'] = null;
        }


        $datos['activo'] =
            $request->boolean('activo');


        PuntoVenta::create($datos);


        return redirect()
            ->route('puntos-venta.index')
            ->with(
                'success',
                'Punto de venta registrado correctamente.'
            );
    }
}
