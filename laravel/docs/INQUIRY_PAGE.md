# InquiryPage - Frontend Implementation

## Überblick

Die InquiryPage ermöglicht es Kunden, Anfragen für Feldvermietungen zu stellen. Sie bietet eine interaktive Board-Ansicht mit Feld-Auswahl, ein Kontaktformular und eine Live-Preis-Berechnung.

## Features

### 1. Board-Darstellung
- **Grid-Layout**: Zeigt das Board-Raster mit allen verfügbaren Feldern
- **Hintergrund-Image**: Verwendet das konfigurierte Board-Hintergrund-Bild
- **Responsive Grid**: Dynamisch basierend auf Board-Konfiguration (Rows, Columns, Offsets, Gap)

### 2. Feld-Auswahl
- **Klickbar**: Felder können per Klick ausgewählt/abgewählt werden
- **Mehrfachauswahl**: Multiple Felder können gleichzeitig ausgewählt werden
- **Visuelle Hervorhebung**:
  - Verfügbare Felder: Grüner Border + transparenter Hintergrund
  - Ausgewählte Felder: Blauer Border + blauer Hintergrund + Checkmark
  - Hover-Effekt: Hellerer Hintergrund bei Hover
- **Preis-Anzeige**: Jedes Feld zeigt seinen Preis pro Monat

### 3. Kontaktformular
- **Kundenname**: Pflichtfeld, max 255 Zeichen
- **E-Mail**: Pflichtfeld, validiert als E-Mail
- **Telefon**: Optional, Tel-Input
- **Startdatum**: Pflichtfeld, Date-Picker, nicht vor heute
- **Enddatum**: Pflichtfeld, Date-Picker, muss nach/gleich Startdatum sein
- **Nachricht**: Optional, Textarea für zusätzliche Informationen

### 4. Preis-Berechnung
- **Live-Update**: Preise werden in Echtzeit berechnet
- **Preis pro Monat**: Summe aller ausgewählten Felder
- **Zeitraum-Berechnung**: Anzahl Monate zwischen Start- und Enddatum
- **Gesamtpreis**: Preis pro Monat × Anzahl Monate
- **Anzeige**: 
  - Anzahl ausgewählter Felder
  - Preis pro Monat
  - Zeitraum in Monaten
  - Gesamtpreis (hervorgehoben)

### 5. Validierung
- **Frontend-Validierung**: 
  - Mindestens ein Feld muss ausgewählt sein
  - Alle Pflichtfelder müssen ausgefüllt sein
  - Enddatum muss nach Startdatum liegen
- **Submit-Button**: 
  - Disabled wenn keine Felder ausgewählt
  - Zeigt Success/Error-Notification
- **Warnhinweis**: Zeigt Meldung wenn keine Felder ausgewählt

## Technische Details

### Page-Klasse (`InquiryPage.php`)

#### Properties
```php
public ?Board $board = null;           // Das aktuelle Board
public ?array $data = [];             // Form-Daten
public array $selectedFields = [];    // Array mit IDs der ausgewählten Felder
```

#### Methoden

**mount()**
- Lädt das Board mit verfügbaren Feldern
- Akzeptiert Board-ID als Query-Parameter
- Filtert nur verfügbare Felder (STATUS_AVAILABLE)

**form(Form $form)**
- Definiert das Kontaktformular
- Verwendet Filament Form-Builder
- Reactive Date-Pickers für Live-Updates

**toggleField(int $fieldId)**
- Fügt Feld zu Auswahl hinzu oder entfernt es
- Wire-Action von Blade-Template aufgerufen
- Dispatched 'fields-updated' Event

**submit()**
- Validiert Form-Daten
- Prüft ob mindestens ein Feld ausgewählt
- Erstellt Inquiry in Datenbank
- Attached Fields via Pivot-Table
- Zeigt Success/Error-Notification
- Resettet Form nach erfolgreichem Submit

**getTotalPricePerMonth()**
- Berechnet Summe der Preise aller ausgewählten Felder
- Query zu Field-Model mit whereIn

**getTotalPrice()**
- Berechnet Gesamtpreis für gewählten Zeitraum
- Nutzt Carbon für Datums-Berechnung
- Rundet Monate auf (mindestens 1 Monat)

**getFieldsGrid()**
- Erstellt Grid-Struktur aus Board-Feldern
- Markiert occupied Positionen
- Wird im Blade-Template verwendet

### Blade-Template (`inquiry-page.blade.php`)

#### Struktur
1. **Header**: Board-Name, Beschreibung
2. **Instructions**: Anleitung für Nutzer
3. **Grid**: 2-Spalten Layout (Board + Form)
   - **Linke Spalte**: Legend + Board-Grid
   - **Rechte Spalte**: Preis-Summary + Form

#### Board-Grid
- CSS Grid Layout
- Dynamic Columns/Rows basierend auf Board
- Background-Image Support
- Offset und Gap Support
- Wire-Click auf Felder für Auswahl
- Conditional Styling (selected/available)

#### Preis-Summary (Sticky)
- Bleibt beim Scrollen sichtbar
- Live-Updates via Livewire
- Zeigt alle relevanten Preis-Informationen
- Warnhinweis wenn keine Felder ausgewählt

#### Formular
- Filament Form-Components
- Wire-Submit für Anfrage
- Disabled Submit-Button wenn keine Auswahl

## Navigation

### Von BoardPage zur InquiryPage
In der BoardPage wurde ein Button hinzugefügt:
```blade
<a href="{{ route('filament.app.pages.inquiry-page', ['board' => $this->board->id]) }}" 
   class="...">
    Anfrage stellen
</a>
```

### Route
Die Route wird automatisch von Filament registriert:
```
filament.app.pages.inquiry-page
```

Query-Parameter `board` wird optional akzeptiert.

## User Flow

1. **Startpunkt**: Kunde ist auf BoardPage
2. **Navigation**: Klick auf "Anfrage stellen" Button
3. **Feld-Auswahl**: Kunde klickt auf verfügbare Felder
   - Felder werden blau markiert
   - Preis-Summary wird aktualisiert
4. **Formular ausfüllen**:
   - Kontaktdaten eingeben
   - Zeitraum wählen
   - Optional: Nachricht schreiben
5. **Preis prüfen**: Live-Berechnung in Sidebar
6. **Absenden**: Klick auf "Anfrage absenden"
7. **Bestätigung**: Success-Notification
8. **Reset**: Form und Auswahl werden zurückgesetzt

## Validierung & Fehlerbehandlung

### Frontend-Validierung
- Filament Form-Validierung für alle Felder
- Mindestens ein Feld muss ausgewählt sein
- Submit-Button disabled wenn keine Auswahl

### Backend-Validierung
- Form-State wird validiert
- Database-Transaction für atomare Operation
- Exception-Handling mit Error-Notification

### Notifications
- **Success**: "Anfrage erfolgreich gesendet!"
- **Error (keine Felder)**: "Bitte wählen Sie mindestens ein Feld aus."
- **Error (Exception)**: "Beim Senden der Anfrage ist ein Fehler aufgetreten."

## Styling

### Farben
- **Verfügbar**: Grün (`border-green-500`, `bg-green-500/20`)
- **Ausgewählt**: Blau (`border-blue-600`, `bg-blue-600/80`)
- **Preis**: Blau (`text-blue-600`)
- **Warnungen**: Amber (`bg-amber-50`, `text-amber-600`)

### Responsive
- Grid-Layout passt sich an
- Mobile: Stackt zu 1 Spalte
- Desktop: 2 Spalten (Board + Form)
- Large Desktop: 3 Spalten mit sticky Sidebar

### Dark Mode
- Vollständige Dark-Mode-Unterstützung
- Alle Komponenten haben dark: Varianten

## Datenfluss

### Inquiry-Erstellung
```php
Inquiry::create([
    'board_id' => $this->board->id,
    'customer_name' => $formData['customer_name'],
    'customer_email' => $formData['customer_email'],
    'customer_phone' => $formData['customer_phone'] ?? null,
    'start_date' => $formData['start_date'],
    'end_date' => $formData['end_date'],
    'requested_fields' => $this->selectedFields, // JSON Array
    'status' => Inquiry::STATUS_PENDING,
    'message' => $formData['message'] ?? null,
]);

// Attach fields via pivot
$inquiry->fields()->attach($this->selectedFields);
```

### Livewire-Updates
- `wire:click="toggleField({{ $fieldId }})"` - Feld-Auswahl
- `wire:submit="submit"` - Form-Submit
- Auto-Update bei Änderung von `$selectedFields` oder `$data`

## Performance-Optimierungen

### Eager Loading
```php
Board::with(['fields' => function ($query) {
    $query->where(Field::status, Field::STATUS_AVAILABLE);
}])
```
Lädt nur verfügbare Felder in einem Query.

### Sticky Sidebar
```css
position: sticky;
top: 1.5rem;
```
Preis-Summary bleibt sichtbar beim Scrollen.

### Conditional Rendering
- Nur verfügbare Felder werden geladen
- Occupied Positionen sind einfache Platzhalter

## Erweiterungsmöglichkeiten

1. **Availability-Check**: Prüfung ob Felder im gewählten Zeitraum verfügbar
2. **Field-Info-Modal**: Details zu Feld bei Hover/Klick
3. **Multi-Board**: Auswahl aus mehreren Boards
4. **Save Draft**: Anfrage zwischenspeichern
5. **Email-Confirmation**: Automatische E-Mail nach Submit
6. **Price-Discounts**: Rabatte bei längerer Mietdauer
7. **Field-Recommendations**: KI-basierte Empfehlungen

## Testing

### Manual Testing
1. Öffne BoardPage
2. Klick auf "Anfrage stellen"
3. Wähle verschiedene Felder aus
4. Prüfe Preis-Berechnung
5. Fülle Formular aus
6. Submit und prüfe Notification
7. Prüfe Datenbank auf neue Inquiry

### Edge Cases
- Keine Felder ausgewählt → Error
- Ungültige Daten → Form-Validierung
- Enddatum vor Startdatum → Validierung
- Exception während Submit → Error-Handling

## Zusammenfassung

Die InquiryPage bietet eine vollständige, benutzerfreundliche Lösung für Kunden-Anfragen:

✅ **Interaktive Board-Darstellung** mit klickbaren Feldern
✅ **Live-Preis-Berechnung** mit Zeitraum-Support
✅ **Vollständiges Kontaktformular** mit Validierung
✅ **Responsive Design** mit Dark-Mode
✅ **Database-Integration** mit Pivot-Tables
✅ **User-Feedback** via Notifications
✅ **Error-Handling** und Validierung

Die Implementierung ist produktionsreif und kann sofort verwendet werden!
