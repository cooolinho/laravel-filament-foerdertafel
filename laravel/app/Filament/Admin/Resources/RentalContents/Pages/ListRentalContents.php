<?php

namespace App\Filament\Admin\Resources\RentalContents\Pages;

use App\Filament\Admin\Resources\RentalContents\RentalContentResource;
use App\Models\RentalContent;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRentalContents extends ListRecords
{
    protected static string $resource = RentalContentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('Ausstehende Prüfungen')
                ->badge(RentalContent::where(RentalContent::needs_review, true)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where(RentalContent::needs_review, true)),

            'all' => Tab::make('Alle Inhalte')
                ->badge(RentalContent::count())
                ->badgeColor('gray'),
        ];
    }
}

