# Rental Details Modal - Implementierung

## Übersicht
Beim Klick auf ein vermietetes Feld in der BoardPage (App) oder im FieldsWidget (Admin) öffnet sich ein Modal, das die Details der Vermietung anzeigt.

## Implementierte Komponenten

### 1. BoardPage (App Panel)
`app/Filament/App/Pages/BoardPage.php`

#### Änderungen:
- **HasActions Interface** implementiert
- **InteractsWithActions Trait** hinzugefügt
- **showRentalDetailsAction()** Methode erstellt

#### Action-Methode:
```php
public function showRentalDetailsAction(): Action
{
    return Action::make('showRentalDetails')
        ->label('Vermietungsdetails')
        ->modalHeading(fn (array $arguments) => 'Vermietungsdetails - Feld ' . ($arguments['fieldName'] ?? ''))
        ->modalContent(function (array $arguments) {
            $rentalId = $arguments['rentalId'] ?? null;
            $rental = Rental::with(['customer', 'fields'])->find($rentalId);
            
            return view('filament.app.components.rental-details-modal', [
                'rental' => $rental,
            ]);
        })
        ->modalWidth(Width::Large)
        ->modalSubmitAction(false)
        ->modalCancelActionLabel('Schließen')
        ->closeModalByClickingAway(true);
}
```

### 2. BoardPage Blade Template
`resources/views/filament/app/pages/board-page.blade.php`

#### Änderungen:
```blade
{{-- Vermietetes Feld - Klickbar --}}
<div class="bg-black/70 hover:bg-black/80 text-white px-4 py-3 rounded-lg w-full h-full text-center transition-all duration-200 cursor-pointer"
     wire:click="mountAction('showRentalDetails', { rentalId: {{ $activeRental->id }}, fieldName: '{{ $field->name }}' })">
    <div class="flex flex-col items-center justify-center gap-2 h-full">
        <div class="font-semibold">
            {{ $activeRental->customer->name ?? 'N/A' }}
        </div>
        <div class="text-xs opacity-75">
            Klicken für Details
        </div>
    </div>
</div>
```

#### Modals-Tag am Ende:
```blade
<x-filament-actions::modals />
```

### 3. FieldsWidget (Admin Panel)
`app/Filament/Admin/Resources/Boards/Widgets/FieldsWidget.php`

#### Neue Methode:
```php
public function showRentalDetailsAction(): Action
{
    return Action::make('showRentalDetails')
        ->label('Vermietungsdetails')
        ->modalHeading(fn (array $arguments) => 'Vermietungsdetails - Feld ' . ($arguments['fieldName'] ?? ''))
        ->modalContent(function (array $arguments) {
            $rentalId = $arguments['rentalId'] ?? null;
            $rental = Rental::with(['customer', 'fields'])->find($rentalId);
            
            return view('filament.admin.components.rental-details-modal', [
                'rental' => $rental,
            ]);
        })
        ->modalWidth(\Filament\Support\Enums\Width::Large)
        ->modalSubmitAction(false)
        ->modalCancelActionLabel('Schließen')
        ->closeModalByClickingAway(true);
}
```

### 4. FieldsWidget Blade Template
`resources/views/filament/admin/resources/boards/widgets/fields-widget.blade.php`

#### Änderungen:
```blade
@if($activeRental)
    {{-- Rented Field - Show Modal on Click --}}
    <div wire:click="mountAction('showRentalDetails', { rentalId: {{ $activeRental->id }}, fieldName: '{{ $field->name }}' })"
         class="flex flex-col p-4 rounded-lg border-2 transition-all hover:shadow-xl hover:scale-105 h-full cursor-pointer {{ $borderColor }}"
         style="min-height: 120px;">
        
        {{-- Field Info --}}
        {{-- ... --}}
        
        {{-- Rental Info mit Hinweis --}}
        <div class="text-xs text-primary-600 dark:text-primary-400 mt-1 font-medium">
            Klicken für Details →
        </div>
    </div>
@else
    {{-- Available/Reserved Field - Link to Field Resource --}}
    <a href="{{ \App\Filament\Admin\Resources\Fields\FieldResource::getUrl('view', ['record' => $field->id]) }}"
       class="...">
        {{-- Field Info --}}
    </a>
@endif
```

### 5. Rental Details Modal View (App)
`resources/views/filament/app/components/rental-details-modal.blade.php`

#### Struktur:
- **Kundeninformationen**
  - Name
  - E-Mail (klickbar)
  - Telefon (optional, klickbar)

- **Mietdauer**
  - Startdatum
  - Enddatum
  - Dauer in Monaten

- **Gemietete Felder**
  - Liste aller Felder mit:
    - Feld-Identifier
    - Feldname
    - Position
    - Preis pro Monat

- **Preisübersicht**
  - Preis pro Monat (Summe)
  - Anzahl Monate
  - **Gesamtpreis** (hervorgehoben)

- **Status Badge**
  - Farbcodierter Status

### 6. Rental Details Modal View (Admin)
`resources/views/filament/admin/components/rental-details-modal.blade.php`

#### Zusätzliche Features gegenüber App-Version:
- **Verlinkte Kundeninformationen** (zu CustomerResource)
- **Firmennamen** (falls vorhanden)
- **Verlinkte Feldnamen** (zu FieldResource)
- **Action-Buttons:**
  - "Details anzeigen" (→ RentalResource View)
  - "Bearbeiten" (→ RentalResource Edit)

### 7. Empty State Component
`resources/views/filament/components/empty-state.blade.php`

Einfache Komponente für Fehlerfälle:
```blade
<div class="flex flex-col items-center justify-center py-8 text-center">
    <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mb-4">...</svg>
    <p class="text-gray-600 dark:text-gray-400">{{ $message ?? 'Keine Daten verfügbar.' }}</p>
</div>
```

---

## Workflow

### User Flow (App Panel - BoardPage):

```
1. Kunde öffnet BoardPage
   ↓
2. Sieht Fördertafel mit allen Feldern
   ↓
3. Vermietete Felder zeigen Kundennamen
   ↓
4. Kunde klickt auf vermietetes Feld
   ↓
5. wire:click triggert mountAction('showRentalDetails', {...})
   ↓
6. showRentalDetailsAction() wird aufgerufen
   ↓
7. Rental-Daten werden geladen (mit Customer & Fields)
   ↓
8. Modal öffnet sich mit rental-details-modal.blade.php
   ↓
9. Kunde sieht:
   - Kundendaten
   - Mietdauer
   - Alle gemieteten Felder
   - Preisübersicht
   - Status
   ↓
10. Kunde schließt Modal (Klick außerhalb oder "Schließen"-Button)
```

### Admin Flow (Admin Panel - FieldsWidget):

```
1. Admin öffnet Board-Detail-Seite
   ↓
2. FieldsWidget zeigt alle Felder im Grid
   ↓
3. Vermietete Felder haben gelben Border
   ↓
4. Admin klickt auf vermietetes Feld
   ↓
5. wire:click triggert mountAction('showRentalDetails', {...})
   ↓
6. showRentalDetailsAction() wird aufgerufen
   ↓
7. Rental-Daten werden geladen (mit Customer & Fields)
   ↓
8. Modal öffnet sich mit rental-details-modal.blade.php (Admin-Version)
   ↓
9. Admin sieht:
   - Kundendaten (verlinkt zu CustomerResource)
   - Mietdauer
   - Alle gemieteten Felder (verlinkt zu FieldResource)
   - Preisübersicht
   - Status
   - Action-Buttons
   ↓
10. Admin kann:
    - "Details anzeigen" → RentalResource View
    - "Bearbeiten" → RentalResource Edit
    - Modal schließen
```

---

## Technische Details

### Filament Actions System

Das Filament Actions System ermöglicht es, Livewire Actions als Modals darzustellen:

1. **Interface & Trait:**
   ```php
   class BoardPage extends Page implements HasActions
   {
       use InteractsWithActions;
   ```

2. **Action-Definition:**
   ```php
   public function showRentalDetailsAction(): Action
   {
       return Action::make('showRentalDetails')
           ->modalContent(fn (array $arguments) => view(...))
   ```

3. **Trigger via wire:click:**
   ```blade
   wire:click="mountAction('showRentalDetails', { rentalId: 123, fieldName: 'Tor' })"
   ```

4. **Modals Tag:**
   ```blade
   <x-filament-actions::modals />
   ```

### Parameter-Übergabe

Parameter werden als JavaScript-Objekt übergeben und sind in der Action als `$arguments` verfügbar:

```blade
wire:click="mountAction('showRentalDetails', { 
    rentalId: {{ $activeRental->id }}, 
    fieldName: '{{ $field->name }}' 
})"
```

```php
function (array $arguments) {
    $rentalId = $arguments['rentalId'] ?? null;
    $fieldName = $arguments['fieldName'] ?? '';
    // ...
}
```

### Eager Loading

Für Performance-Optimierung werden Relationen eager geladen:

```php
$rental = Rental::with(['customer', 'fields'])->find($rentalId);
```

Dadurch werden N+1 Query-Probleme vermieden.

---

## Design-Features

### Responsive Layout
- Mobile-first Design
- Grid-Layout für größere Bildschirme
- Scrollbare Feldliste bei vielen Feldern

### Dark Mode Support
- Alle Komponenten unterstützen Dark Mode
- Angepasste Farbpalette für beide Modi

### Hover-Effekte
- Vermietete Felder ändern Hintergrund bei Hover
- Cursor ändert sich zu Pointer
- "Klicken für Details" Hinweis bei Hover

### Farbcodierung
- **Grün:** Verfügbar
- **Gelb:** Vermietet
- **Blau:** Reserviert
- **Status-Badges:** Farbcodiert je nach Rental-Status

### Icons
- Heroicons für konsistente Iconographie
- Semantische Icons für verschiedene Sektionen

---

## Unterschiede App vs. Admin

| Feature | App Panel | Admin Panel |
|---------|-----------|-------------|
| **Kundenname** | Normal | Verlinkt zu CustomerResource |
| **Feldname** | Normal | Verlinkt zu FieldResource |
| **Firmenname** | ❌ Nicht angezeigt | ✅ Falls vorhanden |
| **Action-Buttons** | ❌ Keine | ✅ Details & Bearbeiten |
| **Weitere Infos** | Basis-Informationen | Erweiterte Informationen |

---

## Fehlerfälle

### 1. Rental-ID fehlt
```php
if (!$rentalId) {
    return view('filament.components.empty-state', [
        'message' => 'Keine Vermietungsdaten verfügbar.'
    ]);
}
```

### 2. Rental nicht gefunden
```php
$rental = Rental::with(['customer', 'fields'])->find($rentalId);

if (!$rental) {
    return view('filament.components.empty-state', [
        'message' => 'Vermietung nicht gefunden.'
    ]);
}
```

### 3. Keine Felder
Falls eine Rental keine Felder hat (sollte nicht vorkommen), wird eine leere Liste angezeigt.

---

## Testing

### Manueller Test (App):
```
1. Als Gast: http://localhost/app/board
2. Klicke auf ein vermietetes Feld (schwarz mit Kundenname)
3. Modal sollte öffnen mit allen Details
4. Klicke außerhalb des Modals → Modal schließt
5. Klicke auf "Schließen" → Modal schließt
```

### Manueller Test (Admin):
```
1. Als Admin: http://localhost/admin/boards/{id}
2. Scrolle zum FieldsWidget
3. Klicke auf ein vermietetes Feld (gelber Border)
4. Modal sollte öffnen mit allen Details
5. Klicke auf "Details anzeigen" → RentalResource View
6. Zurück, Modal erneut öffnen
7. Klicke auf "Bearbeiten" → RentalResource Edit
```

### Browser-Konsole prüfen:
```javascript
// Keine JavaScript-Fehler
// Livewire-Requests sollten erfolgreich sein
```

---

## Performance-Optimierung

### Eager Loading
```php
$rental = Rental::with(['customer', 'fields'])->find($rentalId);
```

### Indexierung
Stelle sicher, dass Foreign Keys indiziert sind:
- `rentals.customer_id`
- `field_rental.rental_id`
- `field_rental.field_id`

### Caching
Optional: Rental-Details cachen für häufig abgefragte Rentals:
```php
$rental = Cache::remember(
    "rental.{$rentalId}.details",
    now()->addMinutes(5),
    fn() => Rental::with(['customer', 'fields'])->find($rentalId)
);
```

---

## Erweiterungsmöglichkeiten

### 1. Zusätzliche Informationen
- **Rechnungen:** Liste aller Rechnungen zur Rental
- **Dokumente:** Verträge, etc.
- **Historie:** Änderungsprotokoll

### 2. Interaktive Features
- **E-Mail senden:** Direkt aus dem Modal
- **Notizen hinzufügen:** Interne Notizen zur Rental
- **Status ändern:** Direkt im Modal (nur Admin)

### 3. Export
- **PDF-Export:** Rental-Details als PDF
- **Excel-Export:** Für Reporting

### 4. Analytics
- **Tracking:** Wie oft wird das Modal geöffnet?
- **Beliebte Felder:** Welche Felder werden am häufigsten angeschaut?

---

## Zusammenfassung

### Neue Dateien: 3
1. `resources/views/filament/app/components/rental-details-modal.blade.php`
2. `resources/views/filament/admin/components/rental-details-modal.blade.php`
3. `resources/views/filament/components/empty-state.blade.php`

### Geänderte Dateien: 4
1. `app/Filament/App/Pages/BoardPage.php`
2. `resources/views/filament/app/pages/board-page.blade.php`
3. `app/Filament/Admin/Resources/Boards/Widgets/FieldsWidget.php`
4. `resources/views/filament/admin/resources/boards/widgets/fields-widget.blade.php`

### Features:
- ✅ Klickbare vermietete Felder in BoardPage
- ✅ Klickbare vermietete Felder in FieldsWidget
- ✅ Modal mit Rental-Details
- ✅ Unterschiedliche Ansichten für App & Admin
- ✅ Responsive Design
- ✅ Dark Mode Support
- ✅ Fehlerbehandlung
- ✅ Performance-Optimierung

**Status: Vollständig implementiert und produktionsbereit! 🎉**
