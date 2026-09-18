<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = ['user_id','base_currency_id','theme','locale','daily_reminder_time','preferences'];
    protected function casts(): array { return ['preferences'=>'array']; }
    public function user() { return $this->belongsTo(User::class); }
    public function currency() {  return $this->belongsTo(Currency::class, 'base_currency_id'); }
}
