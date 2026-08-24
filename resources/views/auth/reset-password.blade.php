<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nueva contraseña</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        .container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 10px;
            text-align: center;
        }

        .description {
            text-align: center;
            color: #6b7280;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            margin-top: 18px;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
        }

        input:focus {
            outline: none;
            border-color: #2563eb;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error {
            background: #fef2f2;
            color: #991b1b;
        }

        .requirements {
            margin-top: 15px;
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="card">

        <h1>Crear nueva contraseña</h1>

        <p class="description">
            Ingresa y confirma tu nueva contraseña.
        </p>

        @if($errors->any())
            <div class="alert error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">

            @csrf

            <input
                type="hidden"
                name="token"
                value="{{ $token }}"
            >

            <label for="password">
                Nueva contraseña
            </label>

            <input
                type="password"
                id="password"
                name="password"
                autocomplete="new-password"
                required
                autofocus
            >

            <label for="password_confirmation">
                Confirmar contraseña
            </label>

            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                autocomplete="new-password"
                required
            >

            <div class="requirements">
                La contraseña debe tener al menos 10 caracteres,
                mayúsculas, minúsculas, números y símbolos.
            </div>

            <button type="submit">
                Actualizar contraseña
            </button>

        </form>

    </div>

</div>
AAfdasfafasfd&10
</body>
</html>

