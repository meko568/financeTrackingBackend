<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BudgetWarningMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $userName;
    public $categoryName;
    public $categoryIcon;
    public $amountSpent;
    public $budgetLimit;
    public $percentageUsed;
    public $remainingAmount;
    public $dashboardUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $userName,
        string $categoryName,
        string $categoryIcon,
        float $amountSpent,
        float $budgetLimit,
        float $percentageUsed,
        float $remainingAmount,
        string $dashboardUrl
    ) {
        $this->userName = $userName;
        $this->categoryName = $categoryName;
        $this->categoryIcon = $categoryIcon;
        $this->amountSpent = $amountSpent;
        $this->budgetLimit = $budgetLimit;
        $this->percentageUsed = $percentageUsed;
        $this->remainingAmount = $remainingAmount;
        $this->dashboardUrl = $dashboardUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⚠️ Budget Warning - You've used 80% of your {$this->categoryName} budget",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.budget-warning',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
