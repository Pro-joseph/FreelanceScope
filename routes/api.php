<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'admin'])
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/freelances', [AdminController::class, 'freelances']);
        Route::post('/freelances', [AdminController::class, 'storeFreelance']);
        Route::put('/freelances/{user}', [AdminController::class, 'updateFreelance']);
        Route::patch('/freelances/{user}/statut', [AdminController::class, 'toggleStatut']);
        Route::delete('/freelances/{user}', [AdminController::class, 'destroyFreelance']);
    });
