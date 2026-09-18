<?php

namespace App\Models;

use App\Enums\LedgerAccountKind;
use App\Enums\LedgerAccountType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LedgerAccount extends Model
{
    protected $fillable = [
        'user_id',
        'currency_id',
        'parent_id',
        'kind',
        'type',
        'name',
        'institution',
        'last_four',
        'icon',
        'color',
        'is_system',
        'include_in_net_worth',
        'is_archived',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'kind' => LedgerAccountKind::class,
            'type' => LedgerAccountType::class,
            'is_system' => 'boolean',
            'include_in_net_worth' => 'boolean',
            'is_archived' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TransactionEntry::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeMoneyAccounts(Builder $query): Builder
    {
        return $query
            ->where('kind', LedgerAccountKind::Asset->value)
            ->whereIn('type', array_map(fn($t) => $t->value, [
                LedgerAccountType::Cash,
                LedgerAccountType::Bank,
                LedgerAccountType::MobileWallet,
                LedgerAccountType::Savings,
                LedgerAccountType::Investment,
            ]))
            ->where('is_system', false);
    }

    public function balance(?string $asOf = null): float
    {
        $totals = $this
            ->entries()
            ->whereHas('transaction', fn($q) => $q->whereIn('status', ['posted', 'reversed'])->when($asOf, fn($qq) => $qq->whereDate('transaction_date', '<=', $asOf)))
            ->selectRaw('COALESCE(SUM(debit),0) debit_total, COALESCE(SUM(credit),0) credit_total')
            ->first();

        $debit = (float) ($totals->debit_total ?? 0);
        $credit = (float) ($totals->credit_total ?? 0);

        return in_array($this->kind, [LedgerAccountKind::Asset, LedgerAccountKind::Expense], true)
            ? $debit - $credit
            : $credit - $debit;
    }
}
