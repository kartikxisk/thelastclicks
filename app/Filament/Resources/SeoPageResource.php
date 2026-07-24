<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeoPageResource\Pages;
use App\Models\SeoPage;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeoPageResource extends Resource
{
    protected static ?string $model = SeoPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationGroup = 'Site';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Manage SEO';

    protected static ?string $modelLabel = 'SEO page';

    protected static ?string $pluralModelLabel = 'Manage SEO';

    /**
     * Paths that exist today — offered as suggestions, but any path is allowed.
     *
     * @return list<string>
     */
    public static function knownPaths(): array
    {
        return [
            '/', '/about', '/our-works', '/industries', '/blog', '/contact',
            '/services/photography', '/services/videography', '/services/post-production',
            '/privacy-policy', '/terms-of-service', '/cookie-policy', '/disclaimer', '/thank-you',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Page')
                ->description('Matched against the exact page path. Any path works — including a blog post like /blog/my-post.')
                ->columns(2)
                ->schema([
                    TextInput::make('page_url')
                        ->label('Page URL')
                        ->required()
                        ->datalist(self::knownPaths())
                        ->helperText('Start with a slash. Home is just /')
                        ->rule('regex:/^\//')
                        ->unique(ignoreRecord: true)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (?string $state, Set $set) => $set('page_url', SeoPage::normalizePath($state))),
                    TextInput::make('label')
                        ->label('Admin label')
                        ->helperText('Only shown here, to find the row quickly. e.g. "Homepage"'),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->helperText('Turn off to fall back to the page\'s own SEO without deleting this row.'),
                ]),

            Section::make('Search result')
                ->columns(1)
                ->schema([
                    TextInput::make('title')
                        ->helperText('Aim for under ~60 characters. Leave blank to keep the page\'s own title.'),
                    Textarea::make('meta_description')
                        ->rows(3)
                        ->helperText('Aim for under ~160 characters.'),
                    TextInput::make('meta_keywords')
                        ->helperText('Comma separated. Google ignores these — only fill if another tool reads them.'),
                ]),

            Section::make('Social preview')
                ->description('Used for WhatsApp / LinkedIn / X link previews. Blank fields fall back to the title and description above.')
                ->columns(2)
                ->schema([
                    TextInput::make('og_title')->label('OG title'),
                    TextInput::make('og_image_url')
                        ->label('OG image URL')
                        ->url()
                        ->helperText('Takes priority over the upload below.'),
                    Textarea::make('og_description')->label('OG description')->rows(3)->columnSpanFull(),
                    FileUpload::make('og_image_path')
                        ->label('OG image upload')
                        ->image()
                        ->disk(config('media-library.disk_name', 'public'))
                        ->directory('seo')
                        ->columnSpanFull(),
                ]),

            Section::make('Indexing')
                ->columns(3)
                ->schema([
                    TextInput::make('canonical_url')
                        ->url()
                        ->columnSpan(3)
                        ->helperText('Leave blank to use the page\'s own canonical.'),
                    Toggle::make('noindex')
                        ->helperText('Hide from Google. Also drops the page from sitemap.xml.'),
                    Toggle::make('nofollow')
                        ->helperText('Tell crawlers not to follow links on this page.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('page_url')->label('Page URL')->searchable()->sortable(),
                TextColumn::make('label')->searchable()->toggleable(),
                TextColumn::make('title')->limit(45)->searchable()->wrap(),
                IconColumn::make('noindex')->boolean()->label('Noindex'),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('page_url')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeoPages::route('/'),
            'create' => Pages\CreateSeoPage::route('/create'),
            'edit' => Pages\EditSeoPage::route('/{record}/edit'),
        ];
    }
}
