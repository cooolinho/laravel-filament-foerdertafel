<?php

namespace App\Filament\Resources\Boards;

use App\Filament\Resources\Boards\Pages\CreateBoard;
use App\Filament\Resources\Boards\Pages\EditBoard;
use App\Filament\Resources\Boards\Pages\ListBoards;
use App\Filament\Resources\Boards\Pages\ViewBoard;
use App\Filament\Resources\Boards\Schemas\BoardForm;
use App\Filament\Resources\Boards\Schemas\BoardInfolist;
use App\Filament\Resources\Boards\Tables\BoardsTable;
use App\Models\Board;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BoardResource extends Resource
{
    protected static ?string $model = Board::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|null|UnitEnum $navigationGroup = 'Board Management';

    protected static ?string $navigationLabel = 'Boards';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function form(Schema $schema): Schema
    {
        return BoardForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BoardInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BoardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBoards::route('/'),
            'create' => CreateBoard::route('/create'),
            'view' => ViewBoard::route('/{record}'),
            'edit' => EditBoard::route('/{record}/edit'),
        ];
    }
}
