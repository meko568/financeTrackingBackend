<?php

namespace App\Services;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    public static function getDashboard(int $userId): mixed
    {
        try {
            return Cache::remember("dashboard.{$userId}", 86400, function () use ($userId) {
                return app(DashboardController::class)->buildSummary($userId);
            });
        } catch (\Exception $e) {
            return app(DashboardController::class)->buildSummary($userId);
        }
    }

    public static function invalidateDashboard(int $userId): void
    {
        Cache::forget("dashboard.{$userId}");
    }

    public static function getTransactions(int $userId, array $filters): mixed
    {
        try {
            $filterKey = md5(serialize($filters));
            return Cache::remember("transactions.{$userId}.{$filterKey}", 86400, function () use ($userId, $filters) {
                return app(TransactionController::class)->buildQuery($userId, $filters);
            });
        } catch (\Exception $e) {
            return app(TransactionController::class)->buildQuery($userId, $filters);
        }
    }

    public static function invalidateTransactions(int $userId): void
    {
        try {
            $redis = Redis::connection();
            $keys = $redis->keys("*transactions.{$userId}*");
            if (!empty($keys)) {
                $redis->del($keys);
            }
        } catch (\Exception $e) {
            // Silently fail if Redis is unavailable
        }
    }

    public static function getCategories(int $userId): mixed
    {
        try {
            return Cache::remember("categories.{$userId}", 86400, function () use ($userId) {
                return Category::where(function($q) use ($userId) {
                    $q->where('is_default', true)
                      ->orWhere('user_id', $userId);
                })->get();
            });
        } catch (\Exception $e) {
            return Category::where(function($q) use ($userId) {
                $q->where('is_default', true)
                  ->orWhere('user_id', $userId);
            })->get();
        }
    }

    public static function invalidateCategories(int $userId): void
    {
        Cache::forget("categories.{$userId}");
    }

    public static function getBudgets(int $userId): mixed
    {
        try {
            return Cache::remember("budgets.{$userId}", 86400, function () use ($userId) {
                return app(\App\Http\Controllers\BudgetController::class)->buildQuery($userId);
            });
        } catch (\Exception $e) {
            return app(\App\Http\Controllers\BudgetController::class)->buildQuery($userId);
        }
    }

    public static function invalidateBudgets(int $userId): void
    {
        Cache::forget("budgets.{$userId}");
    }

    public static function invalidateAll(int $userId): void
    {
        self::invalidateDashboard($userId);
        self::invalidateTransactions($userId);
        self::invalidateCategories($userId);
        self::invalidateBudgets($userId);
    }
}
