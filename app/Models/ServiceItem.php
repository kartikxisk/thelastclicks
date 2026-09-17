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

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'rate_paise' => 'integer',
            'tax_rate_bps' => 'integer',
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
