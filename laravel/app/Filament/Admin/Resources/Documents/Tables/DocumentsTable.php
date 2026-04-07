<?php

namespace App\Filament\Admin\Resources\Documents\Tables;

use App\Models\Document;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make(Document::type)
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->getTypeLabel())
                    ->color(fn ($state) => match ($state) {
                        Document::TYPE_CONTRACT => 'success',
                        Document::TYPE_INVOICE => 'warning',
                        Document::TYPE_SEPA_MANDATE => 'info',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make(Document::title)
                    ->label('Titel')
                    ->sortable()
                    ->searchable()
                    ->limit(40),

                TextColumn::make('documentable.name')
                    ->label('Zugeordnet zu')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->placeholder('Allgemeines Dokument')
                    ->badge()
                    ->color(fn ($record) => $record->isGeneralDocument() ? 'gray' : 'primary'),

                TextColumn::make(Document::file_name)
                    ->label('Datei')
                    ->limit(30)
                    ->searchable(),

                TextColumn::make(Document::file_size)
                    ->label('Größe')
                    ->formatStateUsing(fn ($record) => $record->getFileSizeHuman())
                    ->sortable(),

                TextColumn::make(Document::version)
                    ->label('Version')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make(Document::is_current_version)
                    ->label('Aktuell')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Ja' : 'Nein')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->sortable(),

                IconColumn::make(Document::is_public)
                    ->label('Public'),

                TextColumn::make('uploadedBy.name')
                    ->label('Hochgeladen von')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Hochgeladen am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make(Document::type)
                    ->label('Dokumententyp')
                    ->options(Document::getTypes())
                    ->native(false),

                TernaryFilter::make(Document::is_current_version)
                    ->label('Nur aktuelle Versionen')
                    ->default(true)
                    ->queries(
                        true: fn ($query) => $query->where(Document::is_current_version, true),
                        false: fn ($query) => $query->where(Document::is_current_version, false),
                        blank: fn ($query) => $query,
                    ),

                TernaryFilter::make('assignment_status')
                    ->label('Zuordnungsstatus')
                    ->placeholder('Alle Dokumente')
                    ->trueLabel('Nur zugeordnete Dokumente')
                    ->falseLabel('Nur allgemeine Dokumente')
                    ->queries(
                        true: fn ($query) => $query->assigned(),
                        false: fn ($query) => $query->general(),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('download')
                    ->label('Vorschau')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (Document $record) => route('documents.show', $record))
                    ->openUrlInNewTab(),
                Action::make('newVersion')
                    ->label('Neue Version')
                    ->icon('heroicon-o-document-plus')
                    ->color('info')
                    ->visible(fn ($record) => $record->is_current_version)
                    ->schema([
                        FileUpload::make('file')
                            ->label('Neue Dateiversion')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->disk('public')
                            ->directory('documents')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $file = $data['file'];

                        // Observer wird automatisch mime_type, file_size und file_name setzen
                        $record->createNewVersion([
                            Document::file_path => $file,
                        ]);
                    })
                    ->successNotificationTitle('Neue Version erfolgreich erstellt')
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
