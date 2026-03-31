<?php

namespace App\Filament\Resources\TaxAssessmentLetterResource\Pages;

use App\Filament\Resources\TaxAssessmentLetterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTaxAssessmentLetters extends ListRecords
{
    protected static string $resource = TaxAssessmentLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => auth()->user()?->can('create', TaxAssessmentLetterResource::getModel()) ?? false),
        ];
    }
}