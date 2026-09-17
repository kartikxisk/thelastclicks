<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 50;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Textarea::make('quote')->required()->rows(4)->columnSpanFull(),
            Section::make()->columns(2)->schema([
                TextInput::make('client_name')->required(),
                TextInput::make('role_company')->label('Role / company'),
                Select::make('industry_id')->relationship('industry', 'title')->preload()
                    ->helperText('Shown on this industry page as well as the homepage.'),
                TextInput::make('order')->numeric()->default(0),
            ]),
            Toggle::make('is_published')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Client name + published state pick the row; the quote text and
                // its industry are read on the edit screen, not scanned for here.
                TextColumn::make('order')->sortable()->visibleFrom('md'),
                TextColumn::make('client_name')->searchable()->sortable(),
                TextColumn::make('quote')->limit(60)->wrap()->visibleFrom('md'),
                TextColumn::make('industry.title')->sortable()->visibleFrom('md'),
                IconColumn::make('is_published')->boolean(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
