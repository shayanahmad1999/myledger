<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    protected $fillable = ['user_id','category_id','currency_id','period_start','period_end','amount','alert_percent','last_alert_level','is_active'];
    protected function casts(): array { return ['period_start'=>'date','period_end'=>'date','amount'=>'decimal:4','alert_percent'=>'integer','last_alert_level'=>'integer','is_active'=>'boolean']; }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function scopeForUser(Builder $q, int $userId): Builder { return $q->where('user_id', $userId); }
}
