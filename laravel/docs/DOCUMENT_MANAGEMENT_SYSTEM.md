# Document Management System

## Übersicht

Das Document Management System ermöglicht die Verwaltung von Kundendokumenten mit Versionierung. Primär werden Dokumente für Kunden verwendet, um Mietverträge, Rechnungen und SEPA-Mandate zu verwalten.

## Features

### 1. Dokumententypen
- **Mietvertrag** (`contract`): Verträge zwischen Kunde und Unternehmen
- **Rechnung** (`invoice`): Rechnungen für Mietzahlungen
- **SEPA-Mandat** (`sepa_mandate`): Lastschriftmandate für automatische Zahlungen
- **Widerrufsbelehrung** (`revocation_policy`): Allgemeine Widerrufsbelehrung
- **Info-Broschüre** (`info_brochure`): Informationsmaterialien
- **AGB** (`terms_conditions`): Allgemeine Geschäftsbedingungen
- **Sonstiges** (`other`): Weitere Dokumente

### 2. Allgemeine vs. Zugeordnete Dokumente

Dokumente können in zwei Kategorien eingeteilt werden:

**Allgemeine Dokumente** (ohne Zuordnung):
- Widerrufsbelehrungen
- Info-Broschüren
- AGB
- Können als Anhänge für E-Mails verwendet werden
- Sind für alle Kunden verfügbar

**Zugeordnete Dokumente**:
- Kundenspezifische Verträge
- Rechnungen
- SEPA-Mandate
- Sind einem bestimmten Kunden zugeordnet

### 3. Versionierung
- Dokumente können versioniert werden
- Nur eine Version ist als "aktuelle Version" markiert
- Alle Versionshistorie bleibt erhalten
- Einfaches Erstellen neuer Versionen über die UI

### 3. Polymorphe Beziehungen
Dokumente können verschiedenen Entitäten zugeordnet werden:
- **Customer** (primär)
- Location (zukünftig)
- Board (zukünftig)
- Field (zukünftig)
- Rental (zukünftig)

### 4. Metadaten
Flexible JSON-basierte Metadaten für zusätzliche Informationen:
- Vertragsnummer
- Rechnungsnummer
- SEPA-Mandats-Referenz
- Beliebige weitere Felder

## Datenbankstruktur

### Tabelle: `documents`

| Feld | Typ | Beschreibung |
|------|-----|--------------|
| `id` | bigint | Primärschlüssel |
| `type` | string | Dokumententyp (contract, invoice, sepa_mandate, other) |
| `title` | string | Dokumententitel |
| `description` | text | Optionale Beschreibung |
| `file_path` | string | Speicherpfad der Datei |
| `file_name` | string | Original-Dateiname |
| `mime_type` | string | MIME-Typ (hauptsächlich application/pdf) |
| `file_size` | bigint | Dateigröße in Bytes |
| `version` | integer | Versionsnummer (Standard: 1) |
| `parent_document_id` | bigint | Referenz zum Ursprungsdokument |
| `is_current_version` | boolean | Ist dies die aktuelle Version? |
| `documentable_type` | string | Polymorphe Relation - Entitätstyp |
| `documentable_id` | bigint | Polymorphe Relation - Entitäts-ID |
| `metadata` | json | Zusätzliche Metadaten |
| `uploaded_by` | bigint | User-ID des Uploaders |
| `created_at` | timestamp | Erstellungsdatum |
| `updated_at` | timestamp | Aktualisierungsdatum |
| `deleted_at` | timestamp | Soft-Delete Timestamp |

### Indizes
- `documentable_type`, `documentable_id` (für schnelle Abfragen)
- `type` (für Filterung nach Dokumententyp)
- `is_current_version` (für Abfrage aktueller Versionen)

## Model: Document

### Observer

Der `DocumentObserver` kümmert sich automatisch um:
- **mime_type**: Wird aus der hochgeladenen Datei ermittelt
- **file_size**: Wird aus der hochgeladenen Datei ermittelt (in Bytes)
- **file_name**: Wird aus dem Dateipfad extrahiert

Diese Felder müssen beim Erstellen nicht manuell gesetzt werden.

### Konstanten

#### Dokumententypen
```php
Document::TYPE_CONTRACT           // 'contract'
Document::TYPE_INVOICE            // 'invoice'
Document::TYPE_SEPA_MANDATE       // 'sepa_mandate'
Document::TYPE_REVOCATION_POLICY  // 'revocation_policy'
Document::TYPE_INFO_BROCHURE      // 'info_brochure'
Document::TYPE_TERMS_CONDITIONS   // 'terms_conditions'
Document::TYPE_OTHER              // 'other'
```

#### Feldnamen
```php
Document::type
Document::title
Document::description
Document::file_path
Document::file_name
Document::mime_type
Document::file_size
Document::version
Document::parent_document_id
Document::is_current_version
Document::documentable_type
Document::documentable_id
Document::metadata
Document::uploaded_by
```

### Beziehungen

```php
// Polymorphe Beziehung zur übergeordneten Entität
$document->documentable; // Customer, Location, etc.

// Benutzer, der das Dokument hochgeladen hat
$document->uploadedBy;

// Ursprungsdokument (bei versionierten Dokumenten)
$document->parentDocument;

// Alle Versionen dieses Dokuments
$document->versions;
```

### Hilfsmethoden

```php
// Dokumententypen als Array
Document::getTypes();

// Typ-Label abrufen
$document->getTypeLabel(); // "Mietvertrag"

// Dateigröße lesbar formatieren
$document->getFileSizeHuman(); // "2.5 MB"

// Vollständiger Speicherpfad
$document->getFullPath();

// Download-URL
$document->getDownloadUrl();

// PDF-Prüfung
$document->isPdf(); // true/false

// Zuordnungsstatus prüfen
$document->isAssigned(); // Ist das Dokument zugeordnet?
$document->isGeneralDocument(); // Ist es ein allgemeines Dokument?

// Neue Version erstellen
$newVersion = $document->createNewVersion([
    Document::file_path => 'path/to/new/file.pdf',
    // mime_type, file_size und file_name werden automatisch vom Observer gesetzt
]);

// Neueste Version abrufen
$latestVersion = $document->getLatestVersion();

// Alle Versionen abrufen
$allVersions = $document->getAllVersions();

// Query Scopes
Document::general()->get(); // Alle allgemeinen Dokumente (ohne Zuordnung)
Document::assigned()->get(); // Alle zugeordneten Dokumente
```

## Verwendung in anderen Models

### Customer Model

```php
// Beziehung zu Dokumenten
$customer->documents; // Alle Dokumente des Kunden

// Dokument erstellen
$customer->documents()->create([
    Document::type => Document::TYPE_CONTRACT,
    Document::title => 'Mietvertrag 2026',
    Document::file_path => 'documents/contract.pdf',
    Document::uploaded_by => auth()->id(),
    // mime_type, file_size und file_name werden automatisch vom Observer gesetzt
]);

// Spezifische Dokumenttypen abrufen
$contracts = $customer->documents()
    ->where(Document::type, Document::TYPE_CONTRACT)
    ->where(Document::is_current_version, true)
    ->get();
```

## Filament Resource

### DocumentResource

Die Filament Resource bietet eine vollständige Verwaltungsoberfläche:

#### Features
- **Erstellen**: Neues Dokument hochladen mit Typ, Titel, Beschreibung
- **Anzeigen**: Detailansicht mit allen Informationen und Download-Link
- **Bearbeiten**: Dokument-Metadaten ändern
- **Neue Version**: Direkt aus der Tabelle eine neue Version hochladen
- **Download**: Dokument herunterladen
- **Filter**: Nach Typ und aktuellen Versionen filtern
- **Suche**: Nach Titel, Typ, Dateiname und Kunde suchen

#### Formulare (DocumentForm)
- Dokumententyp (Select)
- Titel (TextInput)
- Beschreibung (Textarea)
- Zuordnung zu Kunde (MorphToSelect)
- Datei-Upload (nur PDF, max 10 MB)
- Metadaten (KeyValue für zusätzliche Felder)

#### Tabelle (DocumentsTable)
Spalten:
- Typ (Badge mit Farben)
- Titel
- Kunde
- Dateiname
- Dateigröße
- Version
- Aktuelle Version (Badge)
- Hochgeladen von (toggleable)
- Hochgeladen am (toggleable)

Aktionen:
- Anzeigen
- Bearbeiten
- Herunterladen
- Neue Version erstellen

Filter:
- Dokumententyp
- Nur aktuelle Versionen (Standard: aktiviert)

#### Detailansicht (DocumentInfolist)
Strukturierte Anzeige in Sections:
- Dokumentinformationen
- Dateiinformationen mit Download
- Versionsinformationen
- Metadaten (wenn vorhanden)

## Workflows

### 1. Neues Dokument hochladen

1. In der Filament-Oberfläche zu "Dokumente" navigieren
2. "Neu erstellen" klicken
3. Formular ausfüllen:
   - Dokumententyp wählen
   - Titel eingeben
   - Optional: Beschreibung
   - Kunde auswählen
   - PDF-Datei hochladen
   - Optional: Metadaten hinzufügen
4. Speichern

### 2. Neue Version eines Dokuments erstellen

**Option A: Über die Tabelle**
1. In der Dokumentenliste die Zeile mit dem gewünschten Dokument finden
2. Auf das "Neue Version" Icon klicken
3. Neue PDF-Datei hochladen
4. Bestätigen

**Option B: Programmatisch**
```php
$document = Document::find($id);
$newVersion = $document->createNewVersion([
    'file_path' => Storage::putFile('documents', $newFile),
    'file_name' => $newFile->getClientOriginalName(),
    'mime_type' => $newFile->getMimeType(),
    'file_size' => $newFile->getSize(),
]);
```

### 3. Dokument per E-Mail versenden

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentMail;

$document = Document::find($id);
$customer = $document->documentable;

Mail::to($customer->email)->send(new DocumentMail($document));
```

### 4. Dokument per E-Mail versenden

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentMail;

$document = Document::find($id);
$customer = $document->documentable;

Mail::to($customer->email)->send(new DocumentMail($document));
```

### 5. Allgemeine Dokumente für E-Mail-Anhänge abrufen

```php
// Alle aktuellen allgemeinen Dokumente (z.B. für E-Mail-Auswahl)
$generalDocuments = Document::general()
    ->where(Document::is_current_version, true)
    ->whereIn(Document::type, [
        Document::TYPE_REVOCATION_POLICY,
        Document::TYPE_INFO_BROCHURE,
        Document::TYPE_TERMS_CONDITIONS,
    ])
    ->orderBy('created_at', 'desc')
    ->get();

// Verwendung bei E-Mail-Versand
$attachments = $generalDocuments->pluck('file_path')->toArray();
```

### 6. Alle aktuellen Kundendokumente anzeigen

```php
$customer = Customer::find($id);
$currentDocuments = $customer->documents()
    ->where(Document::is_current_version, true)
    ->orderBy('created_at', 'desc')
    ->get();
```

## Migration ausführen

```bash
# In Docker
docker exec laravel php artisan migrate --path=database/migrations/2026_02_03_013114_create_documents_table.php

# Lokal
php artisan migrate --path=database/migrations/2026_02_03_013114_create_documents_table.php
```

## Sicherheitshinweise

1. **Dateityp-Validierung**: Nur PDF-Dateien werden akzeptiert
2. **Dateigröße**: Maximum 10 MB
3. **Soft Deletes**: Gelöschte Dokumente werden nicht physisch entfernt
4. **Zugriffskontrolle**: Implementieren Sie Policies für dokumentspezifische Berechtigungen
5. **Storage**: Dateien werden im privaten Storage gespeichert

## Erweiterungsmöglichkeiten

### 1. Weitere Entitätstypen hinzufügen

```php
// In DocumentForm.php - MorphToSelect erweitern
MorphToSelect::make('documentable')
    ->types([
        MorphToSelect\Type::make(Customer::class)->titleAttribute('name'),
        MorphToSelect\Type::make(Location::class)->titleAttribute('name'),
        MorphToSelect\Type::make(Rental::class)->titleAttribute('id'),
    ])
```

### 2. Automatische Dokumentengenerierung

```php
// Service für Vertragserstellung
class ContractGenerator
{
    public function generateContract(Customer $customer, Rental $rental): Document
    {
        $pdf = PDF::loadView('contracts.rental', [
            'customer' => $customer,
            'rental' => $rental,
        ]);
        
        $filename = "vertrag_{$customer->id}_{$rental->id}.pdf";
        $path = Storage::putFileAs('documents', $pdf->output(), $filename);
        
        return $customer->documents()->create([
            Document::type => Document::TYPE_CONTRACT,
            Document::title => "Mietvertrag #{$rental->id}",
            Document::file_path => $path,
            Document::metadata => [
                'rental_id' => $rental->id,
                'contract_number' => "MV-{$rental->id}",
            ],
            // mime_type, file_size und file_name werden automatisch vom Observer gesetzt
        ]);
    }
}
```

### 3. Dokumenten-Vorschau

```php
// In DocumentInfolist.php
TextEntry::make('preview')
    ->label('Vorschau')
    ->view('components.pdf-preview')
    ->viewData(fn ($record) => ['url' => $record->getDownloadUrl()])
```

### 4. E-Mail-Benachrichtigungen

```php
// Observer für automatische Benachrichtigungen
class DocumentObserver
{
    public function created(Document $document): void
    {
        if ($document->documentable instanceof Customer) {
            Mail::to($document->documentable->email)
                ->send(new NewDocumentNotification($document));
        }
    }
}
```

## Best Practices

1. **Immer aktuelle Version verwenden**: Filtere standardmäßig nach `is_current_version = true`
2. **Metadaten nutzen**: Speichere wichtige Referenznummern in den Metadaten
3. **Versionierung**: Erstelle neue Versionen statt bestehende zu überschreiben
4. **Dokumenttypen**: Verwende die vordefinierten Konstanten
5. **Soft Deletes**: Nutze Soft Deletes für bessere Nachvollziehbarkeit

## Troubleshooting

### Problem: Datei wird nicht hochgeladen
- Prüfe die `php.ini` Einstellungen: `upload_max_filesize` und `post_max_size`
- Prüfe Storage-Berechtigungen: `storage/app/documents`

### Problem: Download-Link funktioniert nicht
- Prüfe, ob `APP_URL` in der `.env` korrekt gesetzt ist
- Stelle sicher, dass der Symlink erstellt wurde: `php artisan storage:link`

### Problem: Versionen werden nicht korrekt angezeigt
- Prüfe die Logik in `getAllVersions()` - es werden alle Dokumente mit gleicher `parent_document_id` abgerufen
- Stelle sicher, dass `is_current_version` korrekt gesetzt ist
