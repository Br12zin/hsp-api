<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ForgotPasswordController; // <-- IMPORTAR
use Illuminate\Http\Request;

// ===========================================
// ROTAS PÚBLICAS
// ===========================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ===========================================
// ROTAS DE RECUPERAÇÃO DE SENHA (NOVAS)
// ===========================================
Route::post('/forgot-password', [ForgotPasswordController::class, 'forgot']);
Route::post('/validate-token', [ForgotPasswordController::class, 'validateToken']);
Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);

// ===========================================
// ROTAS PROTEGIDAS (precisam de token)
// ===========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    
    // Rotas administrativas
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'listUsers']);
        Route::get('/users/{id}', [AdminController::class, 'getUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::post('/users/{id}/make-admin', [AdminController::class, 'makeAdmin']);
        Route::get('/stats', [AdminController::class, 'stats']);
    });
});