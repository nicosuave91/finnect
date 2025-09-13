<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\BorrowerController;
use App\Http\Controllers\Api\ComplianceController;
use App\Http\Controllers\Api\WorkflowController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\IntegrationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes
Route::middleware(['auth:sanctum', 'tenant', 'compliance'])->group(function () {
    
    // Authentication routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // Loan management routes
    Route::apiResource('loans', LoanController::class);
    Route::post('/loans/{loan}/status', [LoanController::class, 'updateStatus']);
    Route::post('/loans/{loan}/compliance-check', [LoanController::class, 'runComplianceCheck']);
    Route::get('/loans/{loan}/audit-trail', [LoanController::class, 'getAuditTrail']);
    Route::get('/loans/{loan}/workflow', [LoanController::class, 'getWorkflow']);

    // Borrower management routes
    Route::apiResource('borrowers', BorrowerController::class);
    Route::post('/borrowers/{borrower}/verify-identity', [BorrowerController::class, 'verifyIdentity']);
    Route::get('/borrowers/{borrower}/credit-report', [BorrowerController::class, 'getCreditReport']);

    // Compliance routes
    Route::prefix('compliance')->group(function () {
        Route::get('/regulations', [ComplianceController::class, 'getRegulations']);
        Route::get('/violations', [ComplianceController::class, 'getViolations']);
        Route::get('/audit-trail', [ComplianceController::class, 'getAuditTrail']);
        Route::post('/reports', [ComplianceController::class, 'generateReport']);
        Route::get('/reports/{report}/download', [ComplianceController::class, 'downloadReport']);
    });

    // Workflow management routes
    Route::prefix('workflows')->group(function () {
        Route::get('/', [WorkflowController::class, 'index']);
        Route::post('/', [WorkflowController::class, 'store']);
        Route::get('/{workflow}', [WorkflowController::class, 'show']);
        Route::put('/{workflow}', [WorkflowController::class, 'update']);
        Route::delete('/{workflow}', [WorkflowController::class, 'destroy']);
        Route::post('/{workflow}/steps', [WorkflowController::class, 'addStep']);
        Route::put('/{workflow}/steps/{step}', [WorkflowController::class, 'updateStep']);
        Route::delete('/{workflow}/steps/{step}', [WorkflowController::class, 'removeStep']);
    });

    // Document management routes
    Route::prefix('documents')->group(function () {
        Route::get('/', [DocumentController::class, 'index']);
        Route::post('/upload', [DocumentController::class, 'upload']);
        Route::get('/{document}', [DocumentController::class, 'show']);
        Route::put('/{document}', [DocumentController::class, 'update']);
        Route::delete('/{document}', [DocumentController::class, 'destroy']);
        Route::get('/{document}/download', [DocumentController::class, 'download']);
        Route::post('/{document}/verify', [DocumentController::class, 'verify']);
    });

    // Integration routes
    Route::prefix('integrations')->group(function () {
        Route::get('/', [IntegrationController::class, 'index']);
        Route::get('/{integration}/status', [IntegrationController::class, 'getStatus']);
        Route::post('/{integration}/sync', [IntegrationController::class, 'sync']);
        Route::get('/{integration}/logs', [IntegrationController::class, 'getLogs']);
    });

    // Dashboard and reporting routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats']);
        Route::get('/loans/summary', [DashboardController::class, 'getLoansSummary']);
        Route::get('/compliance/summary', [DashboardController::class, 'getComplianceSummary']);
        Route::get('/workflow/summary', [DashboardController::class, 'getWorkflowSummary']);
    });

    // Admin routes (require admin role)
    Route::middleware(['role:admin'])->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/users', [AdminController::class, 'getUsers']);
            Route::post('/users', [AdminController::class, 'createUser']);
            Route::put('/users/{user}', [AdminController::class, 'updateUser']);
            Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);
            Route::get('/tenants', [AdminController::class, 'getTenants']);
            Route::post('/tenants', [AdminController::class, 'createTenant']);
            Route::put('/tenants/{tenant}', [AdminController::class, 'updateTenant']);
            Route::delete('/tenants/{tenant}', [AdminController::class, 'deleteTenant']);
        });
    });
});

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
    ]);
});