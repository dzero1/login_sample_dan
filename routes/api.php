<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolePermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Private routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user/roles', [RolePermissionController::class, 'getRoles']);
    Route::get('/user/permissions', [RolePermissionController::class, 'getPermissions']);

    // Private routes only available for 'super-admin' and 'admin'
    Route::middleware(["role:{$_ENV['SUPER_ADMIN_ROLE']}|admin"])->group(function () {
        Route::get('/user/{id}/roles', [RolePermissionController::class, 'getRoles'])->where('id', '[0-9]+');
        Route::post('/user/{id}/role', [RolePermissionController::class, 'assignRole'])->where('id', '[0-9]+');
        Route::get('/user/{id}/permissions', [RolePermissionController::class, 'getPermissions'])->where('id', '[0-9]+');
        Route::post('/user/{id}/permission', [RolePermissionController::class, 'assignPermissions'])->where('id', '[0-9]+');
    });

    Route::middleware(["role:".$_ENV['SUPER_ADMIN_ROLE'] ])->group(function () {
        Route::delete('/user/{id}/role', [RolePermissionController::class, 'removeRole'])->where('id', '[0-9]+');
        Route::delete('/user/{id}/permission', [RolePermissionController::class, 'removePermission'])->where('id', '[0-9]+');
    });

});
