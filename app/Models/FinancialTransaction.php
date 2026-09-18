<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'currency_id',
        'source_account_id',
        'destination_account_id',
        'category_id',
        'person_id',
        'loan_id',
        'savings_goal_id',
        'recurring_transaction_id',
        'reversal_of_id',
        'type',
        'reference_no',
        'transaction_date',
        'amount',
        'original_amount',
        'original_currency_id',
        'exchange_rate',
        'description',
        'notes',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['type' => TransactionType::class, 'transaction_date' => 'date', 'amount' => 'decimal:4', 'original_amount' => 'decimal:4', 'exchange_rate' => 'decimal:8', 'metadata' => 'array'];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TransactionEntry::class);
    }

    public function sourceAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'source_account_id');
    }

    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'destination_account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function scopeForUser(Builder $q, int $userId): Builder
    {
        return $q->where('user_id', $userId);
    }
}
