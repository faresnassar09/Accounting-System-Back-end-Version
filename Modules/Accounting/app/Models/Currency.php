<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_base',
        'is_active',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'is_base'       => 'boolean',
        'is_active'     => 'boolean',
    ];

    /**
     * Convert an amount in this foreign currency to the company's base currency.
     */
    public function convertToBase(float $foreignAmount): float
    {
        return round($foreignAmount * (float) $this->exchange_rate, 4);
    }

    /**
     * Convert an amount in base currency into this foreign currency.
     */
    public function convertFromBase(float $baseAmount): float
    {
        $rate = (float) $this->exchange_rate;

        if ($rate <= 0) {
            return 0.0;
        }

        return round($baseAmount / $rate, 4);
    }

    /**
     * Retrieve the company's default base accounting currency.
     */
    public static function getBaseCurrency(): ?self
    {
        return static::where('is_base', true)->first();
    }

    /**
     * Retrieve the base currency code, defaulting to accounting config or USD.
     */
    public static function getBaseCurrencyCode(): string
    {
        return static::getBaseCurrency()?->code ?? config('accounting.currency', 'USD');
    }

    /**
     * Journal entries recorded under this currency.
     */
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'currency_code', 'code');
    }
}
