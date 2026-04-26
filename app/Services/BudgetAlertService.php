<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class BudgetAlertService
{
    public function checkAlerts(int $userId): array
    {
        $month = now()->month;
        $year = now()->year;
        $endOfMonth = Carbon::now()->endOfMonth();

        $budgets = Budget::with('category')
            ->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $alerts = [];

        foreach ($budgets as $budget) {
            $spent = Transaction::forUser($userId)
                ->byType('expense')
                ->where('category_id', $budget->category_id)
                ->forMonth($month, $year)
                ->sum('amount');

            $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;

            if ($percentage >= 100) {
                $alertLevel = 'danger';
            } elseif ($percentage >= 80) {
                $alertLevel = 'warning';
            } else {
                continue;
            }

            $cacheKey = "budget_alert.{$userId}.{$budget->id}.{$month}.{$year}.{$alertLevel}";

            if (!Cache::has($cacheKey)) {
                Cache::put($cacheKey, true, $endOfMonth);
            }

            $alerts[] = [
                'budget_id' => $budget->id,
                'category_name' => $budget->category?->name ?? 'Unknown',
                'category_icon' => $budget->category?->icon ?? '💰',
                'percentage_used' => round($percentage, 2),
                'amount_spent' => (float) $spent,
                'budget_limit' => (float) $budget->amount,
                'alert_level' => $alertLevel,
            ];
        }

        return $alerts;
    }
}
