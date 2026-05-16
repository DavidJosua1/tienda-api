<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PasswordResetController;

// Rutas públicas (sin token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password',  [PasswordResetController::class, 'resetPassword']);

// Rutas protegidas (requieren token JWT)
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);
    Route::put('/update-location', [AuthController::class, 'updateLocation']);

    // CRUD Productos
    Route::get('/productos',          [ProductoController::class, 'index']);
    Route::post('/productos',         [ProductoController::class, 'store']);
    Route::get('/productos/{id}',     [ProductoController::class, 'show']);
    Route::put('/productos/{id}',     [ProductoController::class, 'update']);
    Route::delete('/productos/{id}',  [ProductoController::class, 'destroy']);
});
