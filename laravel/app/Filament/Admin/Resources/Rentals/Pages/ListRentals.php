<?php

namespace App\Filament\Admin\Resources\Rentals\Pages;

use App\Filament\Admin\Resources\Rentals\RentalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentals extends ListRecords
{
    protected static string $resource = RentalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
