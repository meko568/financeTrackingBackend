<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\CacheService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function summary(Request $request)
    {
        $user = $request->user();
        $summary = CacheService::getDashboard($user->id);

        return $this->success(['summary' => $summary]);
    }

    public function buildSummary(int $userId): array
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        $totalIncome = Transaction::forUser($userId)->byType('income')->sum('amount');
        $totalExpenses = Transaction::forUser($userId)->byType('expense')->sum('amount');

        $monthlyIncome = Transaction::forUser($userId)->byType('income')->forMonth($currentMonth, $currentYear)->sum('amount');
        $monthlyExpenses = Transaction::forUser($userId)->byType('expense')->forMonth($currentMonth, $currentYear)->sum('amount');

        // Calculate weekly savings (current week)
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        $weeklyIncome = Transaction::forUser($userId)
            ->byType('income')
            ->whereBetween('transaction_date', [$startOfWeek, $endOfWeek])
            ->sum('amount');
        $weeklyExpenses = Transaction::forUser($userId)
            ->byType('expense')
            ->whereBetween('transaction_date', [$startOfWeek, $endOfWeek])
            ->sum('amount');
        $weeklySavings = $weeklyIncome - abs($weeklyExpenses);

        $lastSixMonths = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $income = Transaction::forUser($userId)->byType('income')->forMonth($month->month, $month->year)->sum('amount');
            $expenses = Transaction::forUser($userId)->byType('expense')->forMonth($month->month, $month->year)->sum('amount');

            $lastSixMonths->push([
                'month' => $month->format('M'),
                'income' => $income,
                'expenses' => $expenses,
            ]);
        }

        $topCategories = Transaction::with('category')
            ->forUser($userId)
            ->byType('expense')
            ->forMonth($currentMonth, $currentYear)
            ->get()
            ->groupBy('category_id')
            ->map(function ($group) {
                return [
                    'category' => $group->first()->category?->name,
                    'icon' => $group->first()->category?->icon,
                    'amount' => $group->sum('amount'),
                ];
            })
            ->sortByDesc('amount')
            ->values()
            ->take(5);

        $recentTransactions = Transaction::with('category')
            ->forUser($userId)
            ->orderBy('transaction_date', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'date' => $transaction->transaction_date->toDateString(),
                    'category' => $transaction->category?->name,
                    'icon' => $transaction->category?->icon,
                    'description' => $transaction->description,
                    'amount' => $transaction->amount,
                    'type' => $transaction->type,
                ];
            });

        return [
            'total_balance' => $totalIncome - $totalExpenses,
            'monthly_income' => $monthlyIncome,
            'monthly_expenses' => $monthlyExpenses,
            'weekly_savings' => $weeklySavings,
            'last_6_months' => $lastSixMonths,
            'top_categories' => $topCategories,
            'recent_transactions' => $recentTransactions,
        ];
    }
}
