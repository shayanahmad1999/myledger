<?php

namespace App\Models;

use App\Enums\RecurringMode;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RecurringTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'currency_id',
        'source_account_id',
        'destination_account_id',
        'category_id',
        'person_id',
        'type',
        'title',
        'amount',
        'frequency',
        'interval',
        'next_run_at',
        'mode',
        'is_active',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'mode' => RecurringMode::class,
            'amount' => 'decimal:4',
            'interval' => 'integer',
            'next_run_at' => 'datetime',
            'is_active' => 'boolean',
            'payload' => 'array',
        ];
    }

    public function scopeForUser(Builder $q, int $userId): Builder
    {
        return $q->where('user_id', $userId);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
