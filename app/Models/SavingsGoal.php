<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SavingsGoal extends Model
{
    protected $fillable = ['user_id', 'account_id', 'currency_id', 'title', 'target_amount', 'allocated_amount', 'target_date', 'icon', 'color', 'is_completed'];

    protected function casts(): array
    {
        return ['target_amount' => 'decimal:4', 'allocated_amount' => 'decimal:4', 'target_date' => 'date', 'is_completed' => 'boolean'];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'account_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(SavingsAllocation::class);
    }

    public function scopeForUser(Builder $q, int $userId): Builder
    {
        return $q->where('user_id', $userId);
    }

    public function getProgressPercentAttribute(): float
    {
        $target = (float) $this->target_amount;
        return $target <= 0 ? 0 : min(100, round(((float) $this->allocated_amount / $target) * 100, 2));
    }
}
