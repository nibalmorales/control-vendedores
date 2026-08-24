<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear contraseña</title>

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
            max-width: 440px;
            background: white;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
        }

        h1 {
            text-align: center;
            margin: 0 0 10px;
        }

        .description {
            text-align: center;
            color: #6b7280;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin: 18px 0 8px;
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
            margin-top: 24px;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .requirements {
            margin-top: 15px;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
        }

        .error {
            background: #fef2f2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="card">

        <h1>Bienvenido a FieldControl</h1>

        <p class="description">
            Crea tu contraseña para activar el acceso a tu cuenta.
        </p>

        @if($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('password.create.store') }}"
        >

            @csrf

            <input
                type="hidden"
                name="token"
                value="{{ $token }}"
            >

            <label for="password">
                Contraseña
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
                Mínimo 10 caracteres, incluyendo mayúsculas,
                minúsculas, números y símbolos.
            </div>

            <button type="submit">
                Crear contraseña
            </button>

        </form>

    </div>

</div>

</body>
</html>
