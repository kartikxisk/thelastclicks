<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->columns(2)->schema([
                TextInput::make('title')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
            ]),
            Textarea::make('hero_copy')->rows(2)->columnSpanFull(),
            RichEditor::make('body')->columnSpanFull(),
            SpatieMediaLibraryFileUpload::make('hero')
                ->collection('hero')
                ->image()
                ->columnSpanFull(),
            TextInput::make('order')->numeric()->default(0),
            TextInput::make('share')->label('Mix share (%)')->numeric()->minValue(0)->maxValue(100)
                ->helperText('Discipline % on the portfolio "mix of work" bars. Leave blank to hide.'),
            Section::make('Design content')
                ->description('Fields used by the design-restored front-end templates.')
                ->columns(2)
                ->schema([
                    TextInput::make('hero_headline')->label('Hero headline')->columnSpanFull(),
                    // Resolved by Service::heroUrl() via MediaUrl, which accepts a
                    // media-disk key (e.g. "industries/x.jpg") as well as an absolute
                    // URL — so no strict ->url() rule, which rejects the path form.
                    TextInput::make('hero_url')->label('Hero image path or URL')
                        ->helperText('A media-disk key (industries/x.jpg) or a full https:// URL. An uploaded hero above overrides it.'),
                    // featured_slug's input is gone. The column stays, but no view
                    // has ever read it — it was a stringly-typed pointer at one
                    // project, which is what the works relation below now does
                    // properly, for many, with referential integrity.
                    // The sectors this service claims to cover. Editorial rather
                    // than derived from the attached work: a service with nothing
                    // filed under it yet still covers its verticals.
                    Select::make('industries')
                        ->label('Industries this service covers')
                        ->relationship('industries', 'title')
                        ->multiple()
                        ->preload()
                        ->columnSpanFull()
                        ->helperText('Renders a link block on this page pointing at each industry.'),
                    Select::make('works')
                        ->label('Work on this page')
                        ->relationship('works', 'title')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull()
                        ->helperText('Projects shown in the work grid. Unpublished ones stay listed here but do not render on the page.'),
                    Fieldset::make('Proof')
                        ->columns(3)
                        ->schema([
                            TextInput::make('proof.count')->label('Count'),
                            TextInput::make('proof.label')->label('Label'),
                            TextInput::make('proof.sectors')->label('Sectors'),
                        ]),
                    Fieldset::make('Call to action')
                        ->columns(1)
                        ->schema([
                            TextInput::make('cta.title')->label('Title'),
                            Textarea::make('cta.copy')->label('Copy')->rows(2),
                            TextInput::make('cta.prefill')->label('Prefill'),
                        ]),
                    // Headings for the two blocks whose wording differs per service
                    // (brief -> delivery vs moodboard -> master vs ingest -> export).
                    // Leave a field empty and the page falls back to the default
                    // string in services/show.blade.php.
                    Fieldset::make('Section headings')
                        ->columns(1)
                        ->schema([
                            TextInput::make('sections.flow.title')->label('Flow heading')
                                ->helperText('Inline <em> renders as the accent. Uppercased by the design.'),
                            Textarea::make('sections.flow.lead')->label('Flow lead')->rows(2),
                            TextInput::make('sections.flow.note')->label('Flow note')
                                ->helperText('Closing line under the phases. Defaults to "Your timeline is fixed in the quote, once we know the scope."'),
                            TextInput::make('sections.work.lead')->label('Work eyebrow')
                                ->helperText('Defaults to "Selected work".'),
                            TextInput::make('sections.work.title')->label('Work heading')
                                ->helperText('Inline <em> renders as the accent. Defaults to "The <em>output.</em>".'),
                            TextInput::make('sections.kit.title')->label('Arsenal heading')
                                ->helperText('Inline <em> renders as the accent. Uppercased by the design.'),
                            Textarea::make('sections.kit.lead')->label('Arsenal lead')->rows(2),
                        ]),
                    Repeater::make('hero_meta')
                        ->label('Hero meta')
                        ->columnSpanFull()
                        ->columns(2)
                        ->schema([
                            TextInput::make('label'),
                            TextInput::make('value'),
                        ]),
                    Repeater::make('pillars')
                        ->columnSpanFull()
                        ->schema([
                            TextInput::make('title'),
                            Textarea::make('desc')->rows(2),
                        ]),
                    // No duration field. A per-stage figure is a commitment the studio
                    // cannot make before it knows the scope — the same five stages run
                    // over a single-day product shoot and a multi-unit campaign — so
                    // the page states the sequence and leaves dates to the quote.
                    Repeater::make('phases')
                        ->columnSpanFull()
                        ->columns(2)
                        ->schema([
                            TextInput::make('num')->label('No.'),
                            TextInput::make('title'),
                            Textarea::make('desc')->rows(2)->columnSpanFull(),
                        ]),
                    Repeater::make('kit')
                        ->columnSpanFull()
                        ->schema([
                            TextInput::make('title'),
                            TagsInput::make('items')->helperText('Flat list of kit items.'),
                        ]),
                    Repeater::make('faqs')
                        ->label('FAQs')
                        ->columnSpanFull()
                        ->schema([
                            TextInput::make('q')->label('Question'),
                            Textarea::make('a')->label('Answer')->rows(2),
                        ]),
                    TagsInput::make('tags')
                        ->helperText('Flat list of tags.')
                        ->columnSpanFull(),
                    TagsInput::make('gallery_urls')
                        ->label('Gallery URLs')
                        ->helperText('Flat list of image URLs.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')->sortable(),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('slug')->searchable(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
