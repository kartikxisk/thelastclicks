<?php

namespace App\Models;

use App\Invoicing\StateCodes;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * One of our legal entities. Exactly one is the default, which is what a new
 * invoice prefills from — see CompanyObserver for how that invariant is held.
 */
class Company extends Model implements HasMedia
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, InteractsWithMedia;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_gst_registered' => 'boolean',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'lut_valid_till' => 'date',
            'default_payment_terms_days' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        // All three print on the PDF and all three are one-per-company: a second
        // signature file would render whichever medialibrary returned first.
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('signature')->singleFile();
        $this->addMediaCollection('stamp')->singleFile();
    }

    /** @param  Builder<Company>  $q */
    public function scopeActive(Builder $q): void
    {
        $q->where('is_active', true);
    }

    /**
     * Rate-card rows scoped to this company.
     *
     * Shared rows carry a null `company_id` and belong to no company, so they
     * are not part of this. `service_items.company_id` is cascadeOnDelete, which
     * makes this also the count of what a delete takes with it.
     *
     * @return HasMany<ServiceItem, $this>
     */
    public function serviceItems(): HasMany
    {
        return $this->hasMany(ServiceItem::class);
    }

    /** The entity a new invoice starts from. */
    public static function default(): ?self
    {
        return self::query()->where('is_default', true)->where('is_active', true)->first();
    }

    /**
     * Promote this company, with the demotion of every other company folded
     * into the same transaction.
     *
     * MySQL has no partial unique index, so "exactly one default" cannot be a
     * database constraint — CompanyObserver::saved() is what demotes the rest,
     * triggered by the `is_default` flip below. Demoting here too, on top of
     * that, would be the same invariant encoded twice, and the two would
     * eventually diverge. Wrapping the save in a transaction is what makes the
     * observer's demotion atomic with this promotion — the save and the
     * `saved` event it fires both happen inside it.
     *
     * It force-fills `is_active` as well, so promoting an inactive company
     * reactivates it. That is deliberate rather than incidental: a default that
     * is not active is invisible to Company::default(), which filters on
     * is_active, and so is the same as having no default at all. The table's
     * "Make default" action is visible on inactive rows, so this is a reachable
     * path and not only an internal one.
     */
    public function makeDefault(): void
    {
        DB::transaction(function (): void {
            $this->forceFill(['is_default' => true, 'is_active' => true])->save();
        });
    }

    public function stateLabel(): ?string
    {
        return StateCodes::name($this->address_state_code);
    }

    /**
     * The address as printed lines, blanks removed.
     *
     * @return list<string>
     */
    public function addressLines(): array
    {
        $cityLine = trim(implode(', ', array_filter([$this->address_city, $this->address_state])));

        if ($cityLine !== '' && filled($this->address_postal_code)) {
            $cityLine .= ' '.$this->address_postal_code;
        }

        return array_values(array_filter([
            $this->address_line1,
            $this->address_line2,
            $cityLine === '' ? null : $cityLine,
        ], fn (?string $line): bool => filled($line)));
    }
}
