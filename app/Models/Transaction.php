<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'type',
        'description',
        'notes',
        'transaction_date',
        'is_recurring',
        'recurring_interval',
    ];

    protected $casts = [
        'type' => 'string',
        'recurring_interval' => 'string',
        'is_recurring' => 'boolean',
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForMonth($query, $month, $year)
    {
        return $query->whereMonth('transaction_date', $month)->whereYear('transaction_date', $year);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
