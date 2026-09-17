<?php

namespace App\Models;

use App\Invoicing\StateCodes;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    /** The entity a new invoice starts from. */
    public static function default(): ?self
    {
        return self::query()->where('is_default', true)->where('is_active', true)->first();
    }

    /**
     * Promote this company and demote the rest, in one transaction.
     *
     * MySQL has no partial unique index, so "exactly one default" cannot be a
     * constraint — it has to be a code path, and this is the only one.
     */
    public function makeDefault(): void
    {
        DB::transaction(function (): void {
            static::query()->where('id', '!=', $this->id)->update(['is_default' => false]);
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
