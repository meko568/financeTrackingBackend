<?php

namespace App\Http\Controllers;

use App\Http\Requests\AIChatRequest;
use App\Models\Transaction;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AIController extends Controller
{
    use ApiResponse;

    public function chat(AIChatRequest $request)
    {
        $user = $request->user();
        $month = now()->month;
        $year = now()->year;

        $transactions = Transaction::with('category')
            ->forUser($user->id)
            ->forMonth($month, $year)
            ->get();

        $income = $transactions->where('type', 'income')->sum('amount');
        $expenses = $transactions->where('type', 'expense')->sum('amount');

        $summary = [
            'month' => now()->format('F Y'),
            'total_income' => $income,
            'total_expenses' => $expenses,
            'net_balance' => $income - $expenses,
            'top_categories' => $transactions
                ->where('type', 'expense')
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
                ->take(3)
                ->all(),
            'recent_transactions' => $transactions->sortByDesc('transaction_date')->take(5)->map(function ($transaction) {
                return [
                    'date' => $transaction->transaction_date->toDateString(),
                    'category' => $transaction->category?->name,
                    'amount' => $transaction->amount,
                    'type' => $transaction->type,
                ];
            })->values()->all(),
        ];

        $language = $request->input('language', 'en');
        $arabicInstructions = $language === 'ar'
            ? " الرد يكون باللغة العربية الفصحى البسيطة. استخدم أرقاماً غربية (0123). كن ودوداً ومختصراً."
            : "";

        $systemMessage = "You are a personal finance advisor. The user's financial data is: " . json_encode($summary) . ". Give clear, actionable advice in a friendly tone." . $arabicInstructions;

        try {
            $response = Http::withToken(config('services.groq.key'))
                ->timeout(30)
                ->accept('application/json')
                ->post(config('services.groq.base_uri') . '/chat/completions', [
                    'model' => config('services.groq.model'),
                    'messages' => [
                        ['role' => 'system', 'content' => $systemMessage],
                        ['role' => 'user', 'content' => $request->message],
                    ],
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->error('Unable to reach Groq API. Please check your internet connection or try again later.', [], 502);
        }

        if ($response->failed()) {
            \Log::warning('Groq API error', ['status' => $response->status(), 'body' => $response->body()]);
            return $this->error('Groq API returned an error: ' . $response->body(), [], $response->status());
        }

        $reply = $response->json('choices.0.message.content', 'Sorry, I could not generate a response.');

        return $this->success(['reply' => $reply]);
    }
}
