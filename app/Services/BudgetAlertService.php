<?php

namespace App\Services;

use App\Jobs\SendBudgetAlertEmail;
use App\Models\Budget;
use App\Models\Transaction;
use App\Models\User;
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
        $user = User::find($userId);

        foreach ($budgets as $budget) {
            $spent = abs(Transaction::forUser($userId)
                ->byType('expense')
                ->where('category_id', $budget->category_id)
                ->forMonth($month, $year)
                ->sum('amount'));

            $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;

            if ($percentage >= 100) {
                $alertLevel = 'danger';
                $emailType = 'exceeded';
            } elseif ($percentage >= 80) {
                $alertLevel = 'warning';
                $emailType = 'warning';
            } else {
                continue;
            }

            $cacheKey = "budget_alert.{$userId}.{$budget->id}.{$month}.{$year}.{$alertLevel}";

            if (!Cache::has($cacheKey)) {
                Cache::put($cacheKey, true, $endOfMonth);

                // Dispatch email job with 5 second delay
                $emailCacheKey = "budget_email_{$emailType}_{$userId}_{$budget->id}_{$month}_{$year}";
                if (!Cache::has($emailCacheKey) && $user) {
                    Cache::put($emailCacheKey, true, $endOfMonth);
                    
                    SendBudgetAlertEmail::dispatch(
                        $user,
                        $budget->category?->name ?? 'Unknown',
                        $budget->category?->icon ?? '💰',
                        $spent,
                        $budget->amount,
                        $emailType,
                        $percentage,
                        config('app.url')
                    )->delay(now()->addSeconds(5))->onQueue('emails');
                }
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
