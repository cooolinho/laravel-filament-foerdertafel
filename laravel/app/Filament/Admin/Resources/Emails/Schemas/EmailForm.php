<?php

namespace App\Filament\Admin\Resources\Emails\Schemas;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Email;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('E-Mail-Informationen')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make(Email::direction)
                                    ->label('Richtung')
                                    ->options(Email::getDirectionOptions())
                                    ->required()
                                    ->default(Email::DIRECTION_OUTBOUND)
                                    ->native(false),

                                Select::make(Email::status)
                                    ->label('Status')
                                    ->options(Email::getStatusOptions())
                                    ->required()
                                    ->default(Email::STATUS_DRAFT)
                                    ->native(false),

                                Select::make(Email::email_template_id)
                                    ->label('E-Mail-Vorlage')
                                    ->relationship('emailTemplate', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->createOptionForm([
                                        // Kann bei Bedarf erweitert werden
                                    ]),
                            ]),
                    ]),

                Section::make('Absender')
                    ->schema([
                        TextInput::make(Email::from_email)
                            ->label('E-Mail-Adresse')
                            ->email()
                            ->required()
                            ->default('info@foerdertafel.de')
                            ->maxLength(255),

                        TextInput::make(Email::from_name)
                            ->label('Name')
                            ->maxLength(255)
                            ->default('Fördertafel Team'),
                    ])
                    ->columns(2),

                Section::make('Empfänger')
                    ->schema([
                        Select::make(Email::customer_id)
                            ->label('Kunde')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $customer = Customer::find($state);
                                    if ($customer) {
                                        $set(Email::to_email, $customer->email);
                                        $set(Email::to_name, $customer->name);
                                    }
                                }
                            }),

                        TextInput::make(Email::to_email)
                            ->label('E-Mail-Adresse')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        TextInput::make(Email::to_name)
                            ->label('Name')
                            ->maxLength(255),

                        TextInput::make(Email::cc)
                            ->label('CC')
                            ->maxLength(255)
                            ->helperText('Mehrere E-Mails mit Komma trennen'),

                        TextInput::make(Email::bcc)
                            ->label('BCC')
                            ->maxLength(255)
                            ->helperText('Mehrere E-Mails mit Komma trennen'),

                        TextInput::make(Email::reply_to)
                            ->label('Antwort an')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Section::make('Nachricht')
                    ->schema([
                        TextInput::make(Email::subject)
                            ->label('Betreff')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make(Email::body_text)
                            ->label('Text-Version')
                            ->rows(5)
                            ->columnSpanFull(),

                        RichEditor::make(Email::body_html)
                            ->label('HTML-Version')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'orderedList',
                                'italic',
                                'link',
                                'h2',
                                'h3',
                                'undo',
                                'redo',
                            ]),
                    ]),

                Section::make('Anhänge')
                    ->schema([
                        Select::make('documents')
                            ->label('Dokumente anhängen')
                            ->multiple()
                            ->relationship(
                                'documents',
                                'title',
                                fn ($query) => $query
                                    ->general() // Nur allgemeine Dokumente (ohne Zuordnung)
                                    ->where(Document::is_current_version, true) // Nur aktuelle Versionen
                                    ->orderBy(Document::type)
                                    ->orderBy(Document::title)
                            )
                            ->getOptionLabelFromRecordUsing(fn (Document $record) =>
                                $record->getTypeLabel() . ': ' . $record->title . ' (' . $record->getFileSizeHuman() . ')'
                            )
                            ->searchable(['title', 'description'])
                            ->preload()
                            ->columnSpanFull()
                            ->helperText('Wählen Sie allgemeine Dokumente aus, die als Anhänge beigefügt werden sollen (z.B. Widerrufsbelehrung, AGB, Info-Broschüren)'),
                    ])
                    ->collapsible(),

                Section::make('Verknüpfungen')
                    ->schema([
                        Select::make(Email::rental_id)
                            ->label('Vermietung')
                            ->relationship('rental', 'id')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make(Email::user_id)
                            ->label('Benutzer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->default(fn () => auth()->id()),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Zeitstempel')
                    ->schema([
                        DateTimePicker::make(Email::sent_at)
                            ->label('Gesendet am')
                            ->seconds(false),

                        DateTimePicker::make(Email::received_at)
                            ->label('Empfangen am')
                            ->seconds(false),

                        DateTimePicker::make(Email::read_at)
                            ->label('Gelesen am')
                            ->seconds(false),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Technische Details')
                    ->schema([
                        TextInput::make(Email::message_id)
                            ->label('Message ID')
                            ->maxLength(255),

                        TextInput::make(Email::in_reply_to)
                            ->label('Als Antwort auf')
                            ->maxLength(255),

                        TextInput::make(Email::references)
                            ->label('Referenzen')
                            ->maxLength(255),

                        Textarea::make(Email::error_message)
                            ->label('Fehlermeldung')
                            ->rows(3)
                            ->columnSpanFull(),

                        KeyValue::make(Email::metadata)
                            ->label('Metadaten')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
