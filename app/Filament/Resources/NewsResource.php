<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\NewsResource\Pages\ListNews;
use App\Filament\Resources\NewsResource\Pages\CreateNews;
use App\Filament\Resources\NewsResource\Pages\EditNews;
use App\Filament\Resources\NewsResource\Pages;
use App\Domain\CMS\Models\News;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-newspaper';

    protected static string | \UnitEnum | null $navigationGroup = 'CMS';

    protected static ?string $navigationLabel = 'Berita';

    protected static ?string $modelLabel = 'Berita';

    protected static ?string $pluralModelLabel = 'Berita';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konten Berita')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->label('Konten')
                            ->required()
                            ->columnSpanFull()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('news/attachments'),
                    ]),

                Section::make('Media & Metadata')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('image_url')
                            ->label('Gambar')
                            ->image()
                            ->disk('public_direct')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                            ->maxSize(10240)
                            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) {
                                return static::compressToWebp($file);
                            })
                            ->helperText('Gambar otomatis dikompres ke format WebP (maks 1 MB)')
                            ->required(),
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'pengumuman' => 'Pengumuman',
                                'pajak' => 'Informasi Pajak',
                                'event' => 'Event',
                                'edukasi' => 'Edukasi',
                                'lainnya' => 'Lainnya',
                            ])
                            ->required(),
                        TextInput::make('author')
                            ->label('Penulis')
                            ->maxLength(100)
                            ->default('Admin Bapenda'),
                        DateTimePicker::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->required()
                            ->default(now()),
                        TextInput::make('source_url')
                            ->label('URL Sumber')
                            ->url()
                            ->maxLength(255),
                        Toggle::make('is_featured')
                            ->label('Berita Unggulan')
                            ->default(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Gambar')
                    ->disk('public_direct')
                    ->circular(false)
                    ->width(80)
                    ->height(45),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pengumuman' => 'danger',
                        'pajak' => 'success',
                        'event' => 'warning',
                        'edukasi' => 'info',
                        default => 'gray',
                    }),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean(),
                TextColumn::make('view_count')
                    ->label('Views')
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Publikasi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'pengumuman' => 'Pengumuman',
                        'pajak' => 'Informasi Pajak',
                        'event' => 'Event',
                        'edukasi' => 'Edukasi',
                        'lainnya' => 'Lainnya',
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
            'index' => ListNews::route('/'),
            'create' => CreateNews::route('/create'),
            'edit' => EditNews::route('/{record}/edit'),
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
        $directory = 'news/images';
        $filename = $directory . '/' . str()->random(40) . '.webp';
        $storagePath = public_path($filename);

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
