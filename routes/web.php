<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\VendedorController;
use App\Http\Controllers\PuntoVentaController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\AsistenciaVendedorController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\DashboardController;


/*
|--------------------------------------------------------------------------
| RUTAS PARA INVITADOS
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/login',
        [AuthController::class, 'mostrarLogin']
    )->name('login');


    Route::post(
        '/login',
        [AuthController::class, 'login']
    )->name('login.procesar');


    /*
    |--------------------------------------------------------------------------
    | RECUPERAR CONTRASEÑA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/olvide-password',
        [PasswordController::class, 'mostrarOlvidePassword']
    )->name('password.request');


    Route::post(
        '/olvide-password',
        [PasswordController::class, 'enviarEnlace']
    )->name('password.email');


    Route::get(
        '/restablecer-password/{token}',
        [PasswordController::class, 'mostrarRestablecer']
    )->name('password.reset');


    Route::post(
        '/restablecer-password',
        [PasswordController::class, 'restablecer']
    )->name('password.update');


    /*
    |--------------------------------------------------------------------------
    | INVITACIÓN PARA CREAR CONTRASEÑA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/crear-password/{token}',
        [PasswordController::class, 'mostrarCrearPassword']
    )->name('password.create');


    Route::post(
        '/crear-password',
        [PasswordController::class, 'crearPassword']
    )->name('password.create.store');
});


/*
|--------------------------------------------------------------------------
| RUTAS PARA TODOS LOS USUARIOS AUTENTICADOS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'nocache'
])->group(function () {

    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    )->name('logout');
});


/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
| Exclusivamente ADMIN.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'nocache',
    'rol:ADMIN'
])->group(function () {

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');
});


/*
|--------------------------------------------------------------------------
| ADMINISTRACIÓN
|--------------------------------------------------------------------------
| ADMIN y SUPERVISOR.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'nocache',
    'rol:ADMIN,SUPERVISOR'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | VENDEDORES
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/vendedores',
        [VendedorController::class, 'index']
    )->name('vendedores.index');


    Route::get(
        '/vendedores/crear',
        [VendedorController::class, 'create']
    )->name('vendedores.create');


    Route::post(
        '/vendedores',
        [VendedorController::class, 'store']
    )->name('vendedores.store');


    /*
    |--------------------------------------------------------------------------
    | EDITAR VENDEDOR
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/vendedores/{id}/editar',
        [VendedorController::class, 'edit']
    )->name('vendedores.edit');


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR VENDEDOR
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/vendedores/{id}',
        [VendedorController::class, 'update']
    )->name('vendedores.update');


    /*
    |--------------------------------------------------------------------------
    | ACTIVAR / DESACTIVAR
    |--------------------------------------------------------------------------
    */

    Route::patch(
        '/vendedores/{id}/estado',
        [VendedorController::class, 'cambiarEstado']
    )->name('vendedores.estado');


    /*
    |--------------------------------------------------------------------------
    | PUNTOS DE VENTA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/puntos-venta',
        [PuntoVentaController::class, 'index']
    )->name('puntos-venta.index');


    Route::get(
        '/puntos-venta/crear',
        [PuntoVentaController::class, 'create']
    )->name('puntos-venta.create');


    Route::post(
        '/puntos-venta',
        [PuntoVentaController::class, 'store']
    )->name('puntos-venta.store');


    /*
    |--------------------------------------------------------------------------
    | HORARIOS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/horarios',
        [HorarioController::class, 'index']
    )->name('horarios.index');


    Route::get(
        '/horarios/crear',
        [HorarioController::class, 'create']
    )->name('horarios.create');


    Route::post(
        '/horarios',
        [HorarioController::class, 'store']
    )->name('horarios.store');


    /*
    |--------------------------------------------------------------------------
    | ASIGNACIONES
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/asignaciones',
        [AsignacionController::class, 'index']
    )->name('asignaciones.index');


    Route::get(
        '/asignaciones/crear',
        [AsignacionController::class, 'create']
    )->name('asignaciones.create');


    Route::post(
        '/asignaciones',
        [AsignacionController::class, 'store']
    )->name('asignaciones.store');


    /*
    |--------------------------------------------------------------------------
    | ASISTENCIAS DEL EQUIPO
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/asistencias',
        [AsistenciaController::class, 'index']
    )->name('asistencias.index');


    Route::get(
        '/asistencias/{id}',
        [AsistenciaController::class, 'show']
    )->name('asistencias.show');


    /*
    |--------------------------------------------------------------------------
    | REPORTES
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/reportes/asistencias',
        [ReporteController::class, 'asistencias']
    )->name('reportes.asistencias');


    Route::get(
        '/reportes/asistencias/exportar',
        [ReporteController::class, 'exportarAsistencias']
    )->name('reportes.asistencias.exportar');
});


/*
|--------------------------------------------------------------------------
| ASISTENCIA PERSONAL
|--------------------------------------------------------------------------
| VENDEDOR y SUPERVISOR.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'nocache',
    'rol:VENDEDOR,SUPERVISOR'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | VISITAS DEL SUPERVISOR
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/mi-jornada/ubicacion',
        [AsistenciaVendedorController::class, 'actualizarUbicacionSupervisor']
    )->name('supervisor.ubicacion.actualizar');


    Route::post(
        '/mi-jornada/visita/iniciar',
        [AsistenciaVendedorController::class, 'iniciarVisita']
    )->name('supervisor.visita.iniciar');


    Route::post(
        '/mi-jornada/visita/finalizar',
        [AsistenciaVendedorController::class, 'finalizarVisita']
    )->name('supervisor.visita.finalizar');


    /*
    |--------------------------------------------------------------------------
    | MI JORNADA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/mi-jornada',
        [AsistenciaVendedorController::class, 'miJornada']
    )->name('vendedor.jornada');


    /*
    |--------------------------------------------------------------------------
    | REGISTRAR LLEGADA
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/mi-jornada/llegada',
        [AsistenciaVendedorController::class, 'registrarLlegada']
    )->name('vendedor.llegada');


    /*
    |--------------------------------------------------------------------------
    | REGISTRAR SALIDA
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/mi-jornada/salida',
        [AsistenciaVendedorController::class, 'registrarSalida']
    )->name('vendedor.salida');


    /*
    |--------------------------------------------------------------------------
    | MI ASISTENCIA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/mi-asistencia',
        [AsistenciaVendedorController::class, 'miAsistencia']
    )->name('vendedor.asistencia');


    /*
    |--------------------------------------------------------------------------
    | MI HISTORIAL
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/mi-historial',
        [AsistenciaVendedorController::class, 'miHistorial']
    )->name('vendedor.historial');
});


/*
|--------------------------------------------------------------------------
| RUTA PRINCIPAL
|--------------------------------------------------------------------------
|
| 1 = ADMIN
| 2 = SUPERVISOR
| 3 = VENDEDOR
|
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    /*
    |--------------------------------------------------------------------------
    | NO AUTENTICADO
    |--------------------------------------------------------------------------
    */

    if (!auth()->check()) {

        return redirect()
            ->route('login');
    }


    $usuario = auth()->user();


    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

    if ((int) $usuario->id_rol === 1) {

        return redirect()
            ->route('dashboard');
    }


    /*
    |--------------------------------------------------------------------------
    | SUPERVISOR
    |--------------------------------------------------------------------------
    */

    if ((int) $usuario->id_rol === 2) {

        return redirect()
            ->route('vendedor.jornada');
    }


    /*
    |--------------------------------------------------------------------------
    | VENDEDOR
    |--------------------------------------------------------------------------
    */

    if ((int) $usuario->id_rol === 3) {

        return redirect()
            ->route('vendedor.jornada');
    }


    /*
    |--------------------------------------------------------------------------
    | ROL DESCONOCIDO
    |--------------------------------------------------------------------------
    */

    abort(
        403,
        'El usuario no tiene un rol válido para acceder al sistema.'
    );
});
