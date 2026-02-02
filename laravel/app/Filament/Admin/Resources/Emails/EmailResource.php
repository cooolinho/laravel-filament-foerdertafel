<?php

namespace App\Filament\Admin\Resources\Emails;

use App\Filament\Admin\Resources\Emails\Pages\CreateEmail;
use App\Filament\Admin\Resources\Emails\Pages\EditEmail;
use App\Filament\Admin\Resources\Emails\Pages\ListEmails;
use App\Filament\Admin\Resources\Emails\Pages\ViewEmail;
use App\Filament\Admin\Resources\Emails\Schemas\EmailForm;
use App\Filament\Admin\Resources\Emails\Schemas\EmailInfolist;
use App\Filament\Admin\Resources\Emails\Tables\EmailsTable;
use App\Filament\Traits\UseResourceUrlsTrait;
use App\Models\Email;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EmailResource extends Resource
{
    use UseResourceUrlsTrait;

    protected static ?string $model = Email::class;

    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'E-Mails';

    protected static ?string $modelLabel = 'E-Mail';

    protected static ?string $pluralModelLabel = 'E-Mails';

    protected static string|null|UnitEnum $navigationGroup = 'Kommunikation';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function getNavigationBadge(): ?string
    {
        return (string) Email::whereNull(Email::read_at)
            ->where(Email::direction, Email::DIRECTION_INBOUND)
            ->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $unreadCount = Email::whereNull(Email::read_at)
            ->where(Email::direction, Email::DIRECTION_INBOUND)
            ->count();

        return $unreadCount > 0 ? 'danger' : 'gray';
    }

    public static function form(Schema $schema): Schema
    {
        return EmailForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmailInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailsTable::configure($table);
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
            'index' => ListEmails::route('/'),
            'create' => CreateEmail::route('/create'),
            'view' => ViewEmail::route('/{record}'),
            'edit' => EditEmail::route('/{record}/edit'),
        ];
    }
}
