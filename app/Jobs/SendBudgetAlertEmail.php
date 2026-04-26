<?php

namespace App\Jobs;

use App\Mail\BudgetExceededMail;
use App\Mail\BudgetWarningMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBudgetAlertEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [5, 10, 20];

    private $user;
    private $categoryName;
    private $categoryIcon;
    private $amountSpent;
    private $budgetLimit;
    private $type;
    private $percentageUsed;
    private $dashboardUrl;

    /**
     * Create a new job instance.
     */
    public function __construct(
        User $user,
        string $categoryName,
        string $categoryIcon,
        float $amountSpent,
        float $budgetLimit,
        string $type,
        ?float $percentageUsed = null,
        string $dashboardUrl = ''
    ) {
        $this->user = $user;
        $this->categoryName = $categoryName;
        $this->categoryIcon = $categoryIcon;
        $this->amountSpent = $amountSpent;
        $this->budgetLimit = $budgetLimit;
        $this->type = $type;
        $this->percentageUsed = $percentageUsed;
        $this->dashboardUrl = $dashboardUrl ?: config('app.url');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->user->email) {
            Log::warning("User {$this->user->id} has no email address, skipping budget alert email");
            return;
        }

        try {
            if ($this->type === 'warning') {
                $remainingAmount = $this->budgetLimit - $this->amountSpent;
                Mail::to($this->user->email)
                    ->send(new BudgetWarningMail(
                        $this->user->name ?? 'User',
                        $this->categoryName,
                        $this->categoryIcon,
                        $this->amountSpent,
                        $this->budgetLimit,
                        $this->percentageUsed,
                        $remainingAmount,
                        $this->dashboardUrl
                    ));
                Log::info("Budget warning email sent to user {$this->user->id} for category {$this->categoryName}");
            } elseif ($this->type === 'exceeded') {
                $exceededAmount = $this->amountSpent - $this->budgetLimit;
                Mail::to($this->user->email)
                    ->send(new BudgetExceededMail(
                        $this->user->name ?? 'User',
                        $this->categoryName,
                        $this->categoryIcon,
                        $this->amountSpent,
                        $this->budgetLimit,
                        $exceededAmount,
                        $this->dashboardUrl
                    ));
                Log::info("Budget exceeded email sent to user {$this->user->id} for category {$this->categoryName}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send budget alert email to user {$this->user->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
