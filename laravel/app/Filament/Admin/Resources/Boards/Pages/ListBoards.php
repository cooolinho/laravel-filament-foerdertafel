<?php

namespace App\Filament\Admin\Resources\Boards\Pages;

use App\Filament\Admin\Resources\Boards\BoardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBoards extends ListRecords
{
    protected static string $resource = BoardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
