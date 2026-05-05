<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Exception;
use Filament\Actions\ActionGroup;
use App\Filament\Resources\PermohonanSewaReklameResource\Pages\ListPermohonanSewaReklame;
use App\Filament\Resources\PermohonanSewaReklameResource\Pages\ViewPermohonanSewaReklame;
use App\Filament\Resources\PermohonanSewaReklameResource\Pages;
use App\Domain\Reklame\Models\PermohonanSewaReklame;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Domain\Reklame\Services\ReklameService;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\GeneratedLoginEmail;
use App\Domain\Region\Models\Province;
use App\Domain\Region\Models\Regency;
use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Village;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use App\Domain\Shared\Services\NotificationService;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;

class PermohonanSewaReklameResource extends Resource
{
    protected static ?string $model = PermohonanSewaReklame::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static string | \UnitEnum | null $navigationGroup = 'Reklame';

    protected static ?string $navigationLabel = 'Permohonan Sewa Reklame';

    protected static ?string $modelLabel = 'Permohonan Sewa';

    protected static ?int $navigationSort = 6;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['diajukan', 'perlu_revisi'])->count() ?: null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pemohon')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nomor_tiket')->label('Nomor Tiket')->disabled(),
                        TextInput::make('nama')->disabled(),
                        TextInput::make('nik')->disabled(),
                        TextInput::make('alamat')->disabled(),
                        TextInput::make('no_telepon')->disabled(),
                        TextInput::make('email')->disabled(),
                        TextInput::make('nama_usaha')->disabled(),
                        TextInput::make('nomor_registrasi_izin')->label('No. Registrasi Izin DPMPTSP')->disabled(),
                    ])->columns(2),
                Section::make('Detail Sewa')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('jenis_reklame_dipasang')->disabled(),
                        TextInput::make('durasi_sewa_hari')
                            ->label('Durasi (hari)')
                            ->disabled(),
                        DatePicker::make('tanggal_mulai_diinginkan')->disabled(),
                        Textarea::make('catatan')->disabled(),
                    ])->columns(2),
                Section::make('Status')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('status')->disabled(),
                        TextInput::make('petugas_nama')->disabled(),
                        Textarea::make('catatan_petugas')->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_tiket')
                    ->label('No. Tiket')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('tanggal_pengajuan')
                    ->label('Tgl Pengajuan')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                TextColumn::make('nama')
                    ->label('Pemohon')
                    ->searchable(),
                TextColumn::make('asetReklame.kode_aset')
                    ->label('Kode Aset'),
                TextColumn::make('asetReklame.lokasi')
                    ->label('Lokasi Aset')
                    ->limit(25),
                TextColumn::make('jenis_reklame_dipasang')
                    ->label('Jenis Reklame')
                    ->limit(20),
                TextColumn::make('durasi_sewa_hari')
                    ->label('Durasi')
                    ->formatStateUsing(function (int $state, $record): string {
                        $satuan = $record->satuan_sewa;
                        if ($satuan) {
                            $jumlah = match ($satuan) {
                                'tahun'  => (int) round($state / 365),
                                'bulan'  => (int) round($state / 30),
                                'minggu' => (int) round($state / 7),
                                default  => $state,
                            };
                            return $jumlah . ' ' . ucfirst($satuan);
                        }
                        if ($state >= 365 && $state % 365 === 0) return ($state / 365) . ' Tahun';
                        if ($state >= 28 && $state % 30 === 0) return ($state / 30) . ' Bulan';
                        if ($state % 7 === 0) return ($state / 7) . ' Minggu';
                        return $state . ' hari';
                    }),
                TextColumn::make('npwpd')
                    ->label('NPWPD')
                    ->badge()
                    ->color(fn(?string $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn(?string $state): string => $state ?: 'Belum ada')
                    ->icon(fn(?string $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),
                TextColumn::make('tanggal_mulai_diinginkan')
                    ->label('Mulai')
                    ->date('d/m/Y'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'diajukan'     => 'warning',
                        'perlu_revisi' => 'info',
                        'diproses'     => 'primary',
                        'disetujui'    => 'success',
                        'ditolak'      => 'danger',
                        default        => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'diajukan'     => 'Diajukan',
                        'perlu_revisi' => 'Perlu Revisi',
                        'diproses'     => 'Diproses',
                        'disetujui'    => 'Disetujui',
                        'ditolak'      => 'Ditolak',
                        default        => $state,
                    }),
                IconColumn::make('file_ktp')
                    ->label('Dok')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus')
                    ->getStateUsing(fn(PermohonanSewaReklame $record) => !empty($record->file_ktp)),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'diajukan'     => 'Diajukan',
                        'perlu_revisi' => 'Perlu Revisi',
                        'diproses'     => 'Diproses',
                        'disetujui'    => 'Disetujui',
                        'ditolak'      => 'Ditolak',
                    ]),
            
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('lihat_detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn(PermohonanSewaReklame $record) => static::getUrl('view', ['record' => $record])),
                Action::make('proses')
                    ->label('Proses')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('primary')
                    ->visible(fn(PermohonanSewaReklame $record) => $record->status === 'diajukan')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Permohonan?')
                    ->modalDescription('Permohonan akan ditandai "Diproses" dan Anda ditugaskan sebagai petugas.')
                    ->action(function (PermohonanSewaReklame $record): void {
                        $record->update([
                            'status'          => 'diproses',
                            'tanggal_diproses' => now(),
                            'petugas_id'      => auth()->id(),
                            'petugas_nama'    => auth()->user()->nama_lengkap ?? auth()->user()->name,
                        ]);

                        // Auto-match NIK → WajibPajak
                        $nikHash = $record->nik_hash;
                        if ($nikHash && !$record->npwpd) {
                            $wp = WajibPajak::where('nik_hash', $nikHash)
                                ->where('status', 'disetujui')
                                ->whereNotNull('npwpd')
                                ->first();

                            if ($wp) {
                                $record->update(['npwpd' => $wp->npwpd]);
                                Notification::make()->success()
                                    ->title('NPWPD ditemukan otomatis')
                                    ->body("NPWPD: {$wp->npwpd} — {$wp->nama_lengkap}")
                                    ->send();
                                return;
                            }
                        }

                        // Check if npwpd was submitted from form
                        if ($record->npwpd) {
                            Notification::make()->success()
                                ->title('Permohonan sedang diproses')
                                ->body("NPWPD sudah ada: {$record->npwpd}")
                                ->send();
                            return;
                        }

                        Notification::make()->success()
                            ->title('Permohonan sedang diproses')
                            ->body('NPWPD belum ditemukan. Gunakan tombol "Buat NPWPD" atau "Cek NPWPD".')
                            ->send();

                        // Notify WP: permohonan sedang diproses
                        if ($record->user) {
                            NotificationService::notifyUserBoth(
                                $record->user,
                                'Permohonan Sewa Reklame Sedang Diproses',
                                'Permohonan sewa reklame Anda (No. ' . $record->nomor_tiket . ') sedang diproses oleh petugas.',
                                'info',
                                actionUrl: route('portal.dashboard'),
                            );
                        }
                    }),
                Action::make('buat_npwpd')
                    ->label('Buat NPWPD')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->visible(fn(PermohonanSewaReklame $record) => $record->status === 'diproses' && !$record->npwpd)
                    ->modalHeading('Buat NPWPD Baru')
                    ->modalDescription('Daftarkan pemohon sebagai wajib pajak dan generate NPWPD otomatis.')
                    ->schema(fn(PermohonanSewaReklame $record) => [
                        Section::make('Data Pemohon (dari permohonan)')
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('nik')
                                    ->label('NIK')
                                    ->default($record->nik)
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('nama_lengkap')
                                    ->label('Nama Lengkap')
                                    ->default($record->nama)
                                    ->required(),
                                TextInput::make('alamat')
                                    ->label('Alamat')
                                    ->default($record->alamat)
                                    ->required(),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->default($record->email)
                                    ->helperText('Kosongkan jika pemohon tidak punya email, sistem akan generate email login otomatis.'),
                                Select::make('tipe_wajib_pajak')
                                    ->label('Tipe Wajib Pajak')
                                    ->options([
                                        'perorangan' => 'Perorangan (P1)',
                                        'perusahaan' => 'Perusahaan (P2)',
                                    ])
                                    ->default(($record->nama_usaha || $record->file_npwp) ? 'perusahaan' : 'perorangan')
                                    ->required(),
                                TextInput::make('nama_perusahaan')
                                    ->label('Nama Perusahaan')
                                    ->default($record->nama_usaha)
                                    ->visible(fn(Get $get) => $get('tipe_wajib_pajak') === 'perusahaan'),
                            ])->columns(2),
                        Section::make('Data Wilayah')
                            ->columnSpanFull()
                            ->schema([
                                Select::make('asal_wilayah')
                                    ->label('Asal Wilayah')
                                    ->options([
                                        'bojonegoro' => 'Bojonegoro',
                                        'luar_bojonegoro' => 'Luar Bojonegoro',
                                    ])
                                    ->default('bojonegoro')
                                    ->required()
                                    ->live(),
                                Select::make('province_code')
                                    ->label('Provinsi')
                                    ->options(Province::pluck('name', 'code'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn(Set $set) => $set('regency_code', null)),
                                Select::make('regency_code')
                                    ->label('Kabupaten/Kota')
                                    ->options(fn(Get $get) => $get('province_code')
                                        ? Regency::where('province_code', $get('province_code'))->pluck('name', 'code')
                                        : [])
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn(Set $set) => $set('district_code', null)),
                                Select::make('district_code')
                                    ->label('Kecamatan')
                                    ->options(fn(Get $get) => $get('regency_code')
                                        ? District::where('regency_code', $get('regency_code'))->pluck('name', 'code')
                                        : [])
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn(Set $set) => $set('village_code', null)),
                                Select::make('village_code')
                                    ->label('Kelurahan/Desa')
                                    ->options(fn(Get $get) => $get('district_code')
                                        ? Village::where('district_code', $get('district_code'))->pluck('name', 'code')
                                        : [])
                                    ->searchable()
                                    ->required(),
                            ])->columns(2),
                        Section::make('Dokumen Pemohon')
                            ->columnSpanFull()
                            ->schema([
                                Placeholder::make('preview_ktp')
                                    ->label('File KTP')
                                    ->content(fn() => $record->file_ktp
                                        ? new HtmlString('<span class="text-success-600"><i>&#10003;</i> ' . basename($record->file_ktp) . '</span>')
                                        : 'Tidak ada'),
                                Placeholder::make('preview_npwp')
                                    ->label('File NPWP')
                                    ->content(fn() => $record->file_npwp
                                        ? new HtmlString('<span class="text-success-600"><i>&#10003;</i> ' . basename($record->file_npwp) . '</span>')
                                        : 'Tidak ada'),
                            ])->columns(2),
                    ])
                    ->action(function (PermohonanSewaReklame $record, array $data): void {
                        $asalWilayah = match ($data['asal_wilayah'] ?? 'bojonegoro') {
                            'dalam_daerah' => 'bojonegoro',
                            'luar_daerah' => 'luar_bojonegoro',
                            default => $data['asal_wilayah'] ?? 'bojonegoro',
                        };

                        // Generate email if empty
                        $usedGeneratedEmail = blank($data['email'] ?? null);

                        $email = filled($data['email'] ?? null)
                            ? str($data['email'])->trim()->lower()->value()
                            : GeneratedLoginEmail::forWajibPajak(
                                $data['nama_lengkap'] ?? null,
                                $data['alamat'] ?? null,
                                $record->no_telepon,
                            );

                        // Check if NIK already exists as WajibPajak
                        $nikHash = WajibPajak::generateHash($data['nik']);
                        $existingWp = WajibPajak::where('nik_hash', $nikHash)
                            ->where('status', 'disetujui')
                            ->whereNotNull('npwpd')
                            ->first();

                        if ($existingWp) {
                            $record->update(['npwpd' => $existingWp->npwpd]);
                            Notification::make()->warning()
                                ->title('WP sudah terdaftar')
                                ->body("NPWPD: {$existingWp->npwpd}. Data permohonan telah diperbarui.")
                                ->send();
                            return;
                        }

                        // Create User shell
                        $emailHash = User::generateHash($email);
                        $user = User::where('email_hash', $emailHash)->first();
                        if (!$user) {
                            $user = User::create([
                                'name'          => $data['nama_lengkap'],
                                'email'         => $email,
                                'password'      => Hash::make(str()->random(32)),
                                'nik'           => $data['nik'],
                                'nama_lengkap'  => $data['nama_lengkap'],
                                'alamat'        => $data['alamat'],
                                'role'          => 'user',
                                'status'        => 'verified',
                                'province_code' => $data['province_code'] ?? null,
                                'regency_code'  => $data['regency_code'] ?? null,
                                'district_code' => $data['district_code'] ?? null,
                                'village_code'  => $data['village_code'] ?? null,
                            ]);
                        }

                        // Create WajibPajak
                        $tipe = $data['tipe_wajib_pajak'] ?? 'perorangan';
                        $npwpd = WajibPajak::generateNpwpd($tipe);

                        WajibPajak::create([
                            'user_id'           => $user->id,
                            'nik'               => $data['nik'],
                            'nama_lengkap'      => $data['nama_lengkap'],
                            'alamat'            => $data['alamat'],
                            'tipe_wajib_pajak'  => $tipe,
                            'nama_perusahaan'   => $data['nama_perusahaan'] ?? null,
                            'asal_wilayah'      => $asalWilayah,
                            'province_code'     => $data['province_code'] ?? null,
                            'regency_code'      => $data['regency_code'] ?? null,
                            'district_code'     => $data['district_code'] ?? null,
                            'village_code'      => $data['village_code'] ?? null,
                            'ktp_image_path'    => $record->file_ktp,
                            'status'            => 'disetujui',
                            'npwpd'             => $npwpd,
                            'tanggal_daftar'    => now(),
                            'tanggal_verifikasi' => now(),
                            'petugas_id'        => auth()->id(),
                            'petugas_nama'      => auth()->user()->nama_lengkap ?? auth()->user()->name,
                        ]);

                        // Save NPWPD to permohonan
                        $record->update(['npwpd' => $npwpd]);

                        Notification::make()->success()
                            ->title('NPWPD berhasil dibuat')
                            ->body("NPWPD: {$npwpd}\n" . ($usedGeneratedEmail
                                ? "Username login otomatis: {$user->email}"
                                : "Email login WP: {$user->email}"))
                            ->send();
                    }),
                Action::make('cek_npwpd')
                    ->label('Cek NPWPD')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('warning')
                    ->visible(fn(PermohonanSewaReklame $record) => $record->status === 'diproses' && !$record->npwpd)
                    ->schema(fn(PermohonanSewaReklame $record) => [
                        TextInput::make('npwpd_cari')
                            ->label('NPWPD')
                            ->placeholder('Masukkan NPWPD untuk dicari')
                            ->default(
                                WajibPajak::where('nik_hash', $record->nik_hash)
                                    ->where('status', 'disetujui')
                                    ->whereNotNull('npwpd')
                                    ->value('npwpd')
                            )
                            ->maxLength(13),
                    ])
                    ->modalHeading('Cek NPWPD')
                    ->modalDescription('Cari NPWPD secara manual atau otomatis berdasarkan NIK pemohon.')
                    ->modalSubmitActionLabel('Cari')
                    ->action(function (PermohonanSewaReklame $record, array $data): void {
                        // If manual NPWPD provided, search by it
                        if (!empty($data['npwpd_cari'])) {
                            $wp = WajibPajak::where('npwpd', $data['npwpd_cari'])
                                ->where('status', 'disetujui')
                                ->first();
                            if ($wp) {
                                $record->update(['npwpd' => $wp->npwpd]);
                                Notification::make()->success()
                                    ->title('NPWPD ditemukan')
                                    ->body("NPWPD: {$wp->npwpd} — {$wp->nama_lengkap}")
                                    ->send();
                                return;
                            }
                            Notification::make()->warning()->title('NPWPD tidak ditemukan')->send();
                            return;
                        }

                        // Auto search by NIK hash
                        $nikHash = $record->nik_hash;
                        if ($nikHash) {
                            $wp = WajibPajak::where('nik_hash', $nikHash)
                                ->where('status', 'disetujui')
                                ->whereNotNull('npwpd')
                                ->first();
                            if ($wp) {
                                $record->update(['npwpd' => $wp->npwpd]);
                                Notification::make()->success()
                                    ->title('NPWPD ditemukan berdasarkan NIK')
                                    ->body("NPWPD: {$wp->npwpd} — {$wp->nama_lengkap}")
                                    ->send();
                                return;
                            }
                        }

                        Notification::make()->warning()
                            ->title('NPWPD tidak ditemukan')
                            ->body('Gunakan tombol "Buat NPWPD" untuk mendaftarkan wajib pajak baru.')
                            ->send();
                    }),
                Action::make('buat_skpd')
                    ->label('Buat SKPD')
                    ->icon('heroicon-o-document-check')
                    ->color('success')
                    ->visible(fn(PermohonanSewaReklame $record) => $record->status === 'diproses' && $record->npwpd && !$record->skpdReklame)
                    ->requiresConfirmation()
                    ->modalHeading('Buat SKPD Draft?')
                    ->modalDescription('SKPD draft akan dibuat dan masuk antrian verifikasi. Verifikator akan menyetujui atau mengembalikan untuk revisi.')
                    ->action(function (PermohonanSewaReklame $record): void {
                        $aset = $record->asetReklame;
                        if (!$aset) {
                            Notification::make()->danger()->title('Aset reklame tidak ditemukan')->send();
                            return;
                        }

                        // Map jenis aset → sub_jenis_pajak
                        $subJenis = SubJenisPajak::where('kode', 'REKLAME_TETAP')->first();
                        $hargaPatokanReklame = match ($aset->jenis) {
                            'neon_box' => HargaPatokanReklame::where('kode', 'RKL_NEON_BOX')->first(),
                            'billboard' => $aset->luas_m2 >= 10
                                ? HargaPatokanReklame::where('kode', 'RKL_BILLBOARD_GTE_10')->first()
                                : HargaPatokanReklame::where('kode', 'RKL_BILLBOARD_LT_10')->first(),
                            default => null,
                        };

                        if (!$subJenis || !$hargaPatokanReklame) {
                            Notification::make()->danger()->title('Master reklame tidak ditemukan untuk jenis: ' . $aset->jenis)->send();
                            return;
                        }

                        // Map durasi_sewa_hari → satuan_waktu + durasi + harga_sewa tetap
                        // Map satuan_sewa → satuan_waktu + durasi + harga_sewa
                        $durasiHari = $record->durasi_sewa_hari;
                        $satuan = $record->satuan_sewa;

                        // Fallback untuk data lama tanpa satuan_sewa
                        if (!$satuan) {
                            if ($durasiHari >= 365) {
                                $satuan = 'tahun';
                            } elseif ($durasiHari >= 28) {
                                $satuan = 'bulan';
                            } else {
                                $satuan = 'minggu';
                            }
                        }

                        $satuanWaktu = match ($satuan) {
                            'tahun'  => 'perTahun',
                            'bulan'  => 'perBulan',
                            'minggu' => 'perMinggu',
                        };
                        $durasi = match ($satuan) {
                            'tahun'  => max(1, (int) round($durasiHari / 365)),
                            'bulan'  => max(1, (int) round($durasiHari / 30)),
                            'minggu' => max(1, (int) round($durasiHari / 7)),
                        };
                        $hargaSewa = match ($satuan) {
                            'tahun'  => (float) $aset->harga_sewa_per_tahun,
                            'bulan'  => (float) $aset->harga_sewa_per_bulan,
                            'minggu' => (float) $aset->harga_sewa_per_minggu,
                        };

                        // Neon box hanya ada tarif perTahun
                        if ($aset->jenis === 'neon_box') {
                            $satuanWaktu = 'perTahun';
                            $durasi = max(1, (int) round($durasiHari / 365));
                            $hargaSewa = (float) $aset->harga_sewa_per_tahun;
                        }

                        if (!$hargaSewa || $hargaSewa <= 0) {
                            Notification::make()->danger()->title('Harga sewa belum diatur pada aset ' . $aset->kode_aset)->send();
                            return;
                        }

                        // Hitung masa berlaku
                        $mulai = $record->tanggal_mulai_diinginkan ?? now();
                        $sampai = match ($satuanWaktu) {
                            'perTahun'  => $mulai->copy()->addYears($durasi)->subDay(),
                            'perBulan'  => $mulai->copy()->addMonths($durasi)->subDay(),
                            'perMinggu' => $mulai->copy()->addWeeks($durasi)->subDay(),
                            default     => $mulai->copy()->addDays($durasiHari)->subDay(),
                        };

                        try {
                            $skpd = app(ReklameService::class)->createDraftSkpdSewa([
                                'sub_jenis_pajak_id'     => $subJenis->id,
                                'harga_patokan_reklame_id' => $hargaPatokanReklame->id,
                                'satuan_waktu'           => $satuanWaktu,
                                'harga_sewa'             => $hargaSewa,
                                'luas_m2'                => $aset->luas_m2,
                                'jumlah_muka'            => $aset->jumlah_muka,
                                'durasi'                 => $durasi,
                                'aset_reklame_pemkab_id' => $aset->id,
                                'permohonan_sewa_id'     => $record->id,
                                'npwpd'                  => $record->npwpd,
                                'nik_wajib_pajak'        => $record->nik,
                                'nama_wajib_pajak'       => $record->nama,
                                'alamat_wajib_pajak'     => $record->alamat ?? '-',
                                'nama_reklame'           => $aset->nama,
                                'isi_materi_reklame'     => $record->jenis_reklame_dipasang,
                                'alamat_reklame'         => $aset->lokasi ?? '-',
                                'bentuk'                 => 'persegi',
                                'panjang'                => $aset->panjang,
                                'lebar'                  => $aset->lebar,
                                'masa_berlaku_mulai'     => $mulai->format('Y-m-d'),
                                'masa_berlaku_sampai'    => $sampai->format('Y-m-d'),
                                'petugas_id'             => auth()->id(),
                                'petugas_nama'           => auth()->user()->nama_lengkap ?? auth()->user()->name,
                            ]);

                            // Link SKPD to permohonan, keep status diproses (wait for verifikator)
                            $record->update([
                                'skpd_id' => $skpd->id,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('SKPD Berhasil Dibuat')
                                ->body("Nomor: {$skpd->nomor_skpd}. Menunggu verifikasi.")
                                ->send();

                            // Notify verifikator: draft SKPD baru perlu diverifikasi
                            NotificationService::notifyRole(
                                'verifikator',
                                'Draft SKPD Reklame Menunggu Verifikasi',
                                'Draft SKPD Reklame (sewa) untuk ' . ($record->nama ?? 'pemohon') . ' perlu diverifikasi.',
                                actionUrl: \App\Filament\Resources\SkpdReklameResource::getUrl('index'),
                            );
                        } catch (Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal membuat SKPD')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
                Action::make('perlu_revisi')
                    ->label('Perlu Revisi')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn(PermohonanSewaReklame $record) => $record->status === 'diproses')
                    ->schema([
                        Textarea::make('catatan_petugas')
                            ->label('Catatan Revisi (akan dilihat pemohon)')
                            ->required()
                            ->helperText('Jelaskan apa yang perlu diperbaiki/dilengkapi oleh pemohon'),
                    ])
                    ->action(function (PermohonanSewaReklame $record, array $data): void {
                        $record->update([
                            'status'          => 'perlu_revisi',
                            'catatan_petugas' => $data['catatan_petugas'],
                            'petugas_id'      => auth()->id(),
                            'petugas_nama'    => auth()->user()->nama_lengkap ?? auth()->user()->name,
                        ]);

                        // Notify WP: perlu revisi
                        if ($record->user) {
                            NotificationService::notifyUserBoth(
                                $record->user,
                                'Permohonan Sewa Reklame Perlu Revisi',
                                'Permohonan sewa reklame Anda (No. ' . $record->nomor_tiket . ') perlu diperbaiki. Catatan: ' . $data['catatan_petugas'],
                                'verification',
                                actionUrl: route('portal.dashboard'),
                            );
                        }

                        Notification::make()->success()->title('Permohonan dikembalikan untuk revisi')->send();
                    }),
                Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(PermohonanSewaReklame $record) => in_array($record->status, ['diajukan', 'diproses']))
                    ->schema([
                        Textarea::make('catatan_petugas')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (PermohonanSewaReklame $record, array $data): void {
                        $record->update([
                            'status'          => 'ditolak',
                            'catatan_petugas' => $data['catatan_petugas'],
                            'tanggal_selesai' => now(),
                            'petugas_id'      => auth()->id(),
                            'petugas_nama'    => auth()->user()->nama_lengkap ?? auth()->user()->name,
                        ]);

                        // Notify WP: permohonan ditolak
                        if ($record->user) {
                            NotificationService::notifyUserBoth(
                                $record->user,
                                'Permohonan Sewa Reklame Ditolak',
                                'Permohonan sewa reklame Anda (No. ' . $record->nomor_tiket . ') ditolak. Alasan: ' . $data['catatan_petugas'],
                                'verification',
                                actionUrl: route('portal.dashboard'),
                            );
                        }

                        Notification::make()->success()->title('Permohonan ditolak')->send();
                    }),
                ActionGroup::make([
                    Action::make('cetak_skpd')
                        ->label('Cetak SKPD')
                        ->icon('heroicon-o-printer')
                        ->url(fn(PermohonanSewaReklame $record) => route('skpd-reklame.show', $record->skpdReklame->id))
                        ->openUrlInNewTab(),
                    Action::make('unduh_skpd')
                        ->label('Unduh SKPD')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (PermohonanSewaReklame $record) {
                            $skpd = $record->skpdReklame;
                            $skpd->load(['reklameObject', 'jenisPajak', 'subJenisPajak', 'asetReklamePemkab']);

                            $pimpinan = $skpd->pimpinan_id
                                ? Pimpinan::find($skpd->pimpinan_id)
                                : Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first();

                            $pdf = Pdf::loadView('documents.skpd-reklame', [
                                'skpd' => $skpd,
                                'pimpinan' => $pimpinan,
                                'isPdf' => true,
                            ]);

                            $pdf->setPaper([0, 0, 609.449, 935.433], 'portrait');

                            $filename = 'SKPD_Reklame_' . str_replace([' ', '/'], '_', $skpd->nomor_skpd) . '.pdf';

                            return response()->streamDownload(fn () => print($pdf->output()), $filename, [
                                'Content-Type' => 'application/pdf',
                            ]);
                        }),
                ])
                    ->label('Dokumen SKPD')
                    ->icon('heroicon-m-document-text')
                    ->color('success')
                    ->visible(fn(PermohonanSewaReklame $record) => $record->status === 'disetujui' && $record->skpdReklame),
            ])
            ->defaultSort('tanggal_pengajuan', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermohonanSewaReklame::route('/'),
            'view'  => ViewPermohonanSewaReklame::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
