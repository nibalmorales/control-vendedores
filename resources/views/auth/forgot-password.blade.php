<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Recuperar contraseña</title>

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
            background: #ffffff;
            width: 100%;
            max-width: 420px;
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
            margin-top: 18px;
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

        .back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #2563eb;
            text-decoration: none;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .success {
            background: #ecfdf5;
            color: #065f46;
        }

        .error {
            background: #fef2f2;
            color: #991b1b;
        }

        .dev-link {
            margin-top: 15px;
            padding: 12px;
            background: #fff7ed;
            border-radius: 8px;
            overflow-wrap: anywhere;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="card">

        <h1>Recuperar contraseña</h1>

        <p class="description">
            Ingresa tu correo y te enviaremos las instrucciones
            para crear una nueva contraseña.
        </p>

        @if(session('status'))
            <div class="alert success">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">

            @csrf

            <label for="correo">Correo electrónico</label>

            <input
                type="email"
                id="correo"
                name="correo"
                value="{{ old('correo') }}"
                autocomplete="email"
                required
                autofocus
            >

            <button type="submit">
                Enviar enlace
            </button>

        </form>

        @if(session('enlace_recuperacion'))
            <div class="dev-link">
                <strong>Solo desarrollo:</strong><br>
                <a href="{{ session('enlace_recuperacion') }}">
                    Abrir enlace de recuperación
                </a>
            </div>
        @endif

        <a href="{{ route('login') }}" class="back">
            Volver al inicio de sesión
        </a>

    </div>

</div>

</body>
</html>
