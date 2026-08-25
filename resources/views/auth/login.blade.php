<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #071f3d;
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .brand {
            text-align: center;
            margin-bottom: 28px;
        }


        .brand-icon {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }


        h1 {
            margin: 0 0 6px;
            font-size: 30px;
            color: #0f2742;
        }

        .subtitle {
            margin: 0;
            color: #111827;
            font-size: 18px;
            line-height: 1.35;
        }

        .login-title {
            margin-top: 28px;
            margin-bottom: 10px;
            font-size: 20px;
            color: #111827;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .subtitle {
            margin: 0;
            color: #6b7280;
        }

        label {
            display: block;
            margin-top: 18px;
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

        .password-container {
            position: relative;
        }

        .password-container input {
            padding-right: 70px;
        }

        .show-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #2563eb;
            cursor: pointer;
        }

        .login-button {
            width: 100%;
            margin-top: 24px;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
        }

        .login-button:hover {
            background: #1d4ed8;
        }

        .forgot {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #2563eb;
            text-decoration: none;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 26px 0;
            color: #9ca3af;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            padding: 0 12px;
        }

        .google-button {
            width: 100%;
            padding: 13px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #ffffff;
            font-size: 16px;
            color: #374151;
            cursor: pointer;
        }

        .google-button:disabled {
            cursor: not-allowed;
            opacity: .6;
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

        .security {
            margin-top: 25px;
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
        }

        @media (max-width: 480px) {
            .card {
                padding: 26px 20px;
            }
        }

    @media (max-width: 480px) {

        body {
            background: #ffffff;
        }

        .container {
            min-height: 100vh;
            padding: 0;
            align-items: flex-start;
        }

        .card {
            max-width: none;
            min-height: 100vh;
            border-radius: 0;
            box-shadow: none;
            padding: 32px 24px 24px;
        }

        .brand {
            margin-bottom: 20px;
        }

        .brand-icon svg {
            width: 50px;
            height: 60px;
        }

        h1 {
            font-size: 27px;
        }

        .subtitle {
            font-size: 16px;
        }

        .login-title {
            margin-top: 25px;
            font-size: 20px;
        }

        label {
            margin-top: 15px;
        }

        input {
            padding: 12px;
        }

        .login-button {
            margin-top: 20px;
        }

        .divider {
            margin: 22px 0;
        }

        .security {
            margin-top: 20px;
        }
    }

.logo {
  width: 75px;
  height: auto;
}

    </style>
</head>

<body>

    <div class="container">

        <div class="card">

            <div class="brand">

        <div class="brand-icon">

           <img class="logo" src="img/logo_vectorizado.svg" alt="Photo Moment">



        </div>

                <h1>Control de Campo</h1>

                <p class="subtitle">
                    Guatemala
                </p>

                <h2 class="login-title">
                    Iniciar sesión
                </h2>

            </div>

            @if (session('status'))
                <div class="alert success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.procesar') }}">

                @csrf

                <label for="correo">
                    Correo electrónico
                </label>

                <input type="email" id="correo" name="correo" value="{{ old('correo') }}" autocomplete="email"
                    required autofocus>

                <label for="password">
                    Contraseña
                </label>

                <div class="password-container">

                    <input type="password" id="password" name="password" autocomplete="current-password" required>

                    <button type="button" class="show-password" id="togglePassword">
                        Mostrar
                    </button>

                </div>

                <button type="submit" class="login-button">
                    Iniciar sesión
                </button>

            </form>

            <a href="{{ route('password.request') }}" class="forgot">
                ¿Olvidaste tu contraseña?
            </a>

            <!--<div class="divider">
                <span>o</span>
            </div>

            <button type="button" class="google-button" disabled title="Disponible próximamente">
                Continuar con Google
            </button> -->

            <div class="security">
                Acceso exclusivo para usuarios autorizados
            </div>

        </div>

    </div>

    <script>
        const password = document.getElementById('password');
        const toggle = document.getElementById('togglePassword');

        toggle.addEventListener('click', function() {

            if (password.type === 'password') {
                password.type = 'text';
                toggle.textContent = 'Ocultar';
            } else {
                password.type = 'password';
                toggle.textContent = 'Mostrar';
            }

        });
    </script>

</body>

</html>
