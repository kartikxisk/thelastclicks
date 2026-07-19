<?php

namespace App\Filament\Pages;

use App\Models\Portfolio;
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
            'seo_default_title' => SiteSetting::get('seo_default_title'),
            'seo_default_description' => SiteSetting::get('seo_default_description'),
            'home_strip' => SiteSetting::get('home_strip', []),
            'hero_videos' => SiteSetting::get('hero_videos', []),
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
                        ]),
                    Forms\Components\Tabs\Tab::make('SEO')
                        ->schema([
                            Forms\Components\TextInput::make('seo_default_title'),
                            Forms\Components\Textarea::make('seo_default_description')->rows(3),
                        ]),
                    Forms\Components\Tabs\Tab::make('Homepage')
                        ->schema([
                            Forms\Components\Repeater::make('home_strip')
                                ->label('Film strip')
                                ->schema([
                                    Forms\Components\Select::make('portfolio_slug')
                                        ->label('Portfolio')
                                        ->options(fn () => Portfolio::published()->orderBy('title')->pluck('title', 'slug')->all())
                                        ->searchable()
                                        ->required(),
                                    Forms\Components\TextInput::make('tag')->required(),
                                    Forms\Components\TextInput::make('title')
                                        ->helperText('HTML allowed, e.g. Indian <em>Navy.</em>')
                                        ->required(),
                                    Forms\Components\TextInput::make('meta')->required(),
                                ])
                                ->columns(2)
                                ->reorderable()
                                ->defaultItems(0),
                            Forms\Components\Repeater::make('hero_videos')
                                ->label('Hero background films (in order)')
                                ->simple(
                                    Forms\Components\Select::make('portfolio_slug')
                                        ->label('Portfolio')
                                        ->options(fn () => Portfolio::published()->orderBy('title')->pluck('title', 'slug')->all())
                                        ->searchable()
                                        ->required(),
                                )
                                ->reorderable()
                                ->defaultItems(0),
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
        ]);
        SiteSetting::set('seo_default_title', $data['seo_default_title'] ?? '');
        SiteSetting::set('seo_default_description', $data['seo_default_description'] ?? '');
        SiteSetting::set('home_strip', array_values($data['home_strip'] ?? []));
        SiteSetting::set('hero_videos', array_values($data['hero_videos'] ?? []));

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
