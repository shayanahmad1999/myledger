<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingsAllocation extends Model
{
    protected $fillable = ['savings_goal_id', 'financial_transaction_id', 'direction', 'amount', 'allocated_at'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:4', 'allocated_at' => 'date'];
    }
}
