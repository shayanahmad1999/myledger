<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Committee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'currency_id',
        'name',
        'contribution_amount',
        'total_members',
        'total_pool_amount',
        'frequency',
        'start_date',
        'status',
        'my_role',
        'my_person_id',
        'notes',
    ];

    protected $casts = [
        'contribution_amount' => 'decimal:2',
        'total_pool_amount' => 'decimal:2',
        'start_date' => 'date',
    ];

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function myPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'my_person_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(CommitteeMember::class)->orderBy('slot_number');
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(CommitteeRound::class)->orderBy('round_number');
    }
}
