<?php

namespace App\Filament\Admin\Resources\EmailTemplates;

use App\Filament\Admin\Resources\EmailTemplates\Pages\CreateEmailTemplate;
use App\Filament\Admin\Resources\EmailTemplates\Pages\EditEmailTemplate;
use App\Filament\Admin\Resources\EmailTemplates\Pages\ListEmailTemplates;
use App\Filament\Admin\Resources\EmailTemplates\Pages\ViewEmailTemplate;
use App\Filament\Admin\Resources\EmailTemplates\Schemas\EmailTemplateForm;
use App\Filament\Admin\Resources\EmailTemplates\Schemas\EmailTemplateInfolist;
use App\Filament\Admin\Resources\EmailTemplates\Tables\EmailTemplatesTable;
use App\Filament\Traits\UseResourceUrlsTrait;
use App\Models\EmailTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EmailTemplateResource extends Resource
{
    use UseResourceUrlsTrait;

    protected static ?string $model = EmailTemplate::class;

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'E-Mail-Vorlagen';

    protected static ?string $modelLabel = 'E-Mail-Vorlage';

    protected static ?string $pluralModelLabel = 'E-Mail-Vorlagen';

    protected static string|null|UnitEnum $navigationGroup = 'Kommunikation';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EmailTemplateForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmailTemplateInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailTemplatesTable::configure($table);
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
            'index' => ListEmailTemplates::route('/'),
            'create' => CreateEmailTemplate::route('/create'),
            'view' => ViewEmailTemplate::route('/{record}'),
            'edit' => EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
