<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'FieldControl')
    </title>


    {{-- =========================================================
         PWA
    ========================================================= --}}

    <link
        rel="manifest"
        href="{{ asset('manifest.json') }}"
    >

    <meta
        name="theme-color"
        content="#071f3d"
    >

    <meta
        name="mobile-web-app-capable"
        content="yes"
    >

    <meta
        name="apple-mobile-web-app-capable"
        content="yes"
    >

    <meta
        name="apple-mobile-web-app-status-bar-style"
        content="default"
    >

    <meta
        name="apple-mobile-web-app-title"
        content="FieldControl"
    >

    <link
        rel="apple-touch-icon"
        href="{{ asset('icons/icon-192.png') }}"
    >


    {{-- =========================================================
         RESTAURAR ESTADO SIDEBAR
    ========================================================= --}}

    <script>

        (function () {

            if (
                window.innerWidth > 768 &&
                localStorage.getItem(
                    'fieldcontrol_sidebar'
                ) === 'collapsed'
            ) {

                document.documentElement
                    .classList
                    .add(
                        'sidebar-precollapsed'
                    );

            }

        })();

    </script>


    <style>

        /* =========================================================
           VARIABLES
        ========================================================= */

        :root {

            --topbar-height: 58px;

            --sidebar-width: 250px;

            --sidebar-collapsed: 76px;

            --primary: #071f3d;

            --primary-hover: #0d2b52;

            --active: #2d5bd1;

            --background: #f5f7fb;

            --text: #172033;

            --text-light: #bfdbfe;

            --transition:
                0.28s cubic-bezier(.4, 0, .2, 1);

        }


        /* =========================================================
           GENERAL
        ========================================================= */

        * {
            box-sizing: border-box;
        }


        html,
        body {

            margin: 0;

            padding: 0;

            min-height: 100%;

        }


        body {

            font-family:
                "Segoe UI",
                Arial,
                sans-serif;

            background:
                var(--background);

            color:
                var(--text);

            font-size: 15px;

        }


        button,
        input,
        select,
        textarea {

            font-family:
                "Segoe UI",
                Arial,
                sans-serif;

        }


        .app {
            min-height: 100vh;
        }


        /* =========================================================
           TOPBAR
        ========================================================= */

        .topbar {

            position: fixed;

            top: 0;

            left: 0;

            right: 0;

            height:
                var(--topbar-height);

            display: flex;

            align-items: center;

            justify-content:
                space-between;

            gap: 20px;

            padding:
                0 16px;

            background:
                var(--primary);

            color:
                #ffffff;

            border-bottom:
                1px solid
                rgba(255, 255, 255, .12);

            z-index: 1200;

        }


        .topbar-left {

            display: flex;

            align-items: center;

            gap: 16px;

            min-width: 0;

        }


        /* =========================================================
           HAMBURGUESA
        ========================================================= */

        .menu-button {

            width: 40px;

            height: 36px;

            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

            padding: 0;

            border:
                1px solid
                rgba(255, 255, 255, .30);

            border-radius: 7px;

            background:
                transparent;

            cursor:
                pointer;

            transition:
                background-color .18s ease,
                border-color .18s ease;

        }


        .menu-button:hover {

            background:
                rgba(255, 255, 255, .10);

            border-color:
                rgba(255, 255, 255, .50);

        }


        .hamburger {

            width: 18px;

            display: flex;

            flex-direction: column;

            gap: 4px;

        }


        .hamburger span {

            width: 100%;

            height: 2px;

            display: block;

            background:
                #ffffff;

            border-radius: 2px;

        }


        /* =========================================================
           MARCA
        ========================================================= */

        .topbar-brand {

            font-size: 21px;

            font-weight: 700;

            letter-spacing: .2px;

            color: #ffffff;

            white-space: nowrap;

        }


        /* =========================================================
           TOPBAR DERECHO
        ========================================================= */

        .topbar-right {

            display: flex;

            align-items: center;

            gap: 12px;

            flex-shrink: 0;

        }


        .user-area {

            display: flex;

            align-items: center;

            gap: 8px;

        }


        .user-avatar {

            width: 25px;

            height: 25px;

            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

            border:
                2px solid #ffffff;

            border-radius: 50%;

            color: #ffffff;

        }


        .user-avatar svg {

            width: 17px;

            height: 17px;

            fill: none;

            stroke:
                currentColor;

            stroke-width: 2;

            stroke-linecap:
                round;

            stroke-linejoin:
                round;

        }


        .user-info {

            text-align: right;

            line-height: 1.1;

        }


        .user-name {

            font-size: 14px;

            font-weight: 700;

            color: #ffffff;

            white-space: nowrap;

        }


        .user-role {

            margin-top: 3px;

            font-size: 11px;

            font-weight: 500;

            color:
                var(--text-light);

        }


        /* =========================================================
           LOGOUT
        ========================================================= */

        .logout-button {

            padding:
                8px 13px;

            border:
                1px solid
                rgba(255, 255, 255, .35);

            border-radius: 18px;

            background:
                transparent;

            color:
                #ffffff;

            font-size: 12px;

            font-weight: 600;

            cursor: pointer;

            white-space: nowrap;

            transition:
                background-color .18s ease,
                border-color .18s ease;

        }


        .logout-button:hover {

            background:
                rgba(255, 255, 255, .10);

            border-color:
                rgba(255, 255, 255, .60);

        }


        /* =========================================================
           SIDEBAR
        ========================================================= */

        .sidebar {

            position: fixed;

            top:
                var(--topbar-height);

            left: 0;

            width:
                var(--sidebar-width);

            height:
                calc(
                    100vh
                    -
                    var(--topbar-height)
                );

            padding:
                18px 14px;

            background:
                var(--primary);

            color:
                #ffffff;

            overflow-x:
                hidden;

            overflow-y:
                auto;

            z-index: 1000;

            transition:
                width var(--transition),
                transform var(--transition);

        }


        .sidebar.collapsed {

            width:
                var(--sidebar-collapsed);

        }


        .navigation-title {

            padding:
                0 10px;

            margin:
                0 0 14px;

            font-size: 15px;

            font-weight: 700;

            color:
                #ffffff;

            white-space:
                nowrap;

        }


        .sidebar.collapsed
        .navigation-title {

            display: none;

        }


        /* =========================================================
           MENÚ
        ========================================================= */

        .menu {

            display: flex;

            flex-direction: column;

            gap: 6px;

        }


        .menu-link {

            min-height: 50px;

            display: flex;

            align-items: center;

            gap: 12px;

            padding:
                5px 10px;

            border-radius: 10px;

            font-size: 16px;

            font-weight: 700;

            color:
                #ffffff;

            text-decoration:
                none;

            white-space:
                nowrap;

            transition:
                background-color .18s ease,
                color .18s ease;

        }


        .menu-link:hover {

            background:
                rgba(255, 255, 255, .08);

            color:
                #ffffff;

        }


        .menu-link.active {

            background:
                var(--active);

            color:
                #ffffff;

        }


        .menu-icon {

            width: 40px;

            height: 40px;

            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

            border-radius: 12px;

            background:
                rgba(255, 255, 255, .14);

            font-size: 19px;

            line-height: 1;

        }


        .menu-link.active
        .menu-icon {

            background:
                rgba(255, 255, 255, .18);

        }


        .menu-text {
            display: inline;
        }


        /* =========================================================
           SIDEBAR COMPRIMIDO
        ========================================================= */

        .sidebar.collapsed
        .menu-link {

            justify-content:
                center;

            padding-left: 0;

            padding-right: 0;

        }


        .sidebar.collapsed
        .menu-text {

            display: none;

        }


        /* =========================================================
           MAIN
        ========================================================= */

        .main {

            min-height: 100vh;

            margin-left:
                var(--sidebar-width);

            padding-top:
                var(--topbar-height);

            transition:
                margin-left
                var(--transition);

        }


        .main.expanded {

            margin-left:
                var(--sidebar-collapsed);

        }


        /* =========================================================
           PRECOMPRIMIDO
        ========================================================= */

        @media (min-width: 769px) {

            html.sidebar-precollapsed
            .sidebar {

                width:
                    var(--sidebar-collapsed);

                transition:
                    none;

            }


            html.sidebar-precollapsed
            .main {

                margin-left:
                    var(--sidebar-collapsed);

                transition:
                    none;

            }


            html.sidebar-precollapsed
            .navigation-title {

                display:
                    none;

            }


            html.sidebar-precollapsed
            .menu-link {

                justify-content:
                    center;

                padding-left:
                    0;

                padding-right:
                    0;

            }


            html.sidebar-precollapsed
            .menu-text {

                display:
                    none;

            }

        }


        /* =========================================================
           CONTENIDO
        ========================================================= */

        .content {
            padding: 30px;
        }


        .content h1,
        .content h2,
        .content h3 {

            font-family:
                "Segoe UI",
                Arial,
                sans-serif;

            color:
                var(--text);

        }


        .content h1 {
            font-weight: 700;
        }


        /* =========================================================
           OVERLAY
        ========================================================= */

        .overlay {
            display: none;
        }


        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 768px) {

            .topbar {

                height:
                    var(--topbar-height);

                padding:
                    0 10px;

                gap: 8px;

            }


            .topbar-left {
                gap: 9px;
            }


            .menu-button {

                width: 36px;

                height: 34px;

            }


            .topbar-brand {
                font-size: 16px;
            }


            .topbar-right {
                gap: 6px;
            }


            .user-avatar {

                width: 22px;

                height: 22px;

            }


            .user-avatar svg {

                width: 14px;

                height: 14px;

            }


            .user-name {

                font-size: 11px;

                max-width: 90px;

                overflow: hidden;

                text-overflow:
                    ellipsis;

            }


            .user-role {
                font-size: 9px;
            }


            .logout-button {

                padding:
                    6px 8px;

                font-size: 9px;

            }


            .sidebar,
            .sidebar.collapsed {

                width: 250px;

                transform:
                    translateX(-100%);

            }


            .sidebar.mobile-open {

                transform:
                    translateX(0);

            }


            .sidebar.collapsed
            .navigation-title {

                display: block;

            }


            .sidebar.collapsed
            .menu-link {

                justify-content:
                    flex-start;

                padding:
                    5px 10px;

            }


            .sidebar.collapsed
            .menu-text {

                display: inline;

            }


            .main,
            .main.expanded {

                margin-left: 0;

            }


            .content {

                padding:
                    18px 12px;

            }


            .overlay {

                position: fixed;

                top:
                    var(--topbar-height);

                right: 0;

                bottom: 0;

                left: 0;

                display: block;

                background:
                    rgba(15, 23, 42, .48);

                opacity: 0;

                pointer-events:
                    none;

                z-index: 900;

                transition:
                    opacity .25s ease;

            }


            .overlay.show {

                opacity: 1;

                pointer-events:
                    auto;

            }

        }


        @media (max-width: 390px) {

            .topbar-brand {
                font-size: 14px;
            }


            .user-avatar {
                display: none;
            }


            .user-name {
                max-width: 70px;
            }


            .logout-button {
                padding: 6px;
            }


            .content {

                padding-left:
                    10px;

                padding-right:
                    10px;

            }

        }

    </style>


    @stack('styles')

</head>


<body>


<div class="app">


    {{-- =========================================================
         TOPBAR
    ========================================================= --}}

    <header class="topbar">


        <div class="topbar-left">


            <button
                type="button"
                class="menu-button"
                id="menuButton"
                aria-label="Abrir o cerrar menú"
                aria-expanded="true"
            >

                <span class="hamburger">

                    <span></span>

                    <span></span>

                    <span></span>

                </span>

            </button>


            <div class="topbar-brand">

                CONTROL DE CAMPO

            </div>


        </div>


        <div class="topbar-right">


            <div class="user-area">


                <div class="user-avatar">

                    <svg viewBox="0 0 24 24">

                        <circle
                            cx="12"
                            cy="8"
                            r="4"
                        />

                        <path
                            d="M5 21a7 7 0 0 1 14 0"
                        />

                    </svg>

                </div>


                <div class="user-info">


                    <div class="user-name">

                        {{ auth()->user()->nombre }}

                        {{ auth()->user()->apellido }}

                    </div>


                    <div class="user-role">

                        {{ auth()->user()->rol->nombre }}

                    </div>


                </div>


            </div>


            <form
                method="POST"
                action="{{ route('logout') }}"
            >

                @csrf


                <button
                    type="submit"
                    class="logout-button"
                >
                    Cerrar sesión
                </button>

            </form>


        </div>


    </header>



    {{-- =========================================================
         SIDEBAR
    ========================================================= --}}

    <aside
        class="sidebar"
        id="sidebar"
    >


        <div class="navigation-title">

            Navegación

        </div>


        @php

            $rolActual =
                auth()->user()->rol->nombre;

        @endphp


        <nav class="menu">


            {{-- =====================================================
                 ADMIN
            ====================================================== --}}

            @if($rolActual === 'ADMIN')


                <a
                    href="{{ route('dashboard') }}"
                    class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    title="Dashboard"
                >

                    <span class="menu-icon">
                        📊
                    </span>

                    <span class="menu-text">
                        Dashboard
                    </span>

                </a>


                <a
                    href="{{ route('vendedores.index') }}"
                    class="menu-link {{ request()->routeIs('vendedores.*') ? 'active' : '' }}"
                    title="Vendedores"
                >

                    <span class="menu-icon">
                        👥
                    </span>

                    <span class="menu-text">
                        Vendedores
                    </span>

                </a>


                <a
                    href="{{ route('puntos-venta.index') }}"
                    class="menu-link {{ request()->routeIs('puntos-venta.*') ? 'active' : '' }}"
                    title="Puntos de venta"
                >

                    <span class="menu-icon">
                        📍
                    </span>

                    <span class="menu-text">
                        Puntos de venta
                    </span>

                </a>


                <a
                    href="{{ route('horarios.index') }}"
                    class="menu-link {{ request()->routeIs('horarios.*') ? 'active' : '' }}"
                    title="Horarios"
                >

                    <span class="menu-icon">
                        🕒
                    </span>

                    <span class="menu-text">
                        Horarios
                    </span>

                </a>


                <a
                    href="{{ route('asignaciones.index') }}"
                    class="menu-link {{ request()->routeIs('asignaciones.*') ? 'active' : '' }}"
                    title="Asignaciones"
                >

                    <span class="menu-icon">
                        📋
                    </span>

                    <span class="menu-text">
                        Asignaciones
                    </span>

                </a>


                <a
                    href="{{ route('asistencias.index') }}"
                    class="menu-link {{ request()->routeIs('asistencias.*') ? 'active' : '' }}"
                    title="Asistencias"
                >

                    <span class="menu-icon">
                        ✅
                    </span>

                    <span class="menu-text">
                        Asistencias
                    </span>

                </a>


                <a
                    href="{{ route('reportes.asistencias') }}"
                    class="menu-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}"
                    title="Reportes"
                >

                    <span class="menu-icon">
                        📈
                    </span>

                    <span class="menu-text">
                        Reportes
                    </span>

                </a>


            @endif



            {{-- =====================================================
                 SUPERVISOR
            ====================================================== --}}

            @if($rolActual === 'SUPERVISOR')


                {{-- MI JORNADA --}}

                <a
                    href="{{ route('vendedor.jornada') }}"
                    class="menu-link {{ request()->routeIs('vendedor.jornada') ? 'active' : '' }}"
                    title="Mi jornada"
                >

                    <span class="menu-icon">
                        📍
                    </span>

                    <span class="menu-text">
                        Mi jornada
                    </span>

                </a>


                {{-- MI ASISTENCIA --}}

                <a
                    href="{{ route('vendedor.asistencia') }}"
                    class="menu-link {{ request()->routeIs('vendedor.asistencia') ? 'active' : '' }}"
                    title="Mi asistencia"
                >

                    <span class="menu-icon">
                        ✅
                    </span>

                    <span class="menu-text">
                        Mi asistencia
                    </span>

                </a>


                {{-- MI HISTORIAL --}}

                <a
                    href="{{ route('vendedor.historial') }}"
                    class="menu-link {{ request()->routeIs('vendedor.historial') ? 'active' : '' }}"
                    title="Mi historial"
                >

                    <span class="menu-icon">
                        🕘
                    </span>

                    <span class="menu-text">
                        Mi historial
                    </span>

                </a>


                {{-- MIS VENDEDORES --}}

                <a
                    href="{{ route('vendedores.index') }}"
                    class="menu-link {{ request()->routeIs('vendedores.*') ? 'active' : '' }}"
                    title="Mis vendedores"
                >

                    <span class="menu-icon">
                        👥
                    </span>

                    <span class="menu-text">
                        Mis vendedores
                    </span>

                </a>


                {{-- PUNTOS DE VENTA --}}

                <a
                    href="{{ route('puntos-venta.index') }}"
                    class="menu-link {{ request()->routeIs('puntos-venta.*') ? 'active' : '' }}"
                    title="Puntos de venta"
                >

                    <span class="menu-icon">
                        📍
                    </span>

                    <span class="menu-text">
                        Puntos de venta
                    </span>

                </a>


                {{-- HORARIOS --}}

                <a
                    href="{{ route('horarios.index') }}"
                    class="menu-link {{ request()->routeIs('horarios.*') ? 'active' : '' }}"
                    title="Horarios"
                >

                    <span class="menu-icon">
                        🕒
                    </span>

                    <span class="menu-text">
                        Horarios
                    </span>

                </a>


                {{-- ASIGNACIONES EQUIPO --}}

                <a
                    href="{{ route('asignaciones.index') }}"
                    class="menu-link {{ request()->routeIs('asignaciones.*') ? 'active' : '' }}"
                    title="Asignaciones"
                >

                    <span class="menu-icon">
                        📋
                    </span>

                    <span class="menu-text">
                        Asignaciones
                    </span>

                </a>


                {{-- ASISTENCIAS EQUIPO --}}

                <a
                    href="{{ route('asistencias.index') }}"
                    class="menu-link {{ request()->routeIs('asistencias.*') ? 'active' : '' }}"
                    title="Asistencias del equipo"
                >

                    <span class="menu-icon">
                        👁
                    </span>

                    <span class="menu-text">
                        Asistencias equipo
                    </span>

                </a>


                {{-- REPORTES EQUIPO --}}

                <a
                    href="{{ route('reportes.asistencias') }}"
                    class="menu-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}"
                    title="Reportes del equipo"
                >

                    <span class="menu-icon">
                        📈
                    </span>

                    <span class="menu-text">
                        Reportes
                    </span>

                </a>


            @endif



            {{-- =====================================================
                 VENDEDOR
            ====================================================== --}}

            @if($rolActual === 'VENDEDOR')


                <a
                    href="{{ route('vendedor.jornada') }}"
                    class="menu-link {{ request()->routeIs('vendedor.jornada') ? 'active' : '' }}"
                    title="Mi jornada"
                >

                    <span class="menu-icon">
                        📍
                    </span>

                    <span class="menu-text">
                        Mi jornada
                    </span>

                </a>


                <a
                    href="{{ route('vendedor.asistencia') }}"
                    class="menu-link {{ request()->routeIs('vendedor.asistencia') ? 'active' : '' }}"
                    title="Mi asistencia"
                >

                    <span class="menu-icon">
                        ✅
                    </span>

                    <span class="menu-text">
                        Mi asistencia
                    </span>

                </a>


                <a
                    href="{{ route('vendedor.historial') }}"
                    class="menu-link {{ request()->routeIs('vendedor.historial') ? 'active' : '' }}"
                    title="Mi historial"
                >

                    <span class="menu-icon">
                        🕘
                    </span>

                    <span class="menu-text">
                        Mi historial
                    </span>

                </a>


            @endif


        </nav>


    </aside>



    {{-- =========================================================
         OVERLAY MOBILE
    ========================================================= --}}

    <div
        class="overlay"
        id="overlay"
    ></div>



    {{-- =========================================================
         CONTENIDO
    ========================================================= --}}

    <main
        class="main"
        id="main"
    >


        <section class="content">

            @yield('content')

        </section>


    </main>


</div>



{{-- =============================================================
     JAVASCRIPT SIDEBAR
============================================================= --}}

<script>

    const sidebar =
        document.getElementById(
            'sidebar'
        );


    const main =
        document.getElementById(
            'main'
        );


    const menuButton =
        document.getElementById(
            'menuButton'
        );


    const overlay =
        document.getElementById(
            'overlay'
        );


    /*
    |--------------------------------------------------------------------------
    | MÓVIL
    |--------------------------------------------------------------------------
    */

    function isMobile()
    {
        return window.innerWidth <= 768;
    }


    /*
    |--------------------------------------------------------------------------
    | RESTAURAR SIDEBAR
    |--------------------------------------------------------------------------
    */

    function restaurarEstadoSidebar()
    {

        if (isMobile()) {

            document.documentElement
                .classList
                .remove(
                    'sidebar-precollapsed'
                );


            sidebar.classList.remove(
                'collapsed'
            );


            main.classList.remove(
                'expanded'
            );


            sidebar.classList.remove(
                'mobile-open'
            );


            overlay.classList.remove(
                'show'
            );


            menuButton.setAttribute(
                'aria-expanded',
                'false'
            );


            return;
        }


        const estado =
            localStorage.getItem(
                'fieldcontrol_sidebar'
            );


        if (estado === 'collapsed') {

            sidebar.classList.add(
                'collapsed'
            );


            main.classList.add(
                'expanded'
            );


            menuButton.setAttribute(
                'aria-expanded',
                'false'
            );

        } else {

            sidebar.classList.remove(
                'collapsed'
            );


            main.classList.remove(
                'expanded'
            );


            menuButton.setAttribute(
                'aria-expanded',
                'true'
            );

        }


        requestAnimationFrame(
            function () {

                document.documentElement
                    .classList
                    .remove(
                        'sidebar-precollapsed'
                    );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | ABRIR / CERRAR
    |--------------------------------------------------------------------------
    */

    function toggleMenu()
    {

        if (isMobile()) {

            const abierto =
                sidebar
                    .classList
                    .toggle(
                        'mobile-open'
                    );


            overlay
                .classList
                .toggle(
                    'show',
                    abierto
                );


            menuButton.setAttribute(
                'aria-expanded',
                abierto
                    ? 'true'
                    : 'false'
            );


            return;
        }


        const comprimido =
            sidebar
                .classList
                .toggle(
                    'collapsed'
                );


        main
            .classList
            .toggle(
                'expanded',
                comprimido
            );


        localStorage.setItem(
            'fieldcontrol_sidebar',

            comprimido
                ? 'collapsed'
                : 'expanded'
        );


        menuButton.setAttribute(
            'aria-expanded',

            comprimido
                ? 'false'
                : 'true'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CERRAR MOBILE
    |--------------------------------------------------------------------------
    */

    function closeMobileMenu()
    {

        sidebar
            .classList
            .remove(
                'mobile-open'
            );


        overlay
            .classList
            .remove(
                'show'
            );


        menuButton.setAttribute(
            'aria-expanded',
            'false'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | EVENTOS
    |--------------------------------------------------------------------------
    */

    menuButton.addEventListener(
        'click',
        toggleMenu
    );


    overlay.addEventListener(
        'click',
        closeMobileMenu
    );


    document
        .querySelectorAll(
            '.menu-link'
        )
        .forEach(
            function (link) {

                link.addEventListener(
                    'click',
                    function () {

                        if (isMobile()) {

                            closeMobileMenu();

                        }

                    }
                );

            }
        );


    /*
    |--------------------------------------------------------------------------
    | RESIZE
    |--------------------------------------------------------------------------
    */

    let ultimoModoMobile =
        isMobile();


    window.addEventListener(
        'resize',
        function () {

            const ahoraMobile =
                isMobile();


            if (
                ahoraMobile ===
                ultimoModoMobile
            ) {

                return;
            }


            ultimoModoMobile =
                ahoraMobile;


            if (ahoraMobile) {

                sidebar.classList.remove(
                    'collapsed'
                );


                main.classList.remove(
                    'expanded'
                );


                sidebar.classList.remove(
                    'mobile-open'
                );


                overlay.classList.remove(
                    'show'
                );


                document.documentElement
                    .classList
                    .remove(
                        'sidebar-precollapsed'
                    );


                menuButton.setAttribute(
                    'aria-expanded',
                    'false'
                );

            } else {

                sidebar.classList.remove(
                    'mobile-open'
                );


                overlay.classList.remove(
                    'show'
                );


                restaurarEstadoSidebar();

            }

        }
    );


    restaurarEstadoSidebar();

</script>


@stack('scripts')


{{-- =============================================================
     SERVICE WORKER
============================================================= --}}

<script>

    if ('serviceWorker' in navigator) {

        window.addEventListener(
            'load',
            function () {

                navigator
                    .serviceWorker
                    .register(
                        '/service-worker.js'
                    )
                    .then(
                        function (registration) {

                            console.log(
                                'FieldControl PWA activa:',
                                registration.scope
                            );

                        }
                    )
                    .catch(
                        function (error) {

                            console.error(
                                'Error registrando Service Worker:',
                                error
                            );

                        }
                    );

            }
        );

    }

</script>


</body>

</html>
