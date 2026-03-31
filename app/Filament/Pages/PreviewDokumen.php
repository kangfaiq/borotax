<?php

namespace App\Filament\Pages;

use App\Domain\Shared\Services\DocumentPreviewService;
use Filament\Pages\Page;

class PreviewDokumen extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static string | \UnitEnum | null $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Preview Dokumen';

    protected static ?string $title = 'Preview Dokumen';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.preview-dokumen';

    public static function shouldRegisterNavigation(): bool
    {
        return app()->environment(['local', 'testing']) && auth()->user()?->role === 'admin';
    }

    public static function canAccess(): bool
    {
        return app()->environment(['local', 'testing']) && auth()->user()?->role === 'admin';
    }

    public function getViewData(): array
    {
        return [
            'previews' => collect(app(DocumentPreviewService::class)->catalog())->groupBy('category'),
        ];
    }
}