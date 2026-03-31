<?php

namespace App\Filament\Resources\TaxAssessmentLetterResource\Pages;

use App\Domain\Tax\Services\TaxAssessmentLetterService;
use App\Filament\Resources\TaxAssessmentLetterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxAssessmentLetter extends CreateRecord
{
    protected static string $resource = TaxAssessmentLetterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return app(TaxAssessmentLetterService::class)->prepareDraftPayload($data, auth()->user());
    }
}