<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceLocationResource\Pages;
use App\Models\ServiceLocation;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceLocationResource extends Resource
{
    protected static ?string $model = ServiceLocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 35;

    protected static ?string $navigationLabel = 'City pages';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->columns(2)->schema([
                Select::make('service_id')->label('Service')->relationship('service', 'title')->required()->preload(),
                TextInput::make('city_slug')->required()->maxLength(32)->alphaDash()
                    ->helperText('Lowercase, e.g. delhi. Part of the URL — changing it breaks inbound links.')
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule, callable $get) => $rule->where('service_id', $get('service_id'))),
                TextInput::make('city_name')->required()->maxLength(64),
                TextInput::make('headline')->required()->helperText('The page H1.'),
            ]),
            RichEditor::make('intro')->required()->columnSpanFull(),
            Repeater::make('sections')->required()->columnSpanFull()->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['heading'] ?? null)
                ->schema([
                    TextInput::make('heading')->required(),
                    RichEditor::make('body')->required(),
                ]),
            TagsInput::make('highlights')->columnSpanFull()->placeholder('Add a fact, press Enter')
                ->nestedRecursiveRules(['string', 'max:70'])
                ->helperText('"At a glance" card beside the copy. Short facts, 70 characters each; leave empty to hide the card.'),
            Repeater::make('faqs')->label('Questions')->columnSpanFull()->collapsible()->collapsed()
                ->itemLabel(fn (array $state): ?string => $state['q'] ?? null)
                ->helperText('Shown as a visible Q&A after the sections. No FAQ schema is emitted.')
                ->schema([
                    TextInput::make('q')->label('Question')->required(),
                    RichEditor::make('a')->label('Answer')->required(),
                ]),
            TextInput::make('meta_title')->maxLength(255)->helperText('Lead only; the site name is appended.'),
            Textarea::make('meta_description')->maxLength(160)->rows(2)->helperText('Keep to 160 characters.'),
            Toggle::make('is_published')->default(true),
            TextInput::make('order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')->sortable()->visibleFrom('md'),
                TextColumn::make('headline')->searchable()->sortable(),
                TextColumn::make('service.title')->visibleFrom('md'),
                TextColumn::make('city_name')->visibleFrom('md'),
                IconColumn::make('is_published')->boolean(),
            ])
            ->defaultSort('order')
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceLocations::route('/'),
            'create' => Pages\CreateServiceLocation::route('/create'),
            'edit' => Pages\EditServiceLocation::route('/{record}/edit'),
        ];
    }
}
