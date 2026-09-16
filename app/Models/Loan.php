<?php

namespace App\Models;

use App\Enums\LoanDirection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    protected $fillable = [
        'user_id','person_id','currency_id','ledger_account_id','direction','title','principal',
        'outstanding_principal','interest_rate','interest_type','start_date','due_date','status','notes',
    ];
    protected function casts(): array
    {
        return [
            'direction' => LoanDirection::class,
            'principal' => 'decimal:4', 'outstanding_principal' => 'decimal:4',
            'interest_rate' => 'decimal:4', 'start_date' => 'date', 'due_date' => 'date',
        ];
    }
    public function person(): BelongsTo { return $this->belongsTo(Person::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function ledgerAccount(): BelongsTo { return $this->belongsTo(LedgerAccount::class); }
    public function payments(): HasMany { return $this->hasMany(LoanPayment::class); }
    public function transactions(): HasMany { return $this->hasMany(FinancialTransaction::class); }
    public function scopeForUser(Builder $q, int $userId): Builder { return $q->where('user_id', $userId); }
}
