<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\NutricionistaController;
use App\Http\Controllers\PacienteController;

Route::post('/login', [AuthController::class , 'login'])->name('login');

// Public Empresas
Route::get('/empresas', [CompanyController::class , 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class , 'logout']);

    // Admin Empresas
    Route::post('/empresas', [CompanyController::class , 'store']);
});

Route::group(['prefix' => 'tenant', 'middleware' => [\App\Http\Middleware\CheckTenant::class]], function () {
    Route::get('/nutricionistas', [NutricionistaController::class , 'index']);
    Route::post('/nutricionistas', [NutricionistaController::class , 'store']);
    Route::put('/nutricionistas/{id}', [NutricionistaController::class , 'update']);
    Route::delete('/nutricionistas/{id}', [NutricionistaController::class , 'destroy']);

    Route::get('/pacientes', [PacienteController::class , 'index']);
    Route::post('/pacientes', [PacienteController::class , 'store']);
});

// User route for testing
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
