<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Models\Budget;
use App\Models\Transaction;
use App\Services\CacheService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $budgets = Budget::with('category')
            ->where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->map(function (Budget $budget) use ($user, $month, $year) {
                $spent = abs(Transaction::forUser($user->id)
                    ->byType('expense')
                    ->where('category_id', $budget->category_id)
                    ->forMonth($month, $year)
                    ->sum('amount'));

                return [
                    'budget' => $budget,
                    'spent' => $spent,
                ];
            });

        return $this->success(['budgets' => $budgets]);
    }

    public function buildQuery(int $userId): array
    {
        $month = now()->month;
        $year = now()->year;

        $budgets = Budget::with('category')
            ->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->map(function (Budget $budget) use ($userId, $month, $year) {
                $spent = abs(Transaction::forUser($userId)
                    ->byType('expense')
                    ->where('category_id', $budget->category_id)
                    ->forMonth($month, $year)
                    ->sum('amount'));

                return [
                    'budget' => $budget,
                    'spent' => $spent,
                ];
            });

        return $budgets->toArray();
    }

    public function store(StoreBudgetRequest $request)
    {
        $budget = Budget::updateOrCreate([
            'user_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'month' => $request->month,
            'year' => $request->year,
        ], [
            'amount' => $request->amount,
        ]);

        $userId = $request->user()->id;
        CacheService::invalidateBudgets($userId);
        CacheService::invalidateDashboard($userId);

        return $this->success(['budget' => $budget], 'Budget saved', 201);
    }

    public function getCurrentMonthSummary(Request $request)
    {
        $user = $request->user();
        $month = now()->month;
        $year = now()->year;

        $summary = Budget::with('category')
            ->where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->map(function (Budget $budget) use ($user, $month, $year) {
                $spent = abs(Transaction::forUser($user->id)
                    ->byType('expense')
                    ->where('category_id', $budget->category_id)
                    ->forMonth($month, $year)
                    ->sum('amount'));

                $percent = $budget->amount > 0 ? min(round(($spent / $budget->amount) * 100), 150) : 0;
                $status = $percent >= 100 ? 'danger' : ($percent >= 80 ? 'warning' : 'ok');

                return [
                    'category' => $budget->category,
                    'budget_amount' => $budget->amount,
                    'spent_amount' => $spent,
                    'percentage' => $percent,
                    'status' => $status,
                ];
            });

        return $this->success(['summary' => $summary]);
    }

    public function destroy(Budget $budget, Request $request)
    {
        if ($budget->user_id !== $request->user()->id) {
            return $this->error('Budget not found.', [], 404);
        }

        $budget->delete();

        $userId = $request->user()->id;
        CacheService::invalidateBudgets($userId);
        CacheService::invalidateDashboard($userId);

        return $this->success([], 'Budget deleted');
    }
}
