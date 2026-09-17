<?php

namespace App\Models;

use App\Invoicing\StateCodes;
use Database\Factories\BillingClientFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingClient extends Model
{
    /** @use HasFactory<BillingClientFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'cc_emails' => 'array',
            'is_active' => 'boolean',
            'payment_terms_days' => 'integer',
        ];
    }

    /**
     * The logo-wall row, when this client is also public-facing.
     *
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @param  Builder<BillingClient>  $q */
    public function scopeActive(Builder $q): void
    {
        $q->where('is_active', true);
    }

    /** A GSTIN is what registration means — there is no second flag to disagree with. */
    public function isRegistered(): bool
    {
        return filled($this->gstin);
    }

    public function gstinStateCode(): ?string
    {
        return $this->isRegistered() ? substr((string) $this->gstin, 0, 2) : null;
    }

    /**
     * Where the supply lands, in precedence order: the explicit override, the
     * GSTIN's own state, then the billing address. Null is a valid answer —
     * the invoice form asks for one rather than guessing.
     *
     * Falls through on blank as well as null. A Filament Select saves an
     * untouched field as '', and `??` only treats null as absent, so a plain
     * `??` chain would return that blank override as-is instead of falling
     * through to the GSTIN or billing state — a silent blank that picks the
     * wrong CGST+SGST/IGST split.
     */
    public function placeOfSupplyStateCode(): ?string
    {
        foreach ([$this->place_of_supply_state_code, $this->gstinStateCode(), $this->billing_address_state_code] as $candidate) {
            if (filled($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function stateLabel(): ?string
    {
        return StateCodes::name($this->billing_address_state_code);
    }

    /** @return list<string> */
    public function addressLines(): array
    {
        $cityLine = trim(implode(', ', array_filter([$this->billing_address_city, $this->billing_address_state])));

        if ($cityLine !== '' && filled($this->billing_address_postal_code)) {
            $cityLine .= ' '.$this->billing_address_postal_code;
        }

        return array_values(array_filter([
            $this->billing_address_line1,
            $this->billing_address_line2,
            $cityLine === '' ? null : $cityLine,
        ], fn (?string $line): bool => filled($line)));
    }
}
