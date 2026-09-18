<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class CommitteeRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'committee_id',
        'round_number',
        'due_date',
        'winner_member_id',
        'total_expected',
        'total_collected',
        'payout_amount',
        'payout_status',
        'payout_date',
        'payout_account_id',
        'payout_transaction_id',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'payout_date' => 'date',
        'total_expected' => 'decimal:2',
        'total_collected' => 'decimal:2',
        'payout_amount' => 'decimal:2',
    ];

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function winnerMember(): BelongsTo
    {
        return $this->belongsTo(CommitteeMember::class, 'winner_member_id');
    }

    public function payoutAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'payout_account_id');
    }

    public function payoutTransaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class, 'payout_transaction_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CommitteePayment::class);
    }
}
