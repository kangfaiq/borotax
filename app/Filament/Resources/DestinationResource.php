<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\DestinationResource\Pages\ListDestinations;
use App\Filament\Resources\DestinationResource\Pages\CreateDestination;
use App\Filament\Resources\DestinationResource\Pages\EditDestination;
use App\Filament\Resources\DestinationResource\Pages;
use App\Domain\CMS\Models\Destination;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DestinationResource extends Resource
{
    protected static ?string $model = Destination::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map-pin';

    protected static string | \UnitEnum | null $navigationGroup = 'CMS';

    protected static ?string $navigationLabel = 'Destinasi';

    protected static ?string $modelLabel = 'Destinasi';

    protected static ?string $pluralModelLabel = 'Destinasi';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', \Illuminate\Support\str()->slug($state ?? ''))),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(150)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-friendly identifier, otomatis dari nama'),
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'wisata' => 'Wisata',
                                'kuliner' => 'Kuliner',
                                'hotel' => 'Hotel',
                                'oleh-oleh' => 'Oleh-Oleh',
                                'hiburan' => 'Hiburan',
                            ])
                            ->required(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Media & Rating')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('image_url')
                            ->label('Gambar')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                            ->maxSize(10240)
                            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) {
                                return static::compressToWebp($file);
                            })
                            ->helperText('Gambar otomatis dikompres ke format WebP (maks 1 MB)')
                            ->required(),
                        TextInput::make('rating')
                            ->label('Rating')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->step(0.1)
                            ->default(0),
                        TextInput::make('review_count')
                            ->label('Jumlah Review')
                            ->numeric()
                            ->default(0),
                        TextInput::make('price_range')
                            ->label('Range Harga')
                            ->maxLength(50)
                            ->placeholder('Rp 10.000 - Rp 50.000'),
                        Toggle::make('is_featured')
                            ->label('Unggulan')
                            ->default(false),
                    ])->columns(3),

                Section::make('Kontak & Lokasi')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel(),
                        TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->required(),
                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->required(),
                    ])->columns(4),

                Section::make('Fasilitas')
                    ->columnSpanFull()
                    ->schema([
                        TagsInput::make('facilities')
                            ->label('Fasilitas')
                            ->placeholder('Tambah fasilitas...')
                            ->splitKeys(['Tab', ','])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Gambar')
                    ->circular(false)
                    ->width(80)
                    ->height(45),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'wisata' => 'success',
                        'kuliner' => 'warning',
                        'hotel' => 'info',
                        'oleh-oleh' => 'primary',
                        'hiburan' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('rating')
                    ->label('Rating')
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('review_count')
                    ->label('Reviews')
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'wisata' => 'Wisata',
                        'kuliner' => 'Kuliner',
                        'hotel' => 'Hotel',
                        'oleh-oleh' => 'Oleh-Oleh',
                        'hiburan' => 'Hiburan',
                    ]),
                TernaryFilter::make('is_featured')
                    ->label('Unggulan'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDestinations::route('/'),
            'create' => CreateDestination::route('/create'),
            'edit' => EditDestination::route('/{record}/edit'),
        ];
    }

    protected static function compressToWebp(TemporaryUploadedFile $file): string
    {
        $sourcePath = $file->getRealPath();
        $mimeType = $file->getMimeType();

        $sourceImage = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/gif' => imagecreatefromgif($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => imagecreatefromjpeg($sourcePath),
        };

        // Resize to 1200x675 (16:9) cover mode
        $origW = imagesx($sourceImage);
        $origH = imagesy($sourceImage);
        $targetW = 1200;
        $targetH = 675;

        $ratioW = $targetW / $origW;
        $ratioH = $targetH / $origH;
        $ratio = max($ratioW, $ratioH);

        $cropW = (int) ceil($targetW / $ratio);
        $cropH = (int) ceil($targetH / $ratio);
        $cropX = (int) (($origW - $cropW) / 2);
        $cropY = (int) (($origH - $cropH) / 2);

        $resized = imagecreatetruecolor($targetW, $targetH);
        imagecopyresampled($resized, $sourceImage, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);
        imagedestroy($sourceImage);

        // Compress to WebP, reduce quality until ≤ 1MB
        $directory = 'destinations/images';
        $filename = $directory . '/' . str()->random(40) . '.webp';
        $storagePath = storage_path('app/public/' . $filename);

        if (!is_dir(dirname($storagePath))) {
            mkdir(dirname($storagePath), 0755, true);
        }

        $quality = 85;
        do {
            imagewebp($resized, $storagePath, $quality);
            clearstatcache(true, $storagePath);
            $size = filesize($storagePath);
            $quality -= 10;
        } while ($size > 1048576 && $quality >= 10);

        imagedestroy($resized);

        return $filename;
    }
}
