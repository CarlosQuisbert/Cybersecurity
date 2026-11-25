<?php

use App\Http\Controllers\Panel\Autenticacion\AccesoController;
use App\Http\Controllers\Panel\ComentarioIncidenteController;
use App\Http\Controllers\Panel\IncidenteController;
use App\Http\Controllers\Panel\InicioController;
use App\Http\Controllers\Panel\PermisoController;
use App\Http\Controllers\Panel\RolController;
use App\Http\Controllers\Panel\TipoIncidenteController;
use App\Http\Controllers\Panel\UsuarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas web
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('acceso');
});

// Autenticación
Route::get('/iniciar-sesion', [AccesoController::class, 'mostrarFormulario'])->name('acceso');
Route::post('/iniciar-sesion', [AccesoController::class, 'iniciarSesion']);
Route::get('/cerrar-sesion', [AccesoController::class, 'cerrarSesion'])->name('salir');

// Panel de administración
Route::prefix('panel')->group(function () {

    Route::get('/inicio', [InicioController::class, 'inicio'])->name('panel.inicio');

    // Usuarios
    Route::get('/usuarios', [UsuarioController::class, 'listar'])->name('usuarios.listar');
    Route::get('/usuarios/crear', [UsuarioController::class, 'crear'])->name('usuarios.crear');
    Route::post('/usuarios', [UsuarioController::class, 'guardar'])->name('usuarios.guardar');
    Route::get('/usuarios/{id}/editar', [UsuarioController::class, 'editar'])->name('usuarios.editar');
    Route::match(['put', 'patch'], '/usuarios/{id}', [UsuarioController::class, 'actualizar'])->name('usuarios.actualizar');
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'eliminar'])->name('usuarios.eliminar');

    // Incidentes
    Route::get('/incidentes', [IncidenteController::class, 'listar'])->name('incidentes.listar');
    Route::post('/incidentes', [IncidenteController::class, 'guardar'])->name('incidentes.guardar');
    Route::get('/incidentes/{id}', [IncidenteController::class, 'mostrar'])->name('incidentes.mostrar');
    Route::match(['put', 'patch'], '/incidentes/{id}', [IncidenteController::class, 'actualizar'])->name('incidentes.actualizar');
    Route::delete('/incidentes/{id}', [IncidenteController::class, 'eliminar'])->name('incidentes.eliminar');

    // Comentarios de incidentes
    Route::get('/incidentes/{incidente}/comentarios', [ComentarioIncidenteController::class, 'listar'])->name('comentarios.listar');
    Route::post('/incidentes/{incidente}/comentarios', [ComentarioIncidenteController::class, 'guardar'])->name('comentarios.guardar');
    Route::delete('/incidentes/{incidente}/comentarios/{id}', [ComentarioIncidenteController::class, 'eliminar'])->name('comentarios.eliminar');

    // Tipos de incidente
    Route::get('/tipos-incidente', [TipoIncidenteController::class, 'listar'])->name('tipos-incidente.listar');
    Route::post('/tipos-incidente', [TipoIncidenteController::class, 'guardar'])->name('tipos-incidente.guardar');
    Route::match(['put', 'patch'], '/tipos-incidente/{id}', [TipoIncidenteController::class, 'actualizar'])->name('tipos-incidente.actualizar');
    Route::delete('/tipos-incidente/{id}', [TipoIncidenteController::class, 'eliminar'])->name('tipos-incidente.eliminar');

    // Roles
    Route::get('/roles', [RolController::class, 'listar'])->name('roles.listar');
    Route::post('/roles', [RolController::class, 'guardar'])->name('roles.guardar');
    Route::match(['put', 'patch'], '/roles/{id}', [RolController::class, 'actualizar'])->name('roles.actualizar');
    Route::delete('/roles/{id}', [RolController::class, 'eliminar'])->name('roles.eliminar');

    // Permisos
    Route::get('/permisos', [PermisoController::class, 'listar'])->name('permisos.listar');
    Route::post('/permisos', [PermisoController::class, 'guardar'])->name('permisos.guardar');
    Route::match(['put', 'patch'], '/permisos/{id}', [PermisoController::class, 'actualizar'])->name('permisos.actualizar');
    Route::delete('/permisos/{id}', [PermisoController::class, 'eliminar'])->name('permisos.eliminar');
});
