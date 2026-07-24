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
                            Forms\Components\FileUpload::make('brand_logo')
                                ->label('Brand logo')
                                ->image()
                                ->directory('branding')
                                ->helperText('Shown in the header, preloader and quote modal. Transparent PNG or SVG works best. Leave empty and no logo is shown anywhere.'),
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

        // FileUpload hands back a string path for single uploads, but can surface an
        // array mid-edit — normalise so the stored setting is always a plain path.
        $brandLogo = $data['brand_logo'] ?? '';
        if (is_array($brandLogo)) {
            $brandLogo = (string) (reset($brandLogo) ?: '');
        }
        SiteSetting::set('brand_logo', $brandLogo);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('Super-admin') ?? false;
    }
}
