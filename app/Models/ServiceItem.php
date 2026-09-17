<?php

namespace App\Models;

use App\Invoicing\Money;
use Database\Factories\ServiceItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceItem extends Model
{
    /** @use HasFactory<ServiceItemFactory> */
    use HasFactory;

    /** @var list<string> */
    public const UNITS = ['project', 'day', 'hour', 'shoot', 'item'];

    /**
     * The GST slabs a rate-card row may carry, in basis points.
     *
     * A const rather than a literal on the form for the same reason as UNITS: a
     * plain ->options() Select adds no server-side rule, so the form has to
     * validate against a list, and one list is the only way the form and any
     * future writer cannot drift apart.
     *
     * @var array<int, string>
     */
    public const TAX_RATES_BPS = [0 => '0%', 500 => '5%', 1200 => '12%', 1800 => '18%', 2800 => '28%'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'rate_paise' => 'integer',
            'tax_rate_bps' => 'integer',
            // Cast like the other three integer columns. Without it a `sort` of
            // "1.5" reaches an unsignedInteger column as a string: MySQL rounds
            // it silently, SQLite stores 1.5, and the two disagree about the
            // order the rate card is in.
            'sort' => 'integer',
            'is_expense' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @param  Builder<ServiceItem>  $q */
    public function scopeActive(Builder $q): void
    {
        $q->where('is_active', true);
    }

    /**
     * Shared items plus the ones belonging to this company.
     *
     * @param  Builder<ServiceItem>  $q
     */
    public function scopeForCompany(Builder $q, ?int $companyId): void
    {
        $q->where(fn (Builder $inner) => $inner->whereNull('company_id')->orWhere('company_id', $companyId));
    }

    public function formattedRate(): string
    {
        return Money::format($this->rate_paise);
    }
}
