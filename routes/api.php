<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DevisController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\FreelanceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFeatureController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('projects.features', ProjectFeatureController::class)->shallow();

    Route::get('/features/{feature}/estimate', [EstimateController::class, 'show']);
    Route::put('/estimates/{estimate}', [EstimateController::class, 'update']);

    Route::post('/projects/{project}/generate-estimate', AIController::class);

    Route::apiResource('devis', DevisController::class);
    Route::get('/devis/{devis}/pdf', [DevisController::class, 'download']);
});

Route::prefix('freelance')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/profile', [FreelanceController::class, 'profile']);
        Route::put('/profile', [FreelanceController::class, 'updateProfile']);
        Route::get('/dashboard', [FreelanceController::class, 'dashboard']);
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
