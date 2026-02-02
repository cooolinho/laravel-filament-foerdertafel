<?php

namespace App\Filament\Admin\Resources\Inquiries\Pages;

use App\Filament\Admin\Resources\Inquiries\InquiryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInquiry extends EditRecord
{
    protected static string $resource = InquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load the field IDs from the pivot table into requested_fields
        $data['requested_fields'] = $this->record->fields()->pluck('fields.id')->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        // Sync fields from requested_fields JSON to pivot table
        if (!empty($this->record->requested_fields)) {
            $this->record->fields()->sync($this->record->requested_fields);
        } else {
            $this->record->fields()->detach();
        }
    }
}
