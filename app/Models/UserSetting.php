<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = ['user_id','base_currency_id','theme','locale','daily_reminder_time','preferences'];
    protected function casts(): array { return ['preferences'=>'array']; }
}
