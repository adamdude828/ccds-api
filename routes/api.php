<?php

use App\Http\Controllers\Auth\AzureRoleSyncController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VideoModeController;
use App\Http\Middleware\ValidateAzureToken;
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

// Protected routes that require Azure authentication
Route::middleware([ValidateAzureToken::class])->group(function () {
    // User info route
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Azure role sync
    Route::post('auth/azure/sync-roles', [AzureRoleSyncController::class, 'syncRoles']);


    // Subject routes
    Route::get('subjects', [SubjectController::class, 'index']);

    // Grade routes
    Route::get('grades', [GradeController::class, 'index']);

    // Video mode routes
    Route::get('modes', [VideoModeController::class, 'index']);

    // Video routes
    Route::get('videos', [VideoController::class, 'index']);
    Route::post('videos', [VideoController::class, 'store']);
    Route::put('videos/{video}', [VideoController::class, 'edit']);
    Route::delete('videos/{video}', [VideoController::class, 'delete']);
    Route::post('videos/upload_complete', [VideoController::class, 'UploadComplete']);
    Route::post('videos/mass_update', [VideoController::class, 'updateMeta']);

    // PDF routes
    Route::get('pdfs', [\App\Http\Controllers\PdfController::class, 'index']);
    Route::post('pdfs', [\App\Http\Controllers\PdfController::class, 'store']);
    Route::get('pdfs/{pdf}', [\App\Http\Controllers\PdfController::class, 'show']);
    Route::post('pdfs/{pdf}', [\App\Http\Controllers\PdfController::class, 'update']);
    Route::delete('pdfs/{pdf}', [\App\Http\Controllers\PdfController::class, 'destroy']);

    // Purge status route
    Route::get('purges/{id}', [\App\Http\Controllers\PurgeController::class, 'show']);

    // Legacy JWT auth routes (for backward compatibility during transition)
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});

// Public routes (no authentication required)
Route::group([
    'middleware' => 'api'
], function ($router) {
    // Public JWT login (for backward compatibility)
    Route::post('login', [AuthController::class, 'login']);
    
    // Public video viewing
    Route::get('videos/{video:uid}', [VideoController::class, 'view']);
    
    // Health check endpoint
    Route::get('health', [HealthController::class, 'check']);
}); 