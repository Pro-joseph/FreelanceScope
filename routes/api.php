<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DevisController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\FreelanceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFeatureController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — aligned with Angular client expectations
|--------------------------------------------------------------------------
|
| All routes match the URL structure expected by FreelanceScope_Angular.
| Auth routes are under /auth, devis routes are nested under /projects,
| and features have a nested update route.
|
*/

// ─── Public Auth Routes ───────────────────────────────────────────────

Route::prefix('auth')->middleware('guest')->group(function () {
    Route::post('/register', [ApiAuthController::class, 'register']);
    Route::post('/login', [ApiAuthController::class, 'login']);
    Route::post('/forgot-password', [ApiAuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [ApiAuthController::class, 'resetPassword']);
});

// ─── Authenticated Routes ─────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/auth/me', function (Request $request) {
        return $request->user();
    });
    Route::post('/auth/logout', [ApiAuthController::class, 'logout']);

    // Clients
    Route::apiResource('clients', ClientController::class);

    // Projects
    Route::apiResource('projects', ProjectController::class);

    // Project Features — nested for list/create, shallow for show/destroy
    Route::get('/projects/{project}/features', [ProjectFeatureController::class, 'index']);
    Route::post('/projects/{project}/features', [ProjectFeatureController::class, 'store']);
    Route::get('/features/{feature}', [ProjectFeatureController::class, 'show']);
    Route::put('/projects/{project}/features/{feature}', [ProjectFeatureController::class, 'update']);
    Route::delete('/features/{feature}', [ProjectFeatureController::class, 'destroy']);

    // Estimates
    Route::get('/features/{feature}/estimate', [EstimateController::class, 'show']);
    Route::put('/estimates/{estimate}', [EstimateController::class, 'update']);

    // AI Estimation & Analyses
    Route::post('/projects/{project}/ai-estimate', AIController::class);
    Route::get('/projects/{project}/ai-analyses', [AIController::class, 'analyses']);

    // Devis — nested under projects (matching Angular DevisService)
    Route::get('/projects/{project}/devis', [DevisController::class, 'index']);
    Route::post('/projects/{project}/devis', [DevisController::class, 'store']);
    Route::get('/projects/{project}/devis/{devis}', [DevisController::class, 'show']);
    Route::put('/projects/{project}/devis/{devis}', [DevisController::class, 'update']);
    Route::delete('/projects/{project}/devis/{devis}', [DevisController::class, 'destroy']);
    Route::get('/projects/{project}/devis/{devis}/pdf', [DevisController::class, 'download']);

    // Dashboard
    Route::get('/dashboard/stats', [FreelanceController::class, 'dashboard']);
});

// ─── Freelance Profile Routes ─────────────────────────────────────────

Route::prefix('freelance')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/profile', [FreelanceController::class, 'profile']);
        Route::put('/profile', [FreelanceController::class, 'updateProfile']);
        Route::get('/dashboard', [FreelanceController::class, 'dashboard']);
    });

// ─── Admin Routes ─────────────────────────────────────────────────────

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'admin'])
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/freelances', [AdminController::class, 'freelances']);
        Route::post('/freelances', [AdminController::class, 'storeFreelance']);
        Route::get('/freelances/{user}', [AdminController::class, 'showFreelance']);
        Route::put('/freelances/{user}', [AdminController::class, 'updateFreelance']);
        Route::patch('/freelances/{user}/statut', [AdminController::class, 'toggleStatut']);
        Route::delete('/freelances/{user}', [AdminController::class, 'destroyFreelance']);
    });
