<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get(
    '/login',
    [\App\Http\Controllers\Admin\Auth\LoginController::class, 'showLoginForm']
)->name('login');

Route::post(
    '/login',
    [\App\Http\Controllers\Admin\Auth\LoginController::class, 'login']
);

Route::get(
    '/logout',
    [\App\Http\Controllers\Admin\Auth\LoginController::class, 'logout']
)->name('logout');

Route::group(['prefix'=>'admin'], function() {

    Route::resource(
        '/users',
        \App\Http\Controllers\Admin\UsersController::class,
    );

    Route::get(
        '/home',
        [\App\Http\Controllers\Admin\HomeController::class, 'index']
    )->name('admin.home');

    Route::resource(
        '/incidents',
        \App\Http\Controllers\Admin\IncidentController::class,
    )->only(['index', 'show', 'store', 'update', 'destroy']);

    Route::resource(
        '/incident-types',
        \App\Http\Controllers\Admin\IncidentTypeController::class,
    )->only(['index', 'store', 'update', 'destroy']);

    Route::get('/incidents/{incident}/comments', [\App\Http\Controllers\Admin\IncidentCommentController::class, 'index']);
    Route::post('/incidents/{incident}/comments', [\App\Http\Controllers\Admin\IncidentCommentController::class, 'store']);
    Route::delete('/incidents/{incident}/comments/{id}', [\App\Http\Controllers\Admin\IncidentCommentController::class, 'destroy']);

    Route::resource(
        '/role',
        \App\Http\Controllers\Admin\RoleController::class,
    );

    Route::resource(
        '/permission',
        \App\Http\Controllers\Admin\PermissionController::class,
    );

});
