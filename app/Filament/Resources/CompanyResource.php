<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Invoicing\StateCodes;
use App\Models\Company;
use App\Rules\Gstin;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Companies';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Identity')->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(255)
                    ->helperText('Trading name, as it appears at the top of the invoice.'),
                TextInput::make('legal_name')->maxLength(255)
                    ->helperText('Only if it differs from the trading name.'),
                TextInput::make('email')->email()->maxLength(255),
                TextInput::make('phone')->maxLength(50),
                TextInput::make('website')->url()->maxLength(255),
            ]),

            Section::make('Tax registration')->columns(2)->schema([
                Toggle::make('is_gst_registered')->default(true)->live()
                    ->helperText('Off means this entity cannot issue a tax invoice.'),
                TextInput::make('gstin')
                    ->label('GSTIN')
                    ->maxLength(15)
                    ->visible(fn (Get $get): bool => (bool) $get('is_gst_registered'))
                    ->required(fn (Get $get): bool => (bool) $get('is_gst_registered'))
                    // The state rule is the load-bearing one: the invoice reads
                    // this state code to choose CGST+SGST or IGST.
                    ->rules(fn (Get $get): array => [new Gstin($get('address_state_code'))])
                    // GSTIN, PAN and IFSC are uppercase by definition, and a
                    // paste out of an email routinely is not. Failing that with
                    // "format is invalid" names the wrong problem: the characters
                    // are right, only the case is not.
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtoupper(trim($state)) : null),
                TextInput::make('pan')->label('PAN')->maxLength(10)
                    // GSTIN, PAN and IFSC are uppercase by definition, and a
                    // paste out of an email routinely is not. Failing that with
                    // "format is invalid" names the wrong problem: the characters
                    // are right, only the case is not.
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtoupper(trim($state)) : null),
                TextInput::make('cin')->label('CIN')->maxLength(21),
                TextInput::make('lut_number')->label('LUT number')->maxLength(255)
                    ->helperText('Needed to invoice an export without IGST.'),
                DatePicker::make('lut_valid_till'),
            ]),

            Section::make('Address')->columns(2)->schema([
                TextInput::make('address_line1')->label('Address line 1')->maxLength(255),
                TextInput::make('address_line2')->label('Address line 2')->maxLength(255),
                TextInput::make('address_city')->maxLength(255),
                Select::make('address_state_code')
                    ->label('State')
                    ->options(StateCodes::options())
                    ->searchable()
                    ->live()
                    // Required once this entity is GST-registered, because this
                    // is the input to the intra-state vs inter-state split. Left
                    // blank it is also never handed to the Gstin rule above, so
                    // the state cross-check is skipped entirely — and phase 2
                    // then reads company.state_code == place_of_supply as
                    // null == '07', which is false, and puts IGST on every
                    // invoice. The law does not let us edit one afterwards.
                    ->required(fn (Get $get): bool => (bool) $get('is_gst_registered'))
                    ->afterStateUpdated(fn (?string $state, callable $set) => $set('address_state', StateCodes::name($state))),
                TextInput::make('address_state')->label('State name')->maxLength(255)
                    ->helperText('Filled from the state above; printed on the invoice.'),
                // Twelve, matching the column: a company invoicing an export can
                // carry a non-Indian address, and a UK postcode is eight characters.
                TextInput::make('address_postal_code')->maxLength(12),
            ]),

            Section::make('Bank and UPI')->columns(2)->schema([
                TextInput::make('bank_name')->maxLength(255),
                TextInput::make('bank_account_name')->maxLength(255),
                TextInput::make('bank_account_number')->maxLength(34),
                TextInput::make('bank_ifsc')->label('IFSC')->maxLength(11)
                    // GSTIN, PAN and IFSC are uppercase by definition, and a
                    // paste out of an email routinely is not. Failing that with
                    // "format is invalid" names the wrong problem: the characters
                    // are right, only the case is not.
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtoupper(trim($state)) : null),
                TextInput::make('bank_branch')->maxLength(255),
                TextInput::make('upi_id')->label('UPI ID')->maxLength(255)
                    ->helperText('Becomes the QR code on the invoice PDF.'),
            ]),

            Section::make('Branding')->columns(3)->schema([
                SpatieMediaLibraryFileUpload::make('logo')->collection('logo')->image(),
                SpatieMediaLibraryFileUpload::make('signature')->collection('signature')->image()
                    ->helperText('Transparent PNG. Sits in the authorised-signatory box.'),
                SpatieMediaLibraryFileUpload::make('stamp')->collection('stamp')->image(),
            ]),

            Section::make('Invoice defaults')->columns(2)->schema([
                // Five characters is the ceiling: PREFX/26-27/0001 is exactly the
                // 16 characters Rule 46(b) allows for an invoice number.
                TextInput::make('invoice_prefix')->required()->maxLength(5)->default('INV')
                    ->rule('regex:/^[A-Za-z0-9-]+$/')
                    ->helperText('Up to 5 characters. Produces numbers like TLC/26-27/001.'),
                TextInput::make('credit_note_prefix')->required()->maxLength(5)->default('CRN')->rule('regex:/^[A-Za-z0-9-]+$/'),
                TextInput::make('proforma_prefix')->required()->maxLength(5)->default('PRO')->rule('regex:/^[A-Za-z0-9-]+$/'),
                TextInput::make('receipt_prefix')->required()->maxLength(5)->default('RCT')->rule('regex:/^[A-Za-z0-9-]+$/'),
                Select::make('default_template')
                    ->options(['classic' => 'Classic', 'modern' => 'Modern', 'minimal' => 'Minimal'])
                    ->default('classic')->required(),
                // The column is unsignedInteger, and ->numeric() alone only
                // adds the `numeric` rule, which passes -5. MySQL answers that
                // with error 1264 — a 500 for the admin — while SQLite stores
                // the negative silently. A zero-day term is not one either:
                // the invoice due date is issue date plus this.
                TextInput::make('default_payment_terms_days')->numeric()->minValue(1)->default(7)->required(),
                Textarea::make('default_terms')->rows(3)->columnSpanFull(),
                Textarea::make('default_notes')->rows(2)->columnSpanFull(),
                Textarea::make('footer_note')->rows(2)->columnSpanFull(),
            ]),

            Section::make()->schema([
                Toggle::make('is_active')->default(true)
                    // The observer throws rather than letting the default be
                    // switched off, which would leave every invoice form with no
                    // company and no explanation.
                    ->disabled(fn (?Company $record): bool => (bool) $record?->is_default)
                    ->helperText(fn (?Company $record): string => $record?->is_default
                        ? 'The default company cannot be deactivated. Make another company default first.'
                        : 'Hide without deleting.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Progressive disclosure: at 390px the body of this table scrolled
            // 806px, so a phone showed about two columns of five and the rest
            // were reachable only by dragging sideways. Name and Default are
            // the two that identify a row — the name says which entity, and
            // Default says which one a new invoice will prefill from, which is
            // the single thing this screen exists to manage. The rest come back
            // at md. visibleFrom() is a render-time breakpoint only, so
            // searching, sorting and the GSTIN placeholder are unchanged.
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('gstin')->label('GSTIN')->searchable()
                    ->placeholder('Unregistered')
                    ->visibleFrom('md'),
                TextColumn::make('address_state')->label('State')->sortable()
                    ->visibleFrom('md'),
                IconColumn::make('is_default')->boolean()->label('Default'),
                IconColumn::make('is_active')->boolean()->label('Active')
                    ->visibleFrom('md'),
            ])
            ->defaultSort('name')
            ->actions([
                Action::make('makeDefault')
                    ->label('Make default')
                    ->icon('heroicon-o-star')
                    // A plain Action is not wired to a policy the way EditAction
                    // is, so without this it would promote a company for anyone
                    // who can merely see the table. No role today can do that —
                    // Accounts holds the whole billing surface and Viewer holds
                    // none of it — but the moment a narrower billing role exists
                    // this is a write action reachable from a read-only screen.
                    ->authorize('update')
                    ->visible(fn (Company $record): bool => ! $record->is_default)
                    ->requiresConfirmation()
                    ->action(fn (Company $record) => $record->makeDefault()),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
