<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $fillable = ['user_id','name','phone','email','notes'];
    public function loans(): HasMany { return $this->hasMany(Loan::class); }
    public function scopeForUser(Builder $q, int $userId): Builder { return $q->where('user_id', $userId); }
}
