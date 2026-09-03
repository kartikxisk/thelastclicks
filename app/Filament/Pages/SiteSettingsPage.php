<?php

namespace App\Filament\Pages;

use App\Models\Quote;
use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SiteSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $title = 'Site Settings';

    protected static ?string $slug = 'site-settings';

    protected static string $view = 'filament.pages.site-settings';

    protected static ?string $navigationGroup = 'Site';

    protected static ?int $navigationSort = 10;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        // @phpstan-ignore-next-line — $form provided by InteractsWithForms trait via __get
        $this->form->fill([
            'contact_email' => SiteSetting::get('contact_email'),
            'contact_phone' => SiteSetting::get('contact_phone'),
            'whatsapp_url' => SiteSetting::get('whatsapp_url'),
            'meta_pixel_id' => SiteSetting::get('meta_pixel_id'),
            'ga_measurement_id' => SiteSetting::get('ga_measurement_id'),
            'address_street' => SiteSetting::get('address_street'),
            'address_locality' => SiteSetting::get('address_locality'),
            'address_region' => SiteSetting::get('address_region'),
            'address_postal_code' => SiteSetting::get('address_postal_code'),
            'address_country' => SiteSetting::get('address_country', 'IN'),
            'geo_latitude' => SiteSetting::get('geo_latitude'),
            'geo_longitude' => SiteSetting::get('geo_longitude'),
            'map_url' => SiteSetting::get('map_url'),
            'hours_days' => SiteSetting::get('hours_days', []),
            'hours_opens' => SiteSetting::get('hours_opens'),
            'hours_closes' => SiteSetting::get('hours_closes'),
            'service_areas' => SiteSetting::get('service_areas', []),
            'socials_instagram' => SiteSetting::get('socials')['instagram'] ?? null,
            'socials_youtube' => SiteSetting::get('socials')['youtube'] ?? null,
            'socials_facebook' => SiteSetting::get('socials')['facebook'] ?? null,
            'socials_linkedin' => SiteSetting::get('socials')['linkedin'] ?? null,
            'socials_x' => SiteSetting::get('socials')['x'] ?? null,
            'socials_behance' => SiteSetting::get('socials')['behance'] ?? null,
            'socials_pinterest' => SiteSetting::get('socials')['pinterest'] ?? null,
            'seo_default_title' => SiteSetting::get('seo_default_title'),
            'seo_default_description' => SiteSetting::get('seo_default_description'),
            'seo_default_og_image' => SiteSetting::get('seo_default_og_image'),
            'brand_logo' => SiteSetting::get('brand_logo'),
            'brand_logo_dark' => SiteSetting::get('brand_logo_dark'),
            'favicon' => SiteSetting::get('favicon'),
            'lead_sla_hours' => SiteSetting::get('lead_sla_hours', Quote::DEFAULT_SLA_HOURS),
            'page_image_about' => SiteSetting::get('page_image_about'),
            'page_image_contact' => SiteSetting::get('page_image_contact'),
            'page_image_works' => SiteSetting::get('page_image_works'),
            'page_image_blog' => SiteSetting::get('page_image_blog'),
            'page_image_industries' => SiteSetting::get('page_image_industries'),
            'page_image_about_body' => SiteSetting::get('page_image_about_body'),
            'page_image_testimonials' => SiteSetting::get('page_image_testimonials'),
            'hero_source' => SiteSetting::heroSource(),
            'work_tile_ratio' => SiteSetting::get('work_tile_ratio', SiteSetting::DEFAULT_WORK_TILE_RATIO),
            'cta_video' => SiteSetting::get('cta_video'),
        ]);
    }

    /** Weekday names, in the order schema.org and every human expects them. */
    public const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    /**
     * Studio address, pin, hours and service area.
     *
     * Local SEO depends on one address stated identically in every place it
     * appears. These keys already had a consumer — home.blade.php builds the
     * Organization address from them and deliberately hardcodes no fallback — but
     * no producer: they were in neither the seeder nor this form, so the homepage
     * emitted no address while the contact page hardcoded its own copy of the same
     * values. This tab is the missing producer.
     *
     * Split out of form() rather than inlined: that method was already at the
     * 150-line ceiling before this tab was added.
     */
    private static function locationTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Location')
            ->schema([
                Forms\Components\TextInput::make('address_street')
                    ->label('Street address')
                    ->helperText('Exactly as it reads on the Google Business Profile — a variant here becomes an inconsistent citation.'),
                Forms\Components\TextInput::make('address_locality')->label('City'),
                Forms\Components\TextInput::make('address_region')->label('State / region'),
                Forms\Components\TextInput::make('address_postal_code')->label('Postal code'),
                Forms\Components\TextInput::make('address_country')
                    ->label('Country code')
                    ->maxLength(2)
                    ->helperText('Two-letter ISO code, e.g. IN.'),
                Forms\Components\TextInput::make('geo_latitude')
                    ->label('Latitude')
                    ->helperText('Five decimal places minimum. Copy the pin from the Business Profile.'),
                Forms\Components\TextInput::make('geo_longitude')->label('Longitude'),
                Forms\Components\TextInput::make('map_url')
                    ->label('Google Maps URL')
                    ->url()
                    ->columnSpanFull(),
                Forms\Components\Select::make('hours_days')
                    ->label('Open days')
                    ->multiple()
                    ->options(array_combine(self::DAYS, self::DAYS)),
                Forms\Components\TimePicker::make('hours_opens')->label('Opens')->seconds(false),
                Forms\Components\TimePicker::make('hours_closes')->label('Closes')->seconds(false),
                Forms\Components\TagsInput::make('service_areas')
                    ->label('Service areas')
                    ->helperText('Named cities you actually serve. These become areaServed in schema — "India" matches nothing a local searcher types.')
                    ->columnSpanFull(),
            ]);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('settings')->tabs([
                    Forms\Components\Tabs\Tab::make('Contact')
                        ->schema([
                            Forms\Components\TextInput::make('contact_email')->email()->required(),
                            Forms\Components\TextInput::make('contact_phone')->required(),
                            Forms\Components\TextInput::make('whatsapp_url')->url(),
                        ]),
                    self::locationTab(),
                    Forms\Components\Tabs\Tab::make('Tracking')
                        ->schema([
                            Forms\Components\TextInput::make('meta_pixel_id')
                                ->label('Meta Pixel ID')
                                ->helperText('Digits only, from Events Manager. Leave blank to load no pixel at all.'),
                            Forms\Components\TextInput::make('ga_measurement_id')
                                ->label('GA4 measurement ID')
                                ->helperText('Format G-XXXXXXXXXX. Overrides the GA_MEASUREMENT_ID env var; blank falls back to it.'),
                            Forms\Components\Placeholder::make('tracking_note')
                                ->label('')
                                ->content('Both tags are held to the cookie banner — nothing is sent until a visitor accepts. Neither loads in local or testing, so dev traffic stays out of your reporting. An ID that is not in the expected format is ignored rather than printed into the page.')
                                ->columnSpanFull(),
                        ]),
                    Forms\Components\Tabs\Tab::make('Socials')
                        ->schema([
                            Forms\Components\TextInput::make('socials_instagram')->label('Instagram URL')->url(),
                            Forms\Components\TextInput::make('socials_youtube')->label('YouTube URL')->url(),
                            Forms\Components\TextInput::make('socials_facebook')->label('Facebook URL')->url(),
                            Forms\Components\TextInput::make('socials_linkedin')->label('LinkedIn URL')->url(),
                            Forms\Components\TextInput::make('socials_x')->label('X (Twitter) URL')->url(),
                            Forms\Components\TextInput::make('socials_behance')->label('Behance URL')->url(),
                            Forms\Components\TextInput::make('socials_pinterest')->label('Pinterest URL')->url(),
                        ]),
                    Forms\Components\Tabs\Tab::make('Leads')
                        ->schema([
                            Forms\Components\TextInput::make('lead_sla_hours')
                                ->label('Response promise (hours)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(168)
                                ->required()
                                ->helperText('A new lead is flagged overdue on the dashboard and pipeline after this many hours. The public pages promise 4 working hours.'),
                        ]),
                    Forms\Components\Tabs\Tab::make('Branding')
                        ->schema([
                            Forms\Components\Placeholder::make('current_brand_logo')
                                ->label('Currently live')
                                ->content(fn (): string => SiteSetting::brandLogoUrl() ?: 'No logo set'),
                            Forms\Components\Toggle::make('remove_brand_logo')
                                ->label('Remove the current brand logo')
                                ->helperText('Leave off to keep the existing logo. Uploading a new file replaces it.')
                                ->default(false),
                            Forms\Components\FileUpload::make('brand_logo')
                                ->label('Brand logo')
                                ->image()
                                ->directory('branding')
                                // ACLs are disabled on the bucket; public-read is rejected.
                                // CloudFront serves the object, so private is correct here.
                                ->visibility('private')
                                // The uploader otherwise XHRs the existing file to read its
                                // size. CloudFront sends no access-control-* headers, so the
                                // browser blocks it and FilePond hangs on "Waiting for size".
                                ->fetchFileInformation(false)
                                ->helperText('The LIGHT mark, for dark backgrounds — the whole public site. Shown in the header, preloader and quote modal. Transparent PNG or SVG works best. Leave empty and no logo is shown anywhere.'),

                            Forms\Components\Placeholder::make('current_brand_logo_dark')
                                ->label('Currently live (dark-ink)')
                                ->content(fn (): string => SiteSetting::brandLogoDarkUrl() ?: 'No dark-ink logo set'),
                            Forms\Components\Toggle::make('remove_brand_logo_dark')
                                ->label('Remove the dark-ink logo')
                                ->helperText('Leave off to keep the existing file. Uploading a new one replaces it.')
                                ->default(false),
                            Forms\Components\FileUpload::make('brand_logo_dark')
                                ->label('Brand logo — dark ink')
                                ->image()
                                ->directory('branding')
                                ->visibility('private')
                                ->fetchFileInformation(false)
                                ->helperText('The same mark in BLACK, for light backgrounds: the admin panel in light mode, print, anything embedded on white. Falls back to the light mark when empty.'),

                            Forms\Components\Placeholder::make('current_favicon')
                                ->label('Current favicon')
                                ->content(fn (): string => SiteSetting::faviconUrl()),
                            Forms\Components\Toggle::make('remove_favicon')
                                ->label('Reset the favicon to the bundled default')
                                ->helperText('Leave off to keep the existing favicon. Uploading a new file replaces it.')
                                ->default(false),
                            Forms\Components\FileUpload::make('favicon')
                                ->label('Favicon')
                                ->image()
                                ->directory('branding')
                                ->visibility('private')
                                ->fetchFileInformation(false)
                                ->helperText('Square PNG (512×512), SVG or ICO. Also used as the Apple touch icon. Falls back to the bundled favicon when empty.'),
                        ]),
                    Forms\Components\Tabs\Tab::make('SEO')
                        ->schema([
                            Forms\Components\TextInput::make('seo_default_title'),
                            Forms\Components\Textarea::make('seo_default_description')->rows(3),
                            // No ->url() rule. This field holds EITHER a pasted
                            // absolute URL or an upload key on the media disk
                            // ('headers/gear-camera-dark.jpg' is what the seeder
                            // puts there), and MediaUrl resolves both. The rule
                            // rejected the seeded key, so the whole Site Settings
                            // form failed validation on load and nothing on it
                            // could be saved — including fields nobody had touched.
                            Forms\Components\TextInput::make('seo_default_og_image')
                                ->label('Default social share image')
                                ->helperText('Fallback OG/Twitter image (1200×630 recommended) used when a page has none. A full https:// URL, or a path on the media disk.'),
                        ]),
                    Forms\Components\Tabs\Tab::make('Page images')
                        ->schema([
                            Forms\Components\Placeholder::make('page_images_note')
                                ->label('')
                                ->content('Decorative stills for the static page headers and the homepage Client stories band. Uploaded to S3/CloudFront. ~1800px wide works best. Leave empty for a plain dark header; Client stories falls back to its animated backdrop.'),
                            ...collect([
                                'about' => 'About — header',
                                'contact' => 'Contact — header',
                                'works' => 'Portfolio — header',
                                'blog' => 'Journal — header',
                                'industries' => 'Industries — header',
                                'about_body' => 'About — studio photo',
                                'testimonials' => 'Home — Client stories background',
                            ])->map(fn (string $label, string $key): Forms\Components\FileUpload => Forms\Components\FileUpload::make("page_image_{$key}")
                                ->label($label)
                                ->image()
                                ->directory('headers')
                                ->visibility('private')
                                ->fetchFileInformation(false))->values()->all(),
                        ]),

                    Forms\Components\Tabs\Tab::make('Portfolio display')
                        ->schema([
                            Forms\Components\Placeholder::make('hero_source_note')
                                ->label('')
                                ->content('What plays behind the homepage headline. Either way an empty source shows no background at all rather than a stand-in.'),
                            Forms\Components\Select::make('hero_source')
                                ->label('Homepage hero background')
                                ->options(SiteSetting::HERO_SOURCES)
                                ->default(SiteSetting::DEFAULT_HERO_SOURCE)
                                ->selectablePlaceholder(false)
                                ->native(false)
                                ->helperText('Featured work needs no uploads here: it reads the covers and preview videos of the first three featured projects, so featuring a project under Works is the whole workflow. Hero Slides gives you a separate set to upload and switch on by hand.'),

                            Forms\Components\Placeholder::make('work_ratio_note')
                                ->label('')
                                ->content('Shape of the floating tiles in the homepage portfolio field. Every tile uses the same ratio — mixed shapes read as a mistake rather than as a collage.'),
                            Forms\Components\Select::make('work_tile_ratio')
                                ->label('Tile aspect ratio')
                                ->options(SiteSetting::WORK_TILE_RATIOS)
                                ->default(SiteSetting::DEFAULT_WORK_TILE_RATIO)
                                ->selectablePlaceholder(false)
                                ->native(false)
                                ->helperText('Landscape suits film stills; square is the safest with mixed source material; portrait suits reels and social cuts.'),

                            Forms\Components\Placeholder::make('cta_video_note')
                                ->label('')
                                ->content(fn (): string => 'Currently live: '.SiteSetting::ctaVideoUrl()),
                            Forms\Components\Toggle::make('remove_cta_video')
                                ->label('Reset to the bundled clip')
                                ->helperText('Leave off to keep the current video. Uploading a new file replaces it.')
                                ->default(false),
                            Forms\Components\FileUpload::make('cta_video')
                                ->label('CTA background video')
                                ->acceptedFileTypes(['video/mp4', 'video/webm'])
                                ->directory('video')
                                ->visibility('private')
                                ->fetchFileInformation(false)
                                ->helperText('Plays behind the closing call-to-action on every page. Muted, looped and dark-scrimmed, so a quiet, low-contrast clip works best. Keep it short and well compressed — it is fetched on every page.'),
                        ]),
                ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        // @phpstan-ignore-next-line — $form provided by InteractsWithForms trait via __get
        $data = $this->form->getState();

        SiteSetting::set('contact_email', $data['contact_email']);
        SiteSetting::set('contact_phone', $data['contact_phone']);
        SiteSetting::set('whatsapp_url', $data['whatsapp_url'] ?? '');
        SiteSetting::set('meta_pixel_id', trim((string) ($data['meta_pixel_id'] ?? '')));
        SiteSetting::set('ga_measurement_id', strtoupper(trim((string) ($data['ga_measurement_id'] ?? ''))));

        // NAP. Every one of these is read by both the homepage Organization node
        // and the contact page's LocalBusiness node, so a change here moves both
        // at once — which is the entire point of storing them in one place.
        foreach (['address_street', 'address_locality', 'address_region', 'address_postal_code',
            'address_country', 'geo_latitude', 'geo_longitude', 'map_url',
            'hours_opens', 'hours_closes'] as $key) {
            SiteSetting::set($key, $data[$key] ?? '');
        }
        // Ordered by weekday rather than by click order: openingHoursSpecification
        // is read by machines, and "Sa, Mo, Tu" is a worse answer than "Mo…Sa".
        SiteSetting::set('hours_days', array_values(array_intersect(
            self::DAYS,
            (array) ($data['hours_days'] ?? [])
        )));
        SiteSetting::set('service_areas', array_values(array_filter((array) ($data['service_areas'] ?? []))));

        SiteSetting::set('socials', [
            'instagram' => $data['socials_instagram'] ?? null,
            'youtube' => $data['socials_youtube'] ?? null,
            'facebook' => $data['socials_facebook'] ?? null,
            'linkedin' => $data['socials_linkedin'] ?? null,
            'x' => $data['socials_x'] ?? null,
            'behance' => $data['socials_behance'] ?? null,
            'pinterest' => $data['socials_pinterest'] ?? null,
        ]);
        SiteSetting::set('seo_default_title', $data['seo_default_title'] ?? '');
        SiteSetting::set('seo_default_description', $data['seo_default_description'] ?? '');
        SiteSetting::set('seo_default_og_image', $data['seo_default_og_image'] ?? '');
        SiteSetting::set('lead_sla_hours', max(1, (int) ($data['lead_sla_hours'] ?? Quote::DEFAULT_SLA_HOURS)));

        $this->storeUpload('brand_logo', $data['brand_logo'] ?? '', (bool) ($data['remove_brand_logo'] ?? false));
        $this->storeUpload('brand_logo_dark', $data['brand_logo_dark'] ?? '', (bool) ($data['remove_brand_logo_dark'] ?? false));
        $this->storeUpload('favicon', $data['favicon'] ?? '', (bool) ($data['remove_favicon'] ?? false));

        // Guarded on the way in as well as on the way out: SiteSetting::heroSource()
        // falls back on read, but a junk row stored here would sit in the admin
        // showing a source the site is not actually using.
        $heroSource = $data['hero_source'] ?? SiteSetting::DEFAULT_HERO_SOURCE;
        SiteSetting::set('hero_source', isset(SiteSetting::HERO_SOURCES[$heroSource])
            ? $heroSource
            : SiteSetting::DEFAULT_HERO_SOURCE);

        // Guard the ratio: an unknown value would emit invalid CSS and collapse
        // every tile to zero height.
        $ratio = $data['work_tile_ratio'] ?? SiteSetting::DEFAULT_WORK_TILE_RATIO;
        SiteSetting::set('work_tile_ratio', isset(SiteSetting::WORK_TILE_RATIOS[$ratio])
            ? $ratio
            : SiteSetting::DEFAULT_WORK_TILE_RATIO);

        $this->storeUpload('cta_video', $data['cta_video'] ?? '', (bool) ($data['remove_cta_video'] ?? false));

        foreach (['about', 'contact', 'works', 'blog', 'industries', 'about_body', 'testimonials'] as $key) {
            $this->storeUpload("page_image_{$key}", $data["page_image_{$key}"] ?? '', false);
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    /**
     * Persist an upload-backed setting.
     *
     * An empty upload field is ambiguous: it means "removed" *and* "failed to
     * hydrate" — the latter is what happens whenever the media disk is
     * unreachable. Writing it back blindly would silently destroy the stored
     * file, so removal has to be asked for explicitly via the toggle.
     */
    protected function storeUpload(string $key, mixed $value, bool $remove): void
    {
        if ($remove) {
            SiteSetting::set($key, '');

            return;
        }

        // FileUpload hands back a string path for single uploads, but can surface
        // an array mid-edit — normalise so the stored setting is always a path.
        if (is_array($value)) {
            $value = reset($value) ?: '';
        }

        if ((string) $value !== '') {
            SiteSetting::set($key, (string) $value);
        }
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('Super-admin') ?? false;
    }
}
