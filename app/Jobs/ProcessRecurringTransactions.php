<?php

namespace App\Jobs;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRecurringTransactions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $today = Carbon::today();

        $recurringTransactions = Transaction::where('is_recurring', true)
            ->whereNotNull('next_due_date')
            ->whereNotNull('recurring_frequency')
            ->whereDate('next_due_date', '<=', $today)
            ->get();

        foreach ($recurringTransactions as $transaction) {
            $endDate = $transaction->recurring_end_date ? Carbon::parse($transaction->recurring_end_date) : null;

            if ($endDate && $today->greaterThan($endDate)) {
                $transaction->update(['is_recurring' => false, 'next_due_date' => null]);
                continue;
            }

            // Create a copy of the transaction for today
            $newTransaction = $transaction->replicate();
            $newTransaction->is_recurring = false;
            $newTransaction->recurring_frequency = null;
            $newTransaction->recurring_end_date = null;
            $newTransaction->next_due_date = null;
            $newTransaction->transaction_date = $today->toDateString();
            $newTransaction->created_at = now();
            $newTransaction->updated_at = now();
            $newTransaction->save();

            // Update next_due_date based on frequency
            $currentNextDue = Carbon::parse($transaction->next_due_date);
            switch ($transaction->recurring_frequency) {
                case 'daily':
                    $newNextDue = $currentNextDue->copy()->addDay();
                    break;
                case 'weekly':
                    $newNextDue = $currentNextDue->copy()->addWeek();
                    break;
                case 'monthly':
                    $newNextDue = $currentNextDue->copy()->addMonth();
                    break;
                case 'yearly':
                    $newNextDue = $currentNextDue->copy()->addYear();
                    break;
                default:
                    $newNextDue = $currentNextDue->copy()->addMonth();
            }

            // If the new next due date exceeds the end date, stop recurring
            if ($endDate && $newNextDue->greaterThan($endDate)) {
                $transaction->update([
                    'next_due_date' => null,
                    'is_recurring' => false,
                ]);
            } else {
                $transaction->update([
                    'next_due_date' => $newNextDue->toDateString(),
                ]);
            }
        }
    }
}
