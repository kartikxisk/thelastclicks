<?php

namespace App\Models;

use App\Invoicing\StateCodes;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /**
     * A GSTIN is uppercase by definition, and so are a PAN and an IFSC.
     *
     * Normalised here rather than only on the form, because nothing else can
     * guarantee it. The Gstin rule uppercases a local copy for its own checks —
     * which is what lets a pasted lowercase GSTIN be accepted instead of
     * failing with "format is invalid", the wrong problem — but a ValidationRule
     * has no channel to change what is persisted. That left the two
     * dehydrateStateUsing() closures on the Filament forms as the only thing
     * normalising storage, so an importer, an artisan command or a
     * hand-written seeder would pass validation and persist a lowercase GSTIN.
     * That is the number filed in GSTR-1.
     *
     * Blank becomes null rather than '': `filled()` is how the rest of this
     * model asks whether a registration exists, and an empty string printed
     * into an invoice header is a stray label with nothing after it.
     *
     * @return Attribute<string|null, string|null>
     */
    protected function gstin(): Attribute
    {
        return Attribute::make(set: self::upper(...));
    }

    /** @return Attribute<string|null, string|null> */
    protected function pan(): Attribute
    {
        return Attribute::make(set: self::upper(...));
    }

    /** @return Attribute<string|null, string|null> */
    protected function bankIfsc(): Attribute
    {
        return Attribute::make(set: self::upper(...));
    }

    private static function upper(?string $value): ?string
    {
        return filled($value) ? strtoupper(trim($value)) : null;
    }

    /**
     * The signature and the stamp are deliberately NOT on the media disk.
     *
     * MEDIA_DISK is an s3 bucket with CloudFront in front of it, serving the
     * public marketing site its imagery anonymously, and medialibrary's
     * DefaultPathGenerator writes to a guessable {media_id}/{file_name}. A
     * scanned signature and a company seal put there are anonymously fetchable
     * by anyone who counts upwards, and cached by the CDN indefinitely.
     *
     * ->visibility('private') does not fix it: ACLs are disabled on that bucket
     * and it is CloudFront, not the object ACL, that decides what is served —
     * SiteSettingsPage carries the same finding about the branding uploads.
     * Only a disk the CDN has no origin for does, which is config
     * `filesystems.billing_disk`.
     *
     * The logo stays public on purpose: it prints on every invoice and on the
     * public site, and is not a signing credential.
     */
    public function registerMediaCollections(): void
    {
        $privateDisk = (string) config('filesystems.billing_disk');

        // All three print on the PDF and all three are one-per-company: a second
        // signature file would render whichever medialibrary returned first.
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('signature')->singleFile()->useDisk($privateDisk);
        $this->addMediaCollection('stamp')->singleFile()->useDisk($privateDisk);
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
     *
     * Three attempts rather than the default one, because two concurrent
     * promotions deadlock on MySQL. The observer's demotion is
     * `UPDATE companies SET is_default = 0 WHERE id <> ?`, which InnoDB serves
     * with a PRIMARY range scan, so each transaction ends up holding rows the
     * other is waiting on and one of them is killed. The invariant survives
     * either way — the victim rolls back whole — but at one attempt the loser
     * reaches the admin as a 500 on a button that would have worked on a
     * retry. SQLite cannot produce that deadlock, so no test here sees it.
     */
    public function makeDefault(): void
    {
        DB::transaction(function (): void {
            $this->forceFill(['is_default' => true, 'is_active' => true])->save();
        }, 3);
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
