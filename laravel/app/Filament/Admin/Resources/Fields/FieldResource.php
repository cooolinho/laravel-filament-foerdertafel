<?php

namespace App\Filament\Admin\Resources\Fields;

use App\Filament\Admin\Resources\Fields\Pages\CreateField;
use App\Filament\Admin\Resources\Fields\Pages\EditField;
use App\Filament\Admin\Resources\Fields\Pages\ListFields;
use App\Filament\Admin\Resources\Fields\Pages\ViewField;
use App\Filament\Admin\Resources\Fields\RelationManagers\RentalsRelationManager;
use App\Filament\Admin\Resources\Fields\Schemas\FieldForm;
use App\Filament\Admin\Resources\Fields\Schemas\FieldInfolist;
use App\Filament\Admin\Resources\Fields\Tables\FieldsTable;
use App\Models\Field;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FieldResource extends Resource
{
    protected static ?string $model = Field::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|null|UnitEnum $navigationGroup = 'Board Management';

    protected static ?string $navigationLabel = 'Fields';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $availableCount = static::getModel()::where(Field::status, Field::STATUS_AVAILABLE)->count();
        $totalCount = static::getModel()::count();
        return "{$availableCount}/{$totalCount}";
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $availableCount = static::getModel()::where(Field::status, Field::STATUS_AVAILABLE)->count();
        $totalCount = static::getModel()::count();

        if ($totalCount === 0) {
            return 'gray';
        }

        $percentage = ($availableCount / $totalCount) * 100;

        return match(true) {
            $percentage >= 50 => 'success',
            $percentage >= 25 => 'warning',
            default => 'danger',
        };
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Available / Total Fields';
    }

    public static function form(Schema $schema): Schema
    {
        return FieldForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FieldInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FieldsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RentalsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFields::route('/'),
            'create' => CreateField::route('/create'),
            'view' => ViewField::route('/{record}'),
            'edit' => EditField::route('/{record}/edit'),
        ];
    }
}
