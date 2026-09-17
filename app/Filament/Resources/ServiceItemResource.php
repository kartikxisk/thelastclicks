<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\RupeeInput;
use App\Filament\Resources\ServiceItemResource\Pages;
use App\Invoicing\Money;
use App\Models\Company;
use App\Models\ServiceItem;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceItemResource extends Resource
{
    protected static ?string $model = ServiceItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Rate card';

    protected static ?string $modelLabel = 'service item';

    // Without this the nav reads "Rate card" while the page heading and
    // breadcrumb read "Service Items" — two names for one screen.
    protected static ?string $pluralModelLabel = 'Rate card';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(255),
                Select::make('company_id')
                    ->label('Company')
                    ->options(fn (): array => Company::orderBy('name')->pluck('name', 'id')->all())
                    ->placeholder('Shared across all companies')
                    ->helperText('Leave blank unless this rate belongs to one entity.'),
                Textarea::make('description')->rows(2)->columnSpanFull()
                    ->helperText('Prefills the invoice line description.'),
                // A rate card is a price list, so a negative price is not one.
                // RupeeInput itself still admits a leading minus on purpose —
                // phase 2 needs negatives for discount and round-off lines — so
                // the constraint belongs here, at the call site.
                RupeeInput::make('rate_paise')->label('Rate')->required()
                    ->rule('regex:/^[0-9][0-9,]*\.?[0-9]{0,2}$/'),
                Select::make('unit')
                    ->options(array_combine(ServiceItem::UNITS, array_map(ucfirst(...), ServiceItem::UNITS)))
                    ->default('project')->required(),
                TextInput::make('sac_code')->label('SAC')->maxLength(8)->default('998383')
                    ->helperText('998383 is event photography and videography.'),
                Select::make('tax_rate_bps')
                    ->label('GST rate')
                    ->options([0 => '0%', 500 => '5%', 1200 => '12%', 1800 => '18%', 2800 => '28%'])
                    ->default(1800)->required(),
                Toggle::make('is_expense')
                    ->label('Reimbursable expense')
                    ->helperText('Groups this line under expenses on the invoice. Does not change the tax.'),
                // unsignedInteger column, so the floor is 0 rather than the 1
                // the payment-term fields use — 0 is the column default and the
                // top of the list. ->numeric() alone passes -1, which MySQL
                // refuses with error 1264 and SQLite stores silently.
                TextInput::make('sort')->numeric()->minValue(0)->default(0),
                Toggle::make('is_active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort')->sortable()->label('#'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('company.name')->label('Company')->placeholder('Shared'),
                TextColumn::make('rate_paise')->label('Rate')
                    ->formatStateUsing(fn (int $state): string => Money::format($state))
                    ->sortable(),
                TextColumn::make('unit'),
                TextColumn::make('sac_code')->label('SAC'),
                TextColumn::make('tax_rate_bps')->label('GST')
                    ->formatStateUsing(fn (int $state): string => ($state / 100).'%'),
                IconColumn::make('is_expense')->boolean()->label('Expense'),
                IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceItems::route('/'),
            'create' => Pages\CreateServiceItem::route('/create'),
            'edit' => Pages\EditServiceItem::route('/{record}/edit'),
        ];
    }
}
