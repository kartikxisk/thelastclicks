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
            'favicon' => SiteSetting::get('favicon'),
            'lead_sla_hours' => SiteSetting::get('lead_sla_hours', Quote::DEFAULT_SLA_HOURS),
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
                                ->helperText('Shown in the header, preloader and quote modal. Transparent PNG or SVG works best. Leave empty and no logo is shown anywhere.'),

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
                            Forms\Components\TextInput::make('seo_default_og_image')
                                ->label('Default social share image URL')
                                ->helperText('Fallback OG/Twitter image (1200×630 recommended) used when a page has none.')
                                ->url(),
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
        $this->storeUpload('favicon', $data['favicon'] ?? '', (bool) ($data['remove_favicon'] ?? false));

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
