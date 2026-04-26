<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'not_in:0'],
            'type' => ['required', 'in:income,expense'],
            'description' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'transaction_date' => ['required', 'date'],
            'is_recurring' => ['boolean'],
            'recurring_frequency' => ['nullable', 'in:daily,weekly,monthly,yearly'],
            'recurring_end_date' => ['nullable', 'date', 'after_or_equal:transaction_date'],
            'next_due_date' => ['nullable', 'date', 'after_or_equal:transaction_date'],
        ];
    }
}
