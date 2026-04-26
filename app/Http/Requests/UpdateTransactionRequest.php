<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'type' => ['sometimes', 'required', 'in:income,expense'],
            'description' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'transaction_date' => ['sometimes', 'required', 'date'],
            'is_recurring' => ['sometimes', 'boolean'],
            'recurring_frequency' => ['nullable', 'in:daily,weekly,monthly,yearly'],
            'recurring_end_date' => ['nullable', 'date', 'after_or_equal:transaction_date'],
            'next_due_date' => ['nullable', 'date', 'after_or_equal:transaction_date'],
        ];
    }
}
