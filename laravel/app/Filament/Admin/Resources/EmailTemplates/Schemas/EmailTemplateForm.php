<?php

namespace App\Filament\Admin\Resources\EmailTemplates\Schemas;

use App\Models\Document;
use App\Models\EmailTemplate;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Template-Informationen')
                    ->schema([
                        TextInput::make(EmailTemplate::name)
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) =>
                                $set(EmailTemplate::slug, Str::slug($state))
                            ),

                        TextInput::make(EmailTemplate::slug)
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Eindeutiger Bezeichner für die Vorlage'),

                        Select::make(EmailTemplate::category)
                            ->label('Kategorie')
                            ->options(EmailTemplate::getCategoryOptions())
                            ->native(false)
                            ->required(),

                        Toggle::make(EmailTemplate::is_active)
                            ->label('Aktiv')
                            ->default(true)
                            ->inline(false),

                        Textarea::make(EmailTemplate::description)
                            ->label('Beschreibung')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Interne Beschreibung des Verwendungszwecks'),
                    ])
                    ->columns(2)
                    ->columnSpan(2),

                Section::make('Verfügbare Platzhalter')
                    ->schema([
                        TextEntry::make('variables_info')
                            ->label('')
                            ->state(function () {
                                $variables = EmailTemplate::getDefaultVariables();
                                $html = '<div class="text-sm">';
                                foreach ($variables as $key => $description) {
                                    $html .= '<div class="mb-2">';
                                    $html .= '<code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ ' . $key . ' }}</code>';
                                    $html .= '<div class="text-gray-600 mt-1">' . $description . '</div>';
                                    $html .= '</div>';
                                }
                                $html .= '</div>';
                                return new HtmlString($html);
                            }),

                        TagsInput::make(EmailTemplate::available_variables)
                            ->label('Verwendete Variablen')
                            ->helperText('Dokumentation der in dieser Vorlage verwendeten Platzhalter')
                            ->placeholder('Variable hinzufügen'),
                    ])
                    ->columnSpan(1),

                Section::make('E-Mail-Absender')
                    ->schema([
                        TextInput::make(EmailTemplate::from_email)
                            ->label('Absender E-Mail')
                            ->email()
                            ->maxLength(255)
                            ->helperText('Leer lassen für Standard-Absender'),

                        TextInput::make(EmailTemplate::from_name)
                            ->label('Absender Name')
                            ->maxLength(255)
                            ->helperText('Leer lassen für Standard-Name'),

                        TextInput::make(EmailTemplate::reply_to)
                            ->label('Antwort an')
                            ->email()
                            ->maxLength(255)
                            ->helperText('Leer lassen für Standard'),
                    ])
                    ->columns(3)
                    ->columnSpan(3),

                Section::make('E-Mail-Inhalt')
                    ->schema([
                        TextInput::make(EmailTemplate::subject)
                            ->label('Betreff')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->helperText('Verwenden Sie {{ variable }} für Platzhalter'),

                        Textarea::make(EmailTemplate::body_text)
                            ->label('Text-Version')
                            ->rows(8)
                            ->columnSpanFull()
                            ->helperText('Plain-Text Version der E-Mail (optional)'),

                        RichEditor::make(EmailTemplate::body_html)
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
                                'blockquote',
                                'codeBlock',
                                'undo',
                                'redo',
                            ])
                            ->helperText('Verwenden Sie {{ variable }} für Platzhalter'),
                    ])
                    ->columnSpan(3),

                Section::make('Anhänge')
                    ->description('Dokumente, die bei jeder E-Mail dieser Vorlage automatisch angehängt werden')
                    ->schema([
                        Select::make('documents')
                            ->label('Dokumente')
                            ->relationship('documents', 'title')
                            ->options(
                                Document::query()
                                    ->where(Document::is_current_version, true)
                                    ->orderBy(Document::title)
                                    ->get()
                                    ->mapWithKeys(fn (Document $doc) => [
                                        $doc->id => "{$doc->title} ({$doc->getTypeLabel()})",
                                    ])
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Diese Dokumente werden automatisch an alle E-Mails dieser Vorlage angehängt.')
                            ->native(false),
                    ])
                    ->columnSpan(3),
            ]);
    }
}
