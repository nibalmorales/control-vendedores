<?php

namespace App\Http\Controllers;

use App\Models\TokenPassword;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function mostrarOlvidePassword()
    {
        return view('auth.forgot-password');
    }

    public function enviarEnlace(Request $request)
    {
        $request->validate([
            'correo' => ['required', 'email'],
        ]);

        $usuario = Usuario::where('correo', $request->correo)
            ->where('activo', 1)
            ->first();

        if ($usuario) {

            TokenPassword::where('id_usuario', $usuario->id_usuario)
                ->where('tipo', 'RECUPERACION')
                ->where('usado', 0)
                ->update([
                    'usado' => 1
                ]);

            $tokenPlano = Str::random(64);

            TokenPassword::create([
                'id_usuario' => $usuario->id_usuario,
                'token' => hash('sha256', $tokenPlano),
                'tipo' => 'RECUPERACION',
                'fecha_expiracion' => now()->addMinutes(30),
                'usado' => 0,
            ]);

            /*
             * Por ahora vamos a mostrar el enlace en desarrollo.
             * Después conectaremos el correo SMTP.
             */

            $enlace = route('password.reset', [
                'token' => $tokenPlano
            ]);

            return back()
                ->with('status', 'Se generó el enlace de recuperación.')
                ->with('enlace_recuperacion', $enlace);
        }

        /*
         * No revelamos si el correo existe.
         */
        return back()->with(
            'status',
            'Si el correo pertenece a una cuenta válida, recibirás instrucciones para recuperar tu contraseña.'
        );
    }

    public function mostrarRestablecer(string $token)
    {
        $tokenHash = hash('sha256', $token);

        $registro = TokenPassword::where('token', $tokenHash)
            ->where('tipo', 'RECUPERACION')
            ->where('usado', 0)
            ->where('fecha_expiracion', '>', now())
            ->first();

        if (!$registro) {
            return redirect()
                ->route('password.request')
                ->withErrors([
                    'correo' => 'El enlace de recuperación no es válido o ya expiró.'
                ]);
        }

        return view('auth.reset-password', [
            'token' => $token
        ]);
    }

    public function restablecer(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'password' => [
                'required',
                'confirmed',
                Password::min(10)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        $tokenHash = hash('sha256', $request->token);

        $registro = TokenPassword::where('token', $tokenHash)
            ->where('tipo', 'RECUPERACION')
            ->where('usado', 0)
            ->where('fecha_expiracion', '>', now())
            ->first();

        if (!$registro) {
            return back()->withErrors([
                'password' => 'El enlace ya no es válido.'
            ]);
        }

        $usuario = Usuario::find($registro->id_usuario);

        if (!$usuario || !$usuario->activo) {
            return back()->withErrors([
                'password' => 'No se pudo actualizar la contraseña.'
            ]);
        }

        $usuario->password = Hash::make($request->password);
        $usuario->save();

        $registro->usado = 1;
        $registro->save();

        TokenPassword::where('id_usuario', $usuario->id_usuario)
            ->where('id_token', '!=', $registro->id_token)
            ->where('tipo', 'RECUPERACION')
            ->where('usado', 0)
            ->update([
                'usado' => 1
            ]);

        return redirect()
            ->route('login')
            ->with('status', 'Tu contraseña fue actualizada correctamente.');
    }

    public function mostrarCrearPassword(string $token)
    {
        $tokenHash = hash('sha256', $token);

        $registro = TokenPassword::where('token', $tokenHash)
            ->where('tipo', 'INVITACION')
            ->where('usado', 0)
            ->where('fecha_expiracion', '>', now())
            ->first();

        if (!$registro) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'correo' => 'La invitación no es válida o ya expiró.'
                ]);
        }

        return view('auth.create-password', [
            'token' => $token
        ]);
    }


    public function crearPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],

            'password' => [
                'required',
                'confirmed',
                Password::min(10)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $tokenHash = hash('sha256', $request->token);

        DB::transaction(function () use ($request, $tokenHash) {

            $registro = TokenPassword::where('token', $tokenHash)
                ->where('tipo', 'INVITACION')
                ->where('usado', 0)
                ->where('fecha_expiracion', '>', now())
                ->lockForUpdate()
                ->first();

            if (!$registro) {
                throw ValidationException::withMessages([
                    'password' => 'La invitación no es válida o ya expiró.'
                ]);
            }

            $usuario = Usuario::where(
                'id_usuario',
                $registro->id_usuario
            )
            ->where('activo', 1)
            ->lockForUpdate()
            ->first();

            if (!$usuario) {
                throw ValidationException::withMessages([
                    'password' => 'La cuenta no se encuentra disponible.'
                ]);
            }

            $usuario->password = Hash::make($request->password);
            $usuario->save();

            $registro->usado = 1;
            $registro->save();
        });

        return redirect()
            ->route('login')
            ->with(
                'status',
                'Tu contraseña fue creada correctamente. Ya puedes iniciar sesión.'
            );
    }
}
