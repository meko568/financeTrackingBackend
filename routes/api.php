<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetAlertController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Test route to verify database and redis connections
    Route::get('/test/connections', function () {
        try {
            $dbStatus = 'connected';
            $dbConnection = config('database.default');
            $dbName = config("database.connections.{$dbConnection}.database");

            $redisStatus = 'connected';
            \Illuminate\Support\Facades\Redis::connection()->ping();

            return response()->json([
                'success' => true,
                'database' => [
                    'status' => $dbStatus,
                    'connection' => $dbConnection,
                    'database' => $dbName,
                ],
                'redis' => [
                    'status' => $redisStatus,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    });

    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::get('/budgets', [BudgetController::class, 'index']);
    Route::post('/budgets', [BudgetController::class, 'store']);
    Route::get('/budgets/current-month', [BudgetController::class, 'getCurrentMonthSummary']);

    Route::post('/ai/chat', [AIController::class, 'chat']);

    Route::get('/budget-alerts', [BudgetAlertController::class, 'index']);
});
