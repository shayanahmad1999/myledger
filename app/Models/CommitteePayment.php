<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'committee_round_id',
        'committee_member_id',
        'amount',
        'status',
        'paid_at',
        'account_id',
        'financial_transaction_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(CommitteeRound::class, 'committee_round_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(CommitteeMember::class, 'committee_member_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'account_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class, 'financial_transaction_id');
    }
}
