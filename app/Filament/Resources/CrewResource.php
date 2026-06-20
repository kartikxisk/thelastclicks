<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CrewResource\Pages;
use App\Models\Crew;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CrewResource extends Resource
{
    protected static ?string $model = Crew::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Site';

    protected static ?string $modelLabel = 'Crew Member';

    protected static ?string $pluralModelLabel = 'Crew';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->columns(2)->schema([
                TextInput::make('name')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                TextInput::make('role')->required(),
                TextInput::make('order')->numeric()->default(0),
            ]),
            RichEditor::make('bio')->columnSpanFull(),
            KeyValue::make('social_json')
                ->label('Social links')
                ->keyLabel('Platform')
                ->valueLabel('URL')
                ->columnSpanFull(),
            SpatieMediaLibraryFileUpload::make('headshot')
                ->collection('headshot')
                ->image()
                ->columnSpanFull(),
            Section::make('Design content')
                ->description('Fields used by the design-restored front-end templates.')
                ->columns(2)
                ->schema([
                    TextInput::make('tagline'),
                    TextInput::make('joined'),
                    TextInput::make('discipline'),
                    TextInput::make('city'),
                    TextInput::make('photo_url')->label('Photo URL')->url()->columnSpanFull(),
                    TagsInput::make('skills')
                        ->helperText('Flat list of skills.')
                        ->columnSpanFull(),
                    Repeater::make('credits')
                        ->helperText('Each row is a year / project / role triple.')
                        ->columnSpanFull()
                        ->columns(3)
                        ->schema([
                            TextInput::make('year')->label('Year'),
                            TextInput::make('project')->label('Project'),
                            TextInput::make('role')->label('Role'),
                        ])
                        // Stored shape is a numeric triple [year, project, role].
                        // Map numeric -> named keys on load so the schema can bind.
                        ->afterStateHydrated(function (Repeater $component, ?array $state): void {
                            if ($state === null) {
                                return;
                            }
                            $component->state(array_map(fn ($row) => [
                                'year' => $row[0] ?? ($row['year'] ?? null),
                                'project' => $row[1] ?? ($row['project'] ?? null),
                                'role' => $row[2] ?? ($row['role'] ?? null),
                            ], array_values($state)));
                        })
                        // Map named keys -> numeric triple on save to preserve shape.
                        ->dehydrateStateUsing(fn (?array $state): array => array_values(array_map(
                            fn ($row) => [$row['year'] ?? null, $row['project'] ?? null, $row['role'] ?? null],
                            $state ?? []
                        ))),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')->sortable(),
                SpatieMediaLibraryImageColumn::make('headshot')->collection('headshot')->circular(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('role'),
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
            'index' => Pages\ListCrew::route('/'),
            'create' => Pages\CreateCrew::route('/create'),
            'edit' => Pages\EditCrew::route('/{record}/edit'),
        ];
    }
}
