# E-Mail Dokumenten-Anhänge

## Übersicht

Das E-Mail-System wurde erweitert, um **allgemeine Dokumente** als Anhänge an E-Mails anzuhängen. Dies ermöglicht es, standardisierte Dokumente wie Widerrufsbelehrungen, AGB oder Info-Broschüren einfach zu E-Mails hinzuzufügen.

## Features

### ✅ Dokumentenauswahl beim E-Mail-Erstellen
- Multi-Select Dropdown mit allen allgemeinen Dokumenten
- Nur Dokumente ohne Zuordnung (allgemeine Dokumente) stehen zur Auswahl
- Nur aktuelle Versionen werden angezeigt
- Gruppiert nach Dokumententyp
- Zeigt Dateigröße an

### ✅ Automatischer Versand
- Dokumente werden automatisch beim E-Mail-Versand angehängt
- Verwendet die tatsächlichen Dateien aus dem Storage
- Korrekte MIME-Types werden verwendet

### ✅ Anzeige in der Detailansicht
- Übersichtliche Darstellung aller angehängten Dokumente
- Badges für Dokumententypen
- Download-Links
- Links zu den Dokument-Details

## Datenbankstruktur

### Tabelle: `document_email` (Pivot-Tabelle)

| Feld | Typ | Beschreibung |
|------|-----|--------------|
| `id` | bigint | Primärschlüssel |
| `document_id` | bigint | Fremdschlüssel zu documents |
| `email_id` | bigint | Fremdschlüssel zu emails |
| `created_at` | timestamp | Erstellungsdatum |
| `updated_at` | timestamp | Aktualisierungsdatum |

**Unique Constraint**: `(document_id, email_id)` - Verhindert Duplikate

## Models

### Email Model - Neue Beziehung

```php
public function documents(): BelongsToMany
{
    return $this->belongsToMany(Document::class, 'document_email')
        ->withTimestamps();
}
```

### Document Model - Neue Beziehung

```php
public function emails(): BelongsToMany
{
    return $this->belongsToMany(Email::class, 'document_email')
        ->withTimestamps();
}
```

## Verwendung

### 1. E-Mail mit Dokumenten erstellen (Filament UI)

1. Zu "E-Mails" navigieren
2. "Neu erstellen" klicken
3. E-Mail-Formular ausfüllen
4. In der **"Anhänge"** Section:
   - Dokumente aus dem Dropdown auswählen
   - Mehrere Dokumente möglich
   - Nur allgemeine Dokumente (ohne Zuordnung) werden angezeigt
5. E-Mail speichern und senden

### 2. Programmatisch Dokumente anhängen

```php
$email = Email::create([
    Email::from_email => 'info@foerdertafel.de',
    Email::to_email => 'kunde@example.com',
    Email::subject => 'Willkommen',
    Email::body_html => '<p>Herzlich willkommen!</p>',
    // ...
]);

// Dokumente anhängen
$email->documents()->attach([1, 2, 3]); // Document IDs

// Oder einzeln
$document = Document::find($id);
$email->documents()->attach($document);

// E-Mail versenden
$email->send();
```

### 3. Allgemeine Dokumente für E-Mail-Auswahl abrufen

```php
// Alle allgemeinen Dokumente (für Dropdown)
$generalDocuments = Document::general()
    ->where(Document::is_current_version, true)
    ->orderBy(Document::type)
    ->orderBy(Document::title)
    ->get();

// Nur bestimmte Typen
$emailDocuments = Document::general()
    ->where(Document::is_current_version, true)
    ->whereIn(Document::type, [
        Document::TYPE_REVOCATION_POLICY,
        Document::TYPE_INFO_BROCHURE,
        Document::TYPE_TERMS_CONDITIONS,
    ])
    ->get();
```

### 4. Dokumente von einer E-Mail abrufen

```php
$email = Email::find($id);

// Alle angehängten Dokumente
$documents = $email->documents;

// Anzahl angehängter Dokumente
$count = $email->documents()->count();

// Prüfen ob Dokumente angehängt sind
if ($email->documents()->exists()) {
    // ...
}
```

### 5. E-Mails mit bestimmtem Dokument finden

```php
$document = Document::find($id);

// Alle E-Mails mit diesem Dokument
$emails = $document->emails;

// Nur gesendete E-Mails
$sentEmails = $document->emails()
    ->where(Email::status, Email::STATUS_SENT)
    ->get();
```

## SendEmailJob Erweiterung

Der `SendEmailJob` wurde erweitert, um Dokumente aus der `documents`-Beziehung automatisch anzuhängen:

```php
// In SendEmailJob::handle()
foreach ($this->email->documents as $document) {
    $fullPath = storage_path('app/public/' . $document->file_path);
    if (file_exists($fullPath)) {
        $message->attach(
            $fullPath,
            [
                'as' => $document->file_name,
                'mime' => $document->mime_type ?? 'application/pdf',
            ]
        );
    }
}
```

## EmailForm - Dokumentenauswahl

Die `EmailForm` enthält jetzt eine neue Section "Anhänge":

```php
Section::make('Anhänge')
    ->schema([
        Select::make('documents')
            ->label('Dokumente anhängen')
            ->multiple()
            ->relationship(
                'documents',
                'title',
                fn ($query) => $query
                    ->general() // Nur allgemeine Dokumente
                    ->where(Document::is_current_version, true)
                    ->orderBy(Document::type)
                    ->orderBy(Document::title)
            )
            ->getOptionLabelFromRecordUsing(fn (Document $record) => 
                $record->getTypeLabel() . ': ' . $record->title . ' (' . $record->getFileSizeHuman() . ')'
            )
            ->searchable(['title', 'description'])
            ->preload()
            ->columnSpanFull()
            ->helperText('Wählen Sie allgemeine Dokumente aus, die als Anhänge beigefügt werden sollen'),
    ])
    ->collapsible()
```

**Features:**
- Multi-Select (mehrere Dokumente auswählbar)
- Filtert automatisch nur allgemeine Dokumente (ohne Zuordnung)
- Zeigt Dokumententyp, Titel und Dateigröße
- Durchsuchbar nach Titel und Beschreibung
- Preload für schnellere Ladezeiten

## EmailInfolist - Anhänge anzeigen

Die `EmailInfolist` zeigt angehängte Dokumente in einer übersichtlichen Liste:

```php
Section::make('Anhänge')
    ->schema([
        RepeatableEntry::make('documents')
            ->label('Angehängte Dokumente')
            ->schema([
                TextEntry::make(Document::type)
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn (Document $record) => $record->getTypeLabel()),
                
                TextEntry::make(Document::title)
                    ->label('Titel')
                    ->url(fn (Document $record) => DocumentResource::getViewUrl($record)),
                
                TextEntry::make(Document::file_size)
                    ->label('Größe')
                    ->formatStateUsing(fn (Document $record) => $record->getFileSizeHuman()),
                
                TextEntry::make(Document::file_path)
                    ->label('Download')
                    ->formatStateUsing(fn () => 'Herunterladen')
                    ->url(fn (Document $record) => Storage::url($record->file_path))
                    ->openUrlInNewTab(),
            ])
            ->columns(4)
    ])
    ->visible(fn ($record) => $record->documents()->exists())
```

**Features:**
- Zeigt alle angehängten Dokumente als Tabelle
- Typ als Badge mit Farbcodierung
- Titel als Link zur Dokument-Detailansicht
- Dateigröße formatiert
- Download-Button
- Section wird nur angezeigt wenn Dokumente vorhanden sind

## Beispiel-Workflows

### Workflow 1: Willkommens-E-Mail mit Standarddokumenten

```php
// 1. Allgemeine Dokumente vorbereiten
$widerruf = Document::create([
    Document::type => Document::TYPE_REVOCATION_POLICY,
    Document::title => 'Widerrufsbelehrung 2026',
    Document::file_path => 'documents/widerruf.pdf',
]);

$agb = Document::create([
    Document::type => Document::TYPE_TERMS_CONDITIONS,
    Document::title => 'AGB Fördertafel',
    Document::file_path => 'documents/agb.pdf',
]);

// 2. E-Mail an Neukunden erstellen
$email = Email::create([
    Email::from_email => 'info@foerdertafel.de',
    Email::to_email => $customer->email,
    Email::to_name => $customer->name,
    Email::subject => 'Willkommen bei der Fördertafel',
    Email::body_html => view('emails.welcome', compact('customer'))->render(),
    Email::customer_id => $customer->id,
]);

// 3. Standarddokumente anhängen
$email->documents()->attach([$widerruf->id, $agb->id]);

// 4. E-Mail versenden
$email->send();
```

### Workflow 2: E-Mail-Template mit Standardanhängen

```php
class WelcomeEmailTemplate
{
    public static function send(Customer $customer): Email
    {
        // E-Mail erstellen
        $email = Email::create([
            Email::direction => Email::DIRECTION_OUTBOUND,
            Email::status => Email::STATUS_DRAFT,
            Email::from_email => 'info@foerdertafel.de',
            Email::from_name => 'Fördertafel Team',
            Email::to_email => $customer->email,
            Email::to_name => $customer->name,
            Email::subject => 'Willkommen bei der Fördertafel',
            Email::body_html => view('emails.welcome', compact('customer'))->render(),
            Email::customer_id => $customer->id,
        ]);

        // Standarddokumente automatisch anhängen
        $standardDocuments = Document::general()
            ->where(Document::is_current_version, true)
            ->whereIn(Document::type, [
                Document::TYPE_REVOCATION_POLICY,
                Document::TYPE_TERMS_CONDITIONS,
                Document::TYPE_INFO_BROCHURE,
            ])
            ->get();

        $email->documents()->attach($standardDocuments->pluck('id'));

        // Versenden
        $email->send();

        return $email;
    }
}
```

### Workflow 3: Vertrags-E-Mail mit kundenspezifischen und allgemeinen Dokumenten

```php
// Kundenspezifischer Vertrag
$contract = $customer->documents()->create([
    Document::type => Document::TYPE_CONTRACT,
    Document::title => "Mietvertrag #{$rental->id}",
    Document::file_path => $contractPath,
]);

// E-Mail erstellen
$email = Email::create([
    Email::to_email => $customer->email,
    Email::to_name => $customer->name,
    Email::subject => 'Ihr Mietvertrag',
    Email::body_html => '<p>Anbei finden Sie Ihren Mietvertrag...</p>',
    Email::customer_id => $customer->id,
    Email::rental_id => $rental->id,
]);

// Kundenspezifischen Vertrag über attachments (altes System)
$email->update([
    Email::attachments => [
        [
            'path' => $contract->file_path,
            'name' => $contract->file_name,
            'mime' => $contract->mime_type,
        ]
    ]
]);

// Allgemeine Dokumente über documents-Beziehung
$generalDocs = Document::general()
    ->where(Document::is_current_version, true)
    ->whereIn(Document::type, [
        Document::TYPE_REVOCATION_POLICY,
        Document::TYPE_TERMS_CONDITIONS,
    ])
    ->get();

$email->documents()->attach($generalDocs->pluck('id'));

// Versenden
$email->send();
```

## Migration ausführen

```bash
# In Docker
docker exec laravel php artisan migrate --path=database/migrations/2026_02_03_150000_create_document_email_table.php

# Lokal
php artisan migrate --path=database/migrations/2026_02_03_150000_create_document_email_table.php
```

## Best Practices

### 1. ✅ Allgemeine Dokumente vorbereiten
Erstellen Sie standardisierte Dokumente ohne Zuordnung:
- Widerrufsbelehrung
- AGB
- Info-Broschüren
- Datenschutzerklärung

### 2. ✅ Versionierung nutzen
Wenn Sie ein allgemeines Dokument aktualisieren:
- Erstellen Sie eine neue Version
- Die E-Mail-Auswahl zeigt automatisch nur die aktuelle Version

### 3. ✅ Dokumententypen nutzen
Verwenden Sie die vordefinierten Typen für konsistente Darstellung

### 4. ✅ Zwei Anhang-Systeme
- **documents-Beziehung**: Für allgemeine, wiederverwendbare Dokumente
- **attachments JSON-Feld**: Für einmalige oder kundenspezifische Anhänge

### 5. ✅ Storage prüfen
Der SendEmailJob prüft automatisch ob Dateien existieren bevor sie angehängt werden

## Vorteile

### 🎯 Wiederverwendbarkeit
- Einmal hochladen, mehrfach verwenden
- Keine Duplikate
- Konsistente Dokumente

### 📊 Nachvollziehbarkeit
- Sehen Sie welche E-Mails ein Dokument enthalten
- Sehen Sie alle Anhänge einer E-Mail
- Audit-Trail über Timestamps

### 🔄 Versionierung
- Automatische Verwendung aktueller Versionen
- Historische E-Mails behalten alte Versionen
- Änderungen nachvollziehbar

### 🚀 Effizienz
- Schnelle Dokumentenauswahl
- Multi-Select für mehrere Dokumente
- Vorsortiert nach Typ

### 💾 Speicherplatz
- Dokumente werden nur einmal gespeichert
- Referenzen in der Pivot-Tabelle

## Troubleshooting

### Problem: Dokumente werden nicht angezeigt
- Prüfen Sie ob Dokumente als "allgemein" markiert sind (keine documentable_id)
- Prüfen Sie ob is_current_version = true

### Problem: Anhänge fehlen in E-Mail
- Prüfen Sie Storage-Pfad: `storage/app/public/documents/`
- Prüfen Sie Dateiberechtigungen
- Prüfen Sie SendEmailJob Logs

### Problem: Zu große Anhänge
- Prüfen Sie SMTP-Limits Ihres Mail-Servers
- Filtern Sie große Dokumente in der Auswahl aus
- Erwägen Sie Links statt Anhänge für sehr große Dateien

## Dateien

### Neu erstellt
- ✅ `database/migrations/2026_02_03_150000_create_document_email_table.php`
- ✅ `docs/EMAIL_DOCUMENT_ATTACHMENTS.md`

### Geändert
- ✅ `app/Models/Email.php` - documents() Beziehung
- ✅ `app/Models/Document.php` - emails() Beziehung
- ✅ `app/Filament/Admin/Resources/Emails/Schemas/EmailForm.php` - Anhänge Section
- ✅ `app/Filament/Admin/Resources/Emails/Schemas/EmailInfolist.php` - Anhänge Anzeige
- ✅ `app/Jobs/SendEmailJob.php` - Dokumente anhängen

## Zusammenfassung

Das E-Mail-System unterstützt jetzt:
- ✅ Auswahl allgemeiner Dokumente beim E-Mail-Erstellen
- ✅ Automatisches Anhängen beim Versand
- ✅ Übersichtliche Anzeige in der Detailansicht
- ✅ Many-to-Many Beziehung über Pivot-Tabelle
- ✅ Vollständige Integration in Filament UI

**Bereit für**: Migration und Testing! 🚀
