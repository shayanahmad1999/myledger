<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function ledgerAccounts(): HasMany { return $this->hasMany(LedgerAccount::class); }
    public function transactions(): HasMany { return $this->hasMany(FinancialTransaction::class); }
    public function loans(): HasMany { return $this->hasMany(Loan::class); }
    public function settings(): HasOne { return $this->hasOne(UserSetting::class); }
}
