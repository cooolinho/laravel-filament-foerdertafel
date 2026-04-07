<?php

namespace App\Filament\Admin\Resources\Inquiries\Pages;

use App\Filament\Admin\Resources\Inquiries\Actions\InquiryActions;
use App\Filament\Admin\Resources\Inquiries\InquiryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInquiry extends ViewRecord
{
    protected static string $resource = InquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            InquiryActions::convertToRental(),

            EditAction::make(),
        ];
    }
}
