<?php

namespace App\Filament\Admin\Resources\Inquiries\Pages;

use App\Filament\Admin\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInquiry extends CreateRecord
{
    protected static string $resource = InquiryResource::class;

    protected function afterCreate(): void
    {
        // Sync fields from requested_fields JSON to pivot table
        if (!empty($this->record->requested_fields)) {
            $this->record->fields()->sync($this->record->requested_fields);
        }
    }
}
