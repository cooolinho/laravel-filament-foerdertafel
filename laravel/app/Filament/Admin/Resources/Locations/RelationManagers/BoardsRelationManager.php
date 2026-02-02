<?php

namespace App\Filament\Admin\Resources\Locations\RelationManagers;

use App\Filament\Admin\Resources\Boards\BoardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class BoardsRelationManager extends RelationManager
{
    protected static string $relationship = 'boards';

    protected static ?string $relatedResource = BoardResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
