<?php

namespace App\Filament\Admin\Resources\Boards\Pages;

use App\Filament\Admin\Resources\Boards\BoardResource;
use App\Filament\Admin\Resources\Boards\Widgets\FieldsWidget;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBoard extends ViewRecord
{
    protected static string $resource = BoardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            FieldsWidget::class,
        ];
    }
}
