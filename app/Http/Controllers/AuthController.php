<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MOSTRAR LOGIN
    |--------------------------------------------------------------------------
    */

    public function mostrarLogin()
    {
        return view('auth.login');
    }


    /*
    |--------------------------------------------------------------------------
    | INICIAR SESIÓN
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDAR DATOS
        |--------------------------------------------------------------------------
        */

        $credenciales = $request->validate([
            'correo' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | PROTECCIÓN CONTRA INTENTOS REPETIDOS
        |--------------------------------------------------------------------------
        */

        $claveLimite =
            Str::lower($request->input('correo'))
            . '|'
            . $request->ip();


        if (RateLimiter::tooManyAttempts($claveLimite, 5)) {

            $segundos =
                RateLimiter::availableIn($claveLimite);

            throw ValidationException::withMessages([
                'correo' =>
                    "Demasiados intentos. Intenta nuevamente en {$segundos} segundos.",
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | BUSCAR USUARIO ACTIVO
        |--------------------------------------------------------------------------
        */

        $usuario = Usuario::where(
            'correo',
            $credenciales['correo']
        )
        ->where('activo', 1)
        ->first();


        /*
        |--------------------------------------------------------------------------
        | AUTENTICAR
        |--------------------------------------------------------------------------
        */

        if (
            !$usuario ||
            !Auth::attempt([
                'correo' =>
                    $credenciales['correo'],

                'password' =>
                    $credenciales['password'],

                'activo' =>
                    1,
            ])
        ) {

            RateLimiter::hit(
                $claveLimite,
                60
            );

            throw ValidationException::withMessages([
                'correo' =>
                    'Las credenciales proporcionadas no son válidas.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN CORRECTO
        |--------------------------------------------------------------------------
        */

        RateLimiter::clear($claveLimite);

        $request->session()->regenerate();

        $usuarioAutenticado = Auth::user();


        /*
        |--------------------------------------------------------------------------
        | REDIRECCIÓN SEGÚN ROL
        |--------------------------------------------------------------------------
        |
        | 1 = ADMIN
        | 2 = SUPERVISOR
        | 3 = VENDEDOR
        |
        */


        /*
        | ADMIN
        */

        if ((int) $usuarioAutenticado->id_rol === 1) {

            return redirect()
                ->route('dashboard');
        }


        /*
        | SUPERVISOR
        */
        if ((int) $usuarioAutenticado->id_rol === 2) {

            return redirect()
                ->route('vendedor.jornada');
        }

        /*
        | VENDEDOR
        */

        if ((int) $usuarioAutenticado->id_rol === 3) {

            return redirect()
                ->route('vendedor.jornada');
        }


        /*
        |--------------------------------------------------------------------------
        | ROL NO VÁLIDO
        |--------------------------------------------------------------------------
        */

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();


        throw ValidationException::withMessages([
            'correo' =>
                'Tu usuario no tiene un rol válido para ingresar al sistema.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | CERRAR SESIÓN
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();


        return redirect()
            ->route('login')
            ->withHeaders([
                'Cache-Control' =>
                    'no-cache, no-store, max-age=0, must-revalidate',

                'Pragma' =>
                    'no-cache',

                'Expires' =>
                    '0',
            ]);
    }
}
