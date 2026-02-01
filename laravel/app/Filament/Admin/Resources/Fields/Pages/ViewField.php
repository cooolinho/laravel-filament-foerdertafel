<?php

namespace App\Filament\Admin\Resources\Fields\Pages;

use App\Filament\Admin\Resources\Fields\FieldResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewField extends ViewRecord
{
    protected static string $resource = FieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
