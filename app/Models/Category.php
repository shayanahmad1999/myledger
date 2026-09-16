<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['user_id','ledger_account_id','parent_id','type','name','icon','color','is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function ledgerAccount(): BelongsTo { return $this->belongsTo(LedgerAccount::class); }
    public function parent(): BelongsTo { return $this->belongsTo(Category::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(Category::class, 'parent_id'); }
    public function scopeForUser(Builder $q, int $userId): Builder { return $q->where('user_id', $userId); }
}
