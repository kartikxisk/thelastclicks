<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillingClientResource\Pages;
use App\Invoicing\StateCodes;
use App\Models\BillingClient;
use App\Models\Client;
use App\Rules\Gstin;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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

class BillingClientResource extends Resource
{
    protected static ?string $model = BillingClient::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Clients';

    protected static ?string $modelLabel = 'billing client';

    // The nav says "Clients"; without this the heading and breadcrumb say
    // "Billing Clients". One screen, one name.
    protected static ?string $pluralModelLabel = 'Clients';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Identity')->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('legal_name')->maxLength(255)
                    ->helperText('The name on their GST registration, if it differs.'),
                TextInput::make('gstin')->label('GSTIN')->maxLength(15)
                    // No state argument here, unlike the company form: a client
                    // can be registered anywhere, and their GSTIN state is what
                    // decides the place of supply rather than contradicting it.
                    ->rules([new Gstin])
                    // GSTIN, PAN and IFSC are uppercase by definition, and a
                    // paste out of an email routinely is not. Failing that with
                    // "format is invalid" names the wrong problem: the characters
                    // are right, only the case is not.
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtoupper(trim($state)) : null)
                    ->helperText('Leave blank for an unregistered client.'),
                TextInput::make('pan')->label('PAN')->maxLength(10)
                    // GSTIN, PAN and IFSC are uppercase by definition, and a
                    // paste out of an email routinely is not. Failing that with
                    // "format is invalid" names the wrong problem: the characters
                    // are right, only the case is not.
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtoupper(trim($state)) : null),
                Select::make('client_id')
                    ->label('Logo-wall entry')
                    ->options(fn (): array => Client::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->helperText('Optional link to the public site client logo.'),
            ]),

            Section::make('Contact')->columns(2)->schema([
                TextInput::make('email')->email()->maxLength(255)
                    ->helperText('Where the invoice is sent.'),
                TagsInput::make('cc_emails')->label('CC')
                    ->helperText('Accounts, CA, anyone else who should receive it.'),
                TextInput::make('phone')->maxLength(50),
            ]),

            Section::make('Billing address')->columns(2)->schema([
                TextInput::make('billing_address_line1')->label('Address line 1')->maxLength(255),
                TextInput::make('billing_address_line2')->label('Address line 2')->maxLength(255),
                TextInput::make('billing_address_city')->label('City')->maxLength(255),
                Select::make('billing_address_state_code')
                    ->label('State')
                    ->options(StateCodes::options())
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (?string $state, callable $set) => $set('billing_address_state', StateCodes::name($state))),
                TextInput::make('billing_address_state')->label('State name')->maxLength(255),
                // Twelve, matching the column, and labelled for both: the
                // country field below says an address outside IN makes this an
                // export, and a UK postcode like SW1A 1AA is eight characters.
                TextInput::make('billing_address_postal_code')->label('PIN / postcode')->maxLength(12),
                TextInput::make('billing_address_country')->label('Country')->default('IN')->maxLength(2)
                    ->helperText('Anything other than IN makes the invoice an export.'),
            ]),

            Section::make('Billing defaults')->columns(2)->schema([
                Select::make('place_of_supply_state_code')
                    ->label('Place of supply')
                    ->options(StateCodes::options())
                    ->searchable()
                    ->helperText('Leave blank to use their GSTIN state, then their billing state.'),
                // unsignedInteger column: ->numeric() alone passes -5, which
                // MySQL refuses with error 1264 and SQLite stores silently.
                TextInput::make('payment_terms_days')->numeric()->minValue(1)
                    ->helperText('Blank uses the company default.'),
                TextInput::make('currency')->default('INR')->maxLength(3),
                Textarea::make('notes')->rows(3)->columnSpanFull(),
                Toggle::make('is_active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Progressive disclosure — see CompanyResource for the measurement.
            // Name and the GST badge stay at every width: the name says which
            // client, and registered-or-not decides what Rule 46 requires on
            // their invoice and is the one thing about them you cannot infer
            // from the name. State, email and Active come back at md.
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('gstin')->label('GST')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Registered' : 'Unregistered')
                    ->color(fn (?string $state): string => filled($state) ? 'success' : 'gray'),
                TextColumn::make('billing_address_state')->label('State')->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('email')->searchable()
                    ->visibleFrom('md'),
                IconColumn::make('is_active')->boolean()->label('Active')
                    ->visibleFrom('md'),
            ])
            ->defaultSort('name')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBillingClients::route('/'),
            'create' => Pages\CreateBillingClient::route('/create'),
            'edit' => Pages\EditBillingClient::route('/{record}/edit'),
        ];
    }
}
