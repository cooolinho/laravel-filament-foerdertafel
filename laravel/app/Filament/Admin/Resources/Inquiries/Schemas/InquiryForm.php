<?php

namespace App\Filament\Admin\Resources\Inquiries\Schemas;

use App\Models\Board;
use App\Models\Field;
use App\Models\Inquiry;
use App\Models\Rental;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make(Inquiry::board_id)
                    ->label('Board')
                    ->relationship('board', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set(Inquiry::requested_fields, null)),

                Select::make(Inquiry::requested_fields)
                    ->label('Gewünschte Felder')
                    ->multiple()
                    ->options(function (callable $get) {
                        $boardId = $get(Inquiry::board_id);
                        if (!$boardId) {
                            return [];
                        }
                        return Field::where(Field::board_id, $boardId)
                            ->get()
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->helperText('Wählen Sie die gewünschten Felder aus.'),

                TextInput::make(Inquiry::customer_name)
                    ->label('Kundenname')
                    ->required()
                    ->maxLength(255),

                TextInput::make(Inquiry::customer_email)
                    ->label('Kunden E-Mail')
                    ->email()
                    ->required()
                    ->maxLength(255),

                TextInput::make(Inquiry::customer_phone)
                    ->label('Kunden Telefon')
                    ->tel()
                    ->maxLength(255),

                DatePicker::make(Inquiry::start_date)
                    ->label('Startdatum')
                    ->required()
                    ->native(false)
                    ->displayFormat('d.m.Y'),

                DatePicker::make(Inquiry::end_date)
                    ->label('Enddatum')
                    ->required()
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->afterOrEqual(Inquiry::start_date),

                Select::make(Inquiry::status)
                    ->label('Status')
                    ->options([
                        Inquiry::STATUS_PENDING => 'Ausstehend',
                        Inquiry::STATUS_APPROVED => 'Genehmigt',
                        Inquiry::STATUS_REJECTED => 'Abgelehnt',
                        Inquiry::STATUS_CONVERTED => 'Vermietung erstellt',
                    ])
                    ->default(Inquiry::STATUS_PENDING)
                    ->required(),

                Textarea::make(Inquiry::message)
                    ->label('Nachricht vom Kunden')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make(Inquiry::admin_notes)
                    ->label('Admin Notizen')
                    ->rows(3)
                    ->columnSpanFull(),

                Select::make(Inquiry::rental_id)
                    ->label('Zugehörige Vermietung')
                    ->relationship('rental', 'id')
                    ->searchable()
                    ->preload()
                    ->helperText('Wird automatisch gesetzt, wenn aus dieser Anfrage eine Vermietung erstellt wird.'),
            ]);
    }
}


