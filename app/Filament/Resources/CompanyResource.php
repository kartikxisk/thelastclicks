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
                    ->rules(fn (Get $get): array => [new Gstin($get('address_state_code'))]),
                TextInput::make('pan')->label('PAN')->maxLength(10),
                TextInput::make('cin')->label('CIN')->maxLength(21),
                TextInput::make('lut_number')->label('LUT number')
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
                    ->afterStateUpdated(fn (?string $state, callable $set) => $set('address_state', StateCodes::name($state))),
                TextInput::make('address_state')->label('State name')->maxLength(255)
                    ->helperText('Filled from the state above; printed on the invoice.'),
                TextInput::make('address_postal_code')->maxLength(6),
            ]),

            Section::make('Bank and UPI')->columns(2)->schema([
                TextInput::make('bank_name')->maxLength(255),
                TextInput::make('bank_account_name')->maxLength(255),
                TextInput::make('bank_account_number')->maxLength(34),
                TextInput::make('bank_ifsc')->label('IFSC')->maxLength(11),
                TextInput::make('bank_branch')->maxLength(255),
                TextInput::make('upi_id')->label('UPI ID')
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
                TextInput::make('default_payment_terms_days')->numeric()->default(7)->required(),
                Textarea::make('default_terms')->rows(3)->columnSpanFull(),
                Textarea::make('default_notes')->rows(2)->columnSpanFull(),
                Textarea::make('footer_note')->rows(2)->columnSpanFull(),
            ]),

            Section::make()->columns(2)->schema([
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
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('gstin')->label('GSTIN')->searchable()
                    ->placeholder('Unregistered'),
                TextColumn::make('address_state')->label('State')->sortable(),
                IconColumn::make('is_default')->boolean()->label('Default'),
                IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->defaultSort('name')
            ->actions([
                Action::make('makeDefault')
                    ->label('Make default')
                    ->icon('heroicon-o-star')
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
