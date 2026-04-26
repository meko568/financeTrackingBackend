<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Services\BudgetAlertService;
use App\Services\CacheService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();
        $filters = array_merge($request->only(['type', 'category_id', 'month', 'year', 'date_from', 'date_to']), ['page' => $request->query('page', 1)]);
        $payload = CacheService::getTransactions($user->id, $filters);

        return $this->success(['transactions' => $payload]);
    }

    public function buildQuery(int $userId, array $filters): array
    {
        $query = Transaction::with('category')->forUser($userId);

        if (isset($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['month']) && isset($filters['year'])) {
            $query->forMonth($filters['month'], $filters['year']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('transaction_date', 'desc')->paginate(15)->toArray();
    }

    public function store(StoreTransactionRequest $request)
    {
        $validated = $request->validated();

        if (!empty($validated['is_recurring']) && empty($validated['next_due_date'])) {
            $validated['next_due_date'] = $validated['transaction_date'];
        }

        $transaction = Transaction::create(array_merge($validated, [
            'user_id' => $request->user()->id,
        ]));

        $userId = $request->user()->id;
        CacheService::invalidateDashboard($userId);
        CacheService::invalidateTransactions($userId);
        CacheService::invalidateBudgets($userId);

        app(BudgetAlertService::class)->checkAlerts($userId);

        return $this->success(['transaction' => $transaction], 'Transaction created', 201);
    }

    public function show(Transaction $transaction, Request $request)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return $this->error('Transaction not found.', [], 404);
        }

        $transaction->load('category');

        return $this->success(['transaction' => $transaction]);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return $this->error('Transaction not found.', [], 404);
        }

        $validated = $request->validated();

        if (!empty($validated['is_recurring']) && empty($validated['next_due_date']) && empty($transaction->next_due_date)) {
            $validated['next_due_date'] = $validated['transaction_date'] ?? $transaction->transaction_date;
        }

        $transaction->update($validated);

        $userId = $request->user()->id;
        CacheService::invalidateDashboard($userId);
        CacheService::invalidateTransactions($userId);

        return $this->success(['transaction' => $transaction], 'Transaction updated');
    }

    public function destroy(Transaction $transaction, Request $request)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return $this->error('Transaction not found.', [], 404);
        }

        $transaction->delete();

        $userId = $request->user()->id;
        CacheService::invalidateDashboard($userId);
        CacheService::invalidateTransactions($userId);
        CacheService::invalidateBudgets($userId);

        return $this->success([], 'Transaction deleted');
    }
}
