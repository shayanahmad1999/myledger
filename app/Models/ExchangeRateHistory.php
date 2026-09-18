<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRateHistory extends Model
{
    protected $table = 'exchange_rate_history';
    protected $fillable = ['currency_id', 'base_currency_id', 'rate_date', 'rate'];

    protected function casts(): array
    {
        return [
            'rate_date' => 'date',
            'rate' => 'decimal:8',
        ];
    }

    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function baseCurrency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency_id');
    }

    public static function getRate(int $currencyId, int $baseCurrencyId, string $date): float
    {
        $history = self::where('currency_id', $currencyId)
            ->where('base_currency_id', $baseCurrencyId)
            ->where('rate_date', '<=', $date)
            ->orderByDesc('rate_date')
            ->first();

        if ($history) {
            return (float) $history->rate;
        }

        // Fallback to current currency exchange_rate
        $currency = Currency::find($currencyId);
        return $currency ? (float) $currency->exchange_rate : 1.0;
    }

    public static function setRate(int $currencyId, int $baseCurrencyId, string $date, float $rate): self
    {
        return self::updateOrCreate(
            [
                'currency_id' => $currencyId,
                'base_currency_id' => $baseCurrencyId,
                'rate_date' => $date,
            ],
            ['rate' => $rate]
        );
    }
}
