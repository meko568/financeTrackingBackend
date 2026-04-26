<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BudgetExceededMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $userName;
    public $categoryName;
    public $categoryIcon;
    public $amountSpent;
    public $budgetLimit;
    public $exceededAmount;
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
        float $exceededAmount,
        string $dashboardUrl
    ) {
        $this->userName = $userName;
        $this->categoryName = $categoryName;
        $this->categoryIcon = $categoryIcon;
        $this->amountSpent = $amountSpent;
        $this->budgetLimit = $budgetLimit;
        $this->exceededAmount = $exceededAmount;
        $this->dashboardUrl = $dashboardUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🚨 Budget Exceeded - You've gone over your {$this->categoryName} budget",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.budget-exceeded',
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
