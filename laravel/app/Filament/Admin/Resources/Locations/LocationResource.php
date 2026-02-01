<?php

namespace App\Filament\Admin\Resources\Locations;

use App\Filament\Admin\Resources\Locations\Pages\CreateLocation;
use App\Filament\Admin\Resources\Locations\Pages\EditLocation;
use App\Filament\Admin\Resources\Locations\Pages\ListLocations;
use App\Filament\Admin\Resources\Locations\Pages\ViewLocation;
use App\Filament\Admin\Resources\Locations\Schemas\LocationForm;
use App\Filament\Admin\Resources\Locations\Schemas\LocationInfolist;
use App\Filament\Admin\Resources\Locations\Tables\LocationsTable;
use App\Models\Location;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|null|UnitEnum $navigationGroup = 'Board Management';

    protected static ?string $navigationLabel = 'Locations';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Schema $schema): Schema
    {
        return LocationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LocationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LocationsTable::configure($table);
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
            'index' => ListLocations::route('/'),
            'create' => CreateLocation::route('/create'),
            'view' => ViewLocation::route('/{record}'),
            'edit' => EditLocation::route('/{record}/edit'),
        ];
    }
}
