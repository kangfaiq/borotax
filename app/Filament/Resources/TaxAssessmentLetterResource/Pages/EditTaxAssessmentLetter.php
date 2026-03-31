<?php

namespace App\Filament\Resources\TaxAssessmentLetterResource\Pages;

use App\Domain\Tax\Services\TaxAssessmentLetterService;
use App\Filament\Resources\TaxAssessmentLetterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTaxAssessmentLetter extends EditRecord
{
    protected static string $resource = TaxAssessmentLetterResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return array_merge(
            app(TaxAssessmentLetterService::class)->prepareDraftPayload($data, $this->record->creator ?? auth()->user()),
            [
                'status' => $this->record->status,
                'document_number' => $this->record->document_number,
                'generated_tax_id' => $this->record->generated_tax_id,
                'verified_by' => $this->record->verified_by,
                'verified_by_name' => $this->record->verified_by_name,
                'verified_at' => $this->record->verified_at,
                'verification_notes' => $this->record->verification_notes,
                'pimpinan_id' => $this->record->pimpinan_id,
            ],
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}