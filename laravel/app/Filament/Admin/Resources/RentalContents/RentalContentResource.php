<?php

namespace App\Filament\Admin\Resources\RentalContents;

use App\Filament\Admin\Resources\RentalContents\Pages\ListRentalContents;
use App\Filament\Admin\Resources\RentalContents\Pages\ViewRentalContent;
use App\Filament\Admin\Resources\RentalContents\Tables\RentalContentsTable;
use App\Filament\Traits\UseResourceUrlsTrait;
use App\Models\RentalContent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RentalContentResource extends Resource
{
    use UseResourceUrlsTrait;

    protected static ?string $model = RentalContent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $recordTitleAttribute = 'access_code';

    protected static string|null|UnitEnum $navigationGroup = 'Customer Management';

    protected static ?string $navigationLabel = 'Content-Prüfung';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where(RentalContent::needs_review, true)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Inhalte warten auf Prüfung';
    }

    public static function table(Table $table): Table
    {
        return RentalContentsTable::configure($table);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentalContents::route('/'),
            'view' => ViewRentalContent::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

