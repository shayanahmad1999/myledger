<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['user_id','financial_transaction_id','loan_id','disk','path','original_name','mime_type','size'];
    protected function casts(): array { return ['size'=>'integer']; }
}
