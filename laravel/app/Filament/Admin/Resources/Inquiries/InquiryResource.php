<?php

namespace App\Filament\Admin\Resources\Inquiries;

use App\Filament\Admin\Resources\Inquiries\Pages\CreateInquiry;
use App\Filament\Admin\Resources\Inquiries\Pages\EditInquiry;
use App\Filament\Admin\Resources\Inquiries\Pages\ListInquiries;
use App\Filament\Admin\Resources\Inquiries\Pages\ViewInquiry;
use App\Filament\Admin\Resources\Inquiries\RelationManagers\FieldsRelationManager;
use App\Filament\Admin\Resources\Inquiries\Schemas\InquiryForm;
use App\Filament\Admin\Resources\Inquiries\Schemas\InquiryInfolist;
use App\Filament\Admin\Resources\Inquiries\Tables\InquiriesTable;
use App\Filament\Traits\UseResourceUrlsTrait;
use App\Models\Inquiry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InquiryResource extends Resource
{
    use UseResourceUrlsTrait;

    protected static ?string $model = Inquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Anfragen';

    protected static ?string $modelLabel = 'Anfrage';

    protected static ?string $pluralModelLabel = 'Anfragen';

    protected static string|null|UnitEnum $navigationGroup = 'Customer Management';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return InquiryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InquiryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InquiriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            FieldsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInquiries::route('/'),
            'create' => CreateInquiry::route('/create'),
            'view' => ViewInquiry::route('/{record}'),
            'edit' => EditInquiry::route('/{record}/edit'),
        ];
    }
}
