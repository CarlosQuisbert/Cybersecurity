<?php

use App\Http\Controllers\Panel\ActivoController;
use App\Http\Controllers\Panel\Autenticacion\AccesoController;
use App\Http\Controllers\Panel\BitacoraController;
use App\Http\Controllers\Panel\ColaPrioridadController;
use App\Http\Controllers\Panel\EvaluacionController;
use App\Http\Controllers\Panel\EvidenciaController;
use App\Http\Controllers\Panel\ModeloController;
use App\Http\Controllers\Panel\TacticaController;
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
    Route::put('/incidentes/{id}/seguimiento', [IncidenteController::class, 'seguimiento'])->name('incidentes.seguimiento');

    // Evaluación multicriterio y priorización automática
    Route::post('/incidentes/{incidente}/evaluaciones', [EvaluacionController::class, 'guardar'])->name('evaluaciones.guardar');
    Route::get('/cola-prioridad', [ColaPrioridadController::class, 'listar'])->name('cola.listar');

    // Evidencias
    Route::post('/incidentes/{incidente}/evidencias', [EvidenciaController::class, 'guardar'])->name('evidencias.guardar');
    Route::get('/incidentes/{incidente}/evidencias/{id}/descargar', [EvidenciaController::class, 'descargar'])->name('evidencias.descargar');

    // Inventario de activos
    Route::get('/activos', [ActivoController::class, 'listar'])->name('activos.listar');
    Route::post('/activos', [ActivoController::class, 'guardar'])->name('activos.guardar');
    Route::match(['put', 'patch'], '/activos/{id}', [ActivoController::class, 'actualizar'])->name('activos.actualizar');
    Route::delete('/activos/{id}', [ActivoController::class, 'eliminar'])->name('activos.eliminar');

    // Configuración del modelo de priorización
    Route::get('/modelo', [ModeloController::class, 'configuracion'])->name('modelo.configuracion');
    Route::post('/modelo/configuraciones', [ModeloController::class, 'guardarConfiguracion'])->name('modelo.configuraciones.guardar');
    Route::post('/modelo/configuraciones/{id}/activar', [ModeloController::class, 'activar'])->name('modelo.configuraciones.activar');
    Route::post('/modelo/recalcular', [ModeloController::class, 'recalcular'])->name('modelo.recalcular');
    Route::get('/modelo/escalas', [ModeloController::class, 'escalas'])->name('modelo.escalas');
    Route::put('/modelo/escalas/{id}', [ModeloController::class, 'actualizarEscala'])->name('modelo.escalas.actualizar');
    Route::get('/modelo/ahp', [ModeloController::class, 'ahp'])->name('modelo.ahp');
    Route::post('/modelo/ahp/calcular', [ModeloController::class, 'calcularAhp'])->name('modelo.ahp.calcular');
    Route::get('/modelo/sensibilidad', [ModeloController::class, 'sensibilidad'])->name('modelo.sensibilidad');

    // Catálogo de tácticas ATT&CK
    Route::get('/tacticas', [TacticaController::class, 'listar'])->name('tacticas.listar');
    Route::post('/tacticas', [TacticaController::class, 'guardar'])->name('tacticas.guardar');
    Route::match(['put', 'patch'], '/tacticas/{id}', [TacticaController::class, 'actualizar'])->name('tacticas.actualizar');
    Route::delete('/tacticas/{id}', [TacticaController::class, 'eliminar'])->name('tacticas.eliminar');

    // Bitácora de acciones
    Route::get('/bitacora', [BitacoraController::class, 'listar'])->name('bitacora.listar');

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
