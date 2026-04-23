<?php

namespace App\Filament\Resources;

use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxAssessmentLetter;
use App\Domain\Tax\Services\TaxAssessmentLetterService;
use App\Enums\TaxAssessmentLetterType;
use App\Enums\TaxAssessmentReason;
use App\Filament\Forms\Components\FilamentDecimalInput;
use App\Filament\Resources\TaxAssessmentLetterResource\Pages;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Filament\Tables\Filters\TrashedFilter;

class TaxAssessmentLetterResource extends Resource
{
    protected static ?string $model = TaxAssessmentLetter::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Verifikasi';

    protected static ?string $navigationLabel = 'Surat Ketetapan';

    protected static ?string $modelLabel = 'Surat Ketetapan Pajak';

    protected static ?string $pluralModelLabel = 'Surat Ketetapan Pajak';

    protected static ?string $slug = 'verifikasi/surat-ketetapan';

    protected static ?int $navigationSort = 6;

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['admin', 'verifikator', 'petugas']) ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'draft')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['sourceTax.jenisPajak', 'user', 'compensations']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Referensi Ketetapan')
                    ->schema([
                        Select::make('source_tax_id')
                            ->label('Billing Sumber')
                            ->required()
                            ->searchable()
                            ->disabled(fn (?TaxAssessmentLetter $record) => $record !== null)
                            ->getSearchResultsUsing(fn (string $search): array => Tax::query()
                                ->with(['jenisPajak', 'user'])
                                ->where(function (Builder $query) use ($search) {
                                    $query
                                        ->where('billing_code', 'like', "%{$search}%")
                                        ->orWhere('skpd_number', 'like', "%{$search}%");
                                })
                                ->orderByDesc('created_at')
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (Tax $tax) => [
                                    $tax->id => trim(($tax->billing_code ?? '-') . ' | ' . ($tax->jenisPajak?->nama ?? '-') . ' | ' . ($tax->user?->nama_lengkap ?? $tax->user?->name ?? '-')),
                                ])
                                ->all())
                            ->getOptionLabelUsing(function ($value): ?string {
                                $tax = Tax::with(['jenisPajak', 'user'])->find($value);

                                if (!$tax) {
                                    return null;
                                }

                                return trim(($tax->billing_code ?? '-') . ' | ' . ($tax->jenisPajak?->nama ?? '-') . ' | ' . ($tax->user?->nama_lengkap ?? $tax->user?->name ?? '-'));
                            }),
                        Select::make('parent_letter_id')
                            ->label('Dokumen Induk')
                            ->searchable()
                            ->options(fn () => TaxAssessmentLetter::query()
                                ->where('status', 'disetujui')
                                ->orderByDesc('created_at')
                                ->limit(100)
                                ->get()
                                ->mapWithKeys(fn (TaxAssessmentLetter $letter) => [
                                    $letter->id => ($letter->document_number ?? $letter->id) . ' | ' . $letter->letter_type?->getLabel(),
                                ])
                                ->all())
                            ->visible(fn (Get $get) => $get('letter_type') === TaxAssessmentLetterType::SKPDKBT->value),
                    ])->columns(2),
                Section::make('Detail Ketetapan')
                    ->schema([
                        Select::make('letter_type')
                            ->label('Jenis Surat')
                            ->required()
                            ->options(collect(TaxAssessmentLetterType::cases())->mapWithKeys(fn (TaxAssessmentLetterType $type) => [$type->value => $type->getLabel()])->all())
                            ->live(),
                        Select::make('issuance_reason')
                            ->label('Dasar Penerbitan')
                            ->required()
                            ->options(fn (Get $get) => match ($get('letter_type')) {
                                TaxAssessmentLetterType::SKPDKB->value => [
                                    TaxAssessmentReason::Pemeriksaan->value => TaxAssessmentReason::Pemeriksaan->getLabel(),
                                    TaxAssessmentReason::JabatanTidakSampaikanSptpd->value => TaxAssessmentReason::JabatanTidakSampaikanSptpd->getLabel(),
                                    TaxAssessmentReason::JabatanTidakKooperatif->value => TaxAssessmentReason::JabatanTidakKooperatif->getLabel(),
                                ],
                                TaxAssessmentLetterType::SKPDKBT->value => [
                                    TaxAssessmentReason::DataBaru->value => TaxAssessmentReason::DataBaru->getLabel(),
                                ],
                                TaxAssessmentLetterType::SKPDLB->value => [
                                    TaxAssessmentReason::LebihBayar->value => TaxAssessmentReason::LebihBayar->getLabel(),
                                ],
                                TaxAssessmentLetterType::SKPDN->value => [
                                    TaxAssessmentReason::Nihil->value => TaxAssessmentReason::Nihil->getLabel(),
                                ],
                                default => [],
                            }),
                        Forms\Components\DatePicker::make('issue_date')
                            ->label('Tanggal Terbit')
                            ->required()
                            ->default(now()),
                        FilamentDecimalInput::configure(TextInput::make('base_amount')
                            ->label('Nominal Dasar')
                            ->required(fn (Get $get) => $get('letter_type') !== TaxAssessmentLetterType::SKPDN->value)
                            ->default(0)
                            ->step(0.01)
                            ->prefix('Rp')),
                        TextInput::make('interest_months')
                            ->label('Bulan Bunga')
                            ->numeric()
                            ->default(0)
                            ->visible(fn (Get $get) => in_array($get('letter_type'), [TaxAssessmentLetterType::SKPDKB->value, TaxAssessmentLetterType::SKPDKBT->value], true)),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('document_number')
                            ->label('Nomor Dokumen')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (?TaxAssessmentLetter $record) => $record?->document_number !== null),
                        TextInput::make('status')
                            ->label('Status')
                            ->formatStateUsing(fn (?TaxAssessmentLetter $record) => $record?->status?->getLabel())
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (?TaxAssessmentLetter $record) => $record !== null),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Tgl Terbit')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('document_number')
                    ->label('Nomor')
                    ->searchable()
                    ->placeholder('Draft'),
                Tables\Columns\TextColumn::make('letter_type')
                    ->label('Jenis')
                    ->badge(),
                Tables\Columns\TextColumn::make('issuance_reason')
                    ->label('Dasar')
                    ->badge(),
                Tables\Columns\TextColumn::make('sourceTax.billing_code')
                    ->label('Billing Sumber')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.nama_lengkap')
                    ->label('Wajib Pajak')
                    ->searchable()
                    ->state(fn (TaxAssessmentLetter $record) => $record->user?->nama_lengkap ?? $record->user?->name ?? '-'),
                Tables\Columns\TextColumn::make('total_assessment')
                    ->label('Total')
                    ->money('IDR')
                    ->state(fn (TaxAssessmentLetter $record) => (float) ($record->total_assessment ?? 0))
                    ->sortable(),
                Tables\Columns\TextColumn::make('available_credit')
                    ->label('Sisa Kredit')
                    ->money('IDR')
                    ->state(fn (TaxAssessmentLetter $record) => (float) ($record->available_credit ?? 0))
                    ->visible(fn () => true),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),
                Tables\Filters\SelectFilter::make('letter_type')
                    ->options(collect(TaxAssessmentLetterType::cases())->mapWithKeys(fn (TaxAssessmentLetterType $type) => [$type->value => $type->getLabel()])->all()),
            
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('cetak')
                        ->label('Cetak')
                        ->icon('heroicon-o-printer')
                        ->url(fn (TaxAssessmentLetter $record) => route('tax-assessment-letters.show', $record->id))
                        ->openUrlInNewTab()
                        ->visible(fn (TaxAssessmentLetter $record) => $record->isApproved()),
                    Action::make('unduh')
                        ->label('Unduh')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (TaxAssessmentLetter $record) => route('tax-assessment-letters.download', $record->id))
                        ->visible(fn (TaxAssessmentLetter $record) => $record->isApproved()),
                ]),
                Action::make('approve')
                    ->label('Setujui & Terbitkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (TaxAssessmentLetter $record) => auth()->user()?->can('review', $record) ?? false)
                    ->authorize(fn (TaxAssessmentLetter $record) => auth()->user()?->can('review', $record) ?? false)
                    ->schema([
                        Textarea::make('verification_notes')
                            ->label('Catatan Verifikasi')
                            ->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->action(function (TaxAssessmentLetter $record, array $data): void {
                        try {
                            app(TaxAssessmentLetterService::class)->approve(
                                $record,
                                auth()->user(),
                                $data['verification_notes'] ?? null,
                            );

                            Notification::make()
                                ->title('Surat ketetapan diterbitkan')
                                ->body('Nomor: ' . $record->fresh()->document_number)
                                ->success()
                                ->send();
                        } catch (InvalidArgumentException $exception) {
                            Notification::make()
                                ->title('Penerbitan gagal')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (TaxAssessmentLetter $record) => auth()->user()?->can('review', $record) ?? false)
                    ->authorize(fn (TaxAssessmentLetter $record) => auth()->user()?->can('review', $record) ?? false)
                    ->schema([
                        Textarea::make('verification_notes')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->action(function (TaxAssessmentLetter $record, array $data): void {
                        app(TaxAssessmentLetterService::class)->reject(
                            $record,
                            auth()->user(),
                            $data['verification_notes'],
                        );

                        Notification::make()
                            ->title('Draft ditolak')
                            ->warning()
                            ->send();
                    }),
                Action::make('allocate_credit')
                    ->label('Kompensasikan')
                    ->icon('heroicon-o-banknotes')
                    ->color('info')
                    ->visible(fn (TaxAssessmentLetter $record) => auth()->user()?->can('allocate', $record) ?? false)
                    ->authorize(fn (TaxAssessmentLetter $record) => auth()->user()?->can('allocate', $record) ?? false)
                    ->schema([
                        TextInput::make('target_billing_code')
                            ->label('Billing Tujuan')
                            ->required(),
                        FilamentDecimalInput::configure(TextInput::make('allocation_amount')
                            ->label('Nominal Kompensasi')
                            ->required()
                            ->step(0.01)
                            ->prefix('Rp')),
                    ])
                    ->action(function (TaxAssessmentLetter $record, array $data): void {
                        try {
                            $targetTax = Tax::where('billing_code', $data['target_billing_code'])->firstOrFail();

                            app(TaxAssessmentLetterService::class)->allocateCredit(
                                $record,
                                $targetTax,
                                (float) $data['allocation_amount'],
                                auth()->user(),
                            );

                            Notification::make()
                                ->title('Kompensasi berhasil diproses')
                                ->success()
                                ->send();
                        } catch (\Throwable $throwable) {
                            Notification::make()
                                ->title('Kompensasi gagal')
                                ->body($throwable->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('edit')
                    ->label('Ubah Draft')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (TaxAssessmentLetter $record) => static::getUrl('edit', ['record' => $record]))
                    ->visible(fn (TaxAssessmentLetter $record) => auth()->user()?->can('update', $record) ?? false),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaxAssessmentLetters::route('/'),
            'create' => Pages\CreateTaxAssessmentLetter::route('/create'),
            'edit' => Pages\EditTaxAssessmentLetter::route('/{record}/edit'),
        ];
    }
}