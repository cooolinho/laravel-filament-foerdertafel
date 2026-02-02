<?php

namespace App\Filament\Admin\Resources\EmailTemplates\Schemas;

use App\Models\EmailTemplate;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class EmailTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Template-Informationen')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make(EmailTemplate::name)
                                    ->label('Name')
                                    ->size(TextSize::Large)
                                    ->weight('bold'),

                                TextEntry::make(EmailTemplate::slug)
                                    ->label('Slug')
                                    ->badge()
                                    ->copyable(),

                                TextEntry::make(EmailTemplate::category)
                                    ->label('Kategorie')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        EmailTemplate::CATEGORY_RENTAL => 'success',
                                        EmailTemplate::CATEGORY_INQUIRY => 'info',
                                        EmailTemplate::CATEGORY_SYSTEM => 'warning',
                                        EmailTemplate::CATEGORY_MARKETING => 'primary',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string =>
                                        EmailTemplate::getCategoryOptions()[$state] ?? $state
                                    ),
                            ]),

                        IconEntry::make(EmailTemplate::is_active)
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktiv' : 'Inaktiv'),

                        TextEntry::make(EmailTemplate::description)
                            ->label('Beschreibung')
                            ->columnSpanFull()
                            ->placeholder('Keine Beschreibung'),
                    ])
                    ->columns(3),

                Section::make('E-Mail-Absender')
                    ->schema([
                        TextEntry::make(EmailTemplate::from_email)
                            ->label('Absender E-Mail')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->placeholder('Standard-Absender'),

                        TextEntry::make(EmailTemplate::from_name)
                            ->label('Absender Name')
                            ->icon('heroicon-o-user')
                            ->placeholder('Standard-Name'),

                        TextEntry::make(EmailTemplate::reply_to)
                            ->label('Antwort an')
                            ->icon('heroicon-o-arrow-uturn-left')
                            ->copyable()
                            ->placeholder('Standard'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('E-Mail-Inhalt')
                    ->schema([
                        TextEntry::make(EmailTemplate::subject)
                            ->label('Betreff')
                            ->size(TextSize::Large)
                            ->columnSpanFull(),

                        TextEntry::make(EmailTemplate::body_html)
                            ->label('HTML-Vorschau')
                            ->html()
                            ->columnSpanFull(),

                        TextEntry::make(EmailTemplate::body_text)
                            ->label('Text-Version')
                            ->columnSpanFull()
                            ->placeholder('Keine Text-Version')
                            ->visible(fn ($record) => !empty($record->body_text)),
                    ]),

                Section::make('Verfügbare Variablen')
                    ->schema([
                        TextEntry::make(EmailTemplate::available_variables)
                            ->label('Verwendete Variablen')
                            ->badge()
                            ->separator(',')
                            ->formatStateUsing(fn (string $state): string => '{{ ' . $state . ' }}')
                            ->columnSpanFull()
                            ->placeholder('Keine Variablen dokumentiert'),
                    ])
                    ->collapsible(),

                Section::make('Verwendung')
                    ->schema([
                        TextEntry::make('emails_count')
                            ->label('Anzahl gesendeter E-Mails')
                            ->state(fn ($record) => $record->emails()->count())
                            ->icon('heroicon-o-paper-airplane')
                            ->badge()
                            ->color('info'),
                    ])
                    ->collapsible(),

                Section::make('Zeitstempel')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Erstellt am')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Aktualisiert am')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-o-pencil'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
