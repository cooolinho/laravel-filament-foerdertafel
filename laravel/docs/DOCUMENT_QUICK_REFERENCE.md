# Document System - Quick Reference

## Schnellzugriff

### Model verwenden

```php
use App\Models\Document;

// Neues Dokument erstellen
$document = Document::create([
    Document::type => Document::TYPE_CONTRACT,
    Document::title => 'Mietvertrag 2026',
    Document::description => 'Vertrag für Tafel XY',
    Document::file_path => 'documents/vertrag.pdf',
    Document::documentable_type => Customer::class,
    Document::documentable_id => $customer->id,
    Document::uploaded_by => auth()->id(),
    // mime_type, file_size und file_name werden automatisch vom Observer gesetzt
]);

// Dokument für Kunde erstellen
$customer->documents()->create([
    Document::type => Document::TYPE_SEPA_MANDATE,
    Document::title => 'SEPA-Mandat',
    // ... weitere Felder
    // mime_type, file_size und file_name werden automatisch vom Observer gesetzt
]);

// Neue Version erstellen
$newVersion = $document->createNewVersion([
    Document::file_path => 'documents/vertrag_v2.pdf',
    // mime_type, file_size und file_name werden automatisch vom Observer gesetzt
]);
```

### Dokumententypen

| Konstante | Wert | Label |
|-----------|------|-------|
| `Document::TYPE_CONTRACT` | `contract` | Mietvertrag |
| `Document::TYPE_INVOICE` | `invoice` | Rechnung |
| `Document::TYPE_SEPA_MANDATE` | `sepa_mandate` | SEPA-Mandat |
| `Document::TYPE_OTHER` | `other` | Sonstiges |

### Abfragen

```php
// Alle aktuellen Dokumente eines Kunden
$customer->documents()->where(Document::is_current_version, true)->get();

// Alle Verträge
Document::where(Document::type, Document::TYPE_CONTRACT)->get();

// Alle Versionen eines Dokuments
$document->getAllVersions();

// Neueste Version
$document->getLatestVersion();
```

### Hilfsmethoden

```php
$document->getTypeLabel();        // "Mietvertrag"
$document->getFileSizeHuman();    // "2.5 MB"
$document->getDownloadUrl();      // URL zum Download
$document->isPdf();               // true/false
```

### Migration

```bash
docker exec laravel php artisan migrate --path=database/migrations/2026_02_03_013114_create_documents_table.php
```

### In Customer Model verfügbar

```php
$customer->documents; // Alle Dokumente
```

## Filament Features

- ✅ Upload nur PDF (max 10 MB)
- ✅ Versionierung mit "Neue Version" Button
- ✅ Download-Funktion
- ✅ Metadaten als Key-Value Felder
- ✅ Optionale Zuordnung zu Kunden (oder leer für allgemeine Dokumente)
- ✅ Filter nach Typ, aktuellen Versionen und Zuordnungsstatus
- ✅ Soft Deletes
- ✅ Allgemeine Dokumente für E-Mail-Anhänge verfügbar
