<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    protected $fillable = ['loan_id','financial_transaction_id','principal_amount','interest_amount','paid_at'];
    protected function casts(): array { return ['principal_amount'=>'decimal:4','interest_amount'=>'decimal:4','paid_at'=>'date']; }
}
