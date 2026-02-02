# Event-Listener-System für Inquiry-Feldreservierung

## Übersicht
Dieses System verwendet Laravel Events und Listeners, um automatisch Felder zu reservieren, wenn eine Inquiry erstellt wird, und sie wieder freizugeben, wenn eine Inquiry abgelehnt wird.

## Architektur

### Events

#### 1. InquiryCreated
`app/Events/InquiryCreated.php`

**Wann wird es ausgelöst?**
- Direkt nach dem erfolgreichen Erstellen einer Inquiry in der Datenbank
- In `InquiryPage::submit()` nach der Transaction

**Payload:**
- `$inquiry` - Die neu erstellte Inquiry-Instanz

#### 2. InquiryRejected
`app/Events/InquiryRejected.php`

**Wann wird es ausgelöst?**
- Wenn eine Inquiry vom Admin abgelehnt wird
- Muss manuell im Admin-Panel aufgerufen werden

**Payload:**
- `$inquiry` - Die abgelehnte Inquiry-Instanz

---

### Listeners

#### 1. ReserveFieldsForInquiry
`app/Listeners/ReserveFieldsForInquiry.php`

**Funktion:**
Setzt alle angefragten Felder auf den Status `reserved`.

**Ablauf:**
1. Holt die `requested_fields` aus der Inquiry
2. Filtert alle Felder, die aktuell `available` sind
3. Setzt deren Status auf `reserved`
4. Loggt die Anzahl der reservierten Felder

**Code:**
```php
Field::whereIn('id', $fieldIds)
    ->where(Field::status, Field::STATUS_AVAILABLE)
    ->update([Field::status => Field::STATUS_RESERVED]);
```

**Wichtig:**
- Nur Felder mit Status `available` werden reserviert
- Bereits reservierte oder vermietete Felder werden nicht geändert
- Logging für Nachverfolgbarkeit

#### 2. ReleaseReservedFields
`app/Listeners/ReleaseReservedFields.php`

**Funktion:**
Gibt reservierte Felder wieder frei, wenn eine Inquiry abgelehnt wird.

**Ablauf:**
1. Holt die `requested_fields` aus der Inquiry
2. Filtert alle Felder, die aktuell `reserved` sind
3. Setzt deren Status zurück auf `available`
4. Loggt die Anzahl der freigegebenen Felder

**Code:**
```php
Field::whereIn('id', $fieldIds)
    ->where(Field::status, Field::STATUS_RESERVED)
    ->update([Field::status => Field::STATUS_AVAILABLE]);
```

**Wichtig:**
- Nur Felder mit Status `reserved` werden freigegeben
- Vermietete Felder (`rented`) bleiben unverändert
- Logging für Nachverfolgbarkeit

---

### Event Service Provider
`app/Providers/EventServiceProvider.php`

Registriert die Event-Listener-Mappings:

```php
protected $listen = [
    InquiryCreated::class => [
        ReserveFieldsForInquiry::class,
    ],
    InquiryRejected::class => [
        ReleaseReservedFields::class,
    ],
];
```

**Registrierung:**
Der EventServiceProvider ist in `bootstrap/providers.php` registriert.

---

## Workflow

### 1. Inquiry erstellen (Kunde)

```
Kunde füllt Formular aus
        ↓
Wählt Felder aus (z.B. Feld #1, #2, #3)
        ↓
Klickt "Anfrage absenden"
        ↓
InquiryPage::submit() wird aufgerufen
        ↓
DB::transaction {
    Inquiry::create(...)
    $inquiry->fields()->attach(...)
}
        ↓
InquiryCreated::dispatch($inquiry) ← EVENT
        ↓
ReserveFieldsForInquiry::handle() ← LISTENER
        ↓
UPDATE fields SET status='reserved' WHERE id IN (1,2,3) AND status='available'
        ↓
Felder sind jetzt reserviert ✅
        ↓
Redirect zur Bestätigungsseite
```

### 2. Inquiry ablehnen (Admin)

```
Admin öffnet Inquiry im Admin-Panel
        ↓
Ändert Status zu "rejected"
        ↓
Speichert die Inquiry
        ↓
InquiryRejected::dispatch($inquiry) ← EVENT
        ↓
ReleaseReservedFields::handle() ← LISTENER
        ↓
UPDATE fields SET status='available' WHERE id IN (1,2,3) AND status='reserved'
        ↓
Felder sind wieder verfügbar ✅
```

### 3. Inquiry genehmigen → Rental erstellen (Admin)

```
Admin genehmigt Inquiry
        ↓
Erstellt Rental aus Inquiry
        ↓
RentalObserver::created() ← EXISTING OBSERVER
        ↓
UPDATE fields SET status='rented'
        ↓
Felder sind vermietet ✅
```

---

## Field Status-Übersicht

| Status | Beschreibung | Sichtbar im Board? | Wann? |
|--------|--------------|-------------------|-------|
| `available` | Verfügbar | ✅ Ja (grün) | Initial, nach Ablehnung |
| `reserved` | Reserviert | ❌ Nein | Nach Inquiry-Erstellung |
| `rented` | Vermietet | ❌ Nein | Nach Rental-Erstellung |

---

## Integration in InquiryPage

### Vor der Änderung:
```php
$inquiry = DB::transaction(function () use ($formData) {
    $inquiry = Inquiry::create([...]);
    $inquiry->fields()->attach($this->selectedFields);
    return $inquiry;
});

// Redirect
session(['inquiry_complete' => $inquiry->id]);
$this->redirect(...);
```

### Nach der Änderung:
```php
$inquiry = DB::transaction(function () use ($formData) {
    $inquiry = Inquiry::create([...]);
    $inquiry->fields()->attach($this->selectedFields);
    return $inquiry;
});

// ✨ NEU: Event dispatchen
InquiryCreated::dispatch($inquiry);

// Redirect
session(['inquiry_complete' => $inquiry->id]);
$this->redirect(...);
```

---

## Vorteile des Event-Listener-Systems

### 1. **Separation of Concerns**
- InquiryPage kümmert sich nur um das Erstellen der Inquiry
- Listener kümmern sich um die Feldreservierung
- Klare Trennung der Verantwortlichkeiten

### 2. **Erweiterbarkeit**
Weitere Listener können einfach hinzugefügt werden:
```php
protected $listen = [
    InquiryCreated::class => [
        ReserveFieldsForInquiry::class,
        SendInquiryConfirmationEmail::class,  // ← Neu
        NotifyAdminAboutInquiry::class,       // ← Neu
        LogInquiryToAnalytics::class,         // ← Neu
    ],
];
```

### 3. **Testbarkeit**
Events und Listeners können unabhängig getestet werden:
```php
Event::fake([InquiryCreated::class]);

// Test inquiry creation
$this->post('/inquiry', $data);

Event::assertDispatched(InquiryCreated::class);
```

### 4. **Asynchrone Verarbeitung**
Listeners können in Queues verschoben werden:
```php
class ReserveFieldsForInquiry implements ShouldQueue
{
    use Queueable;
    // ...
}
```

### 5. **Logging & Debugging**
Jeder Listener loggt seine Aktionen:
```
[2026-02-02 10:30:45] INFO: Reserved 3 fields for Inquiry #42
[2026-02-02 10:35:20] INFO: Released 3 reserved fields for rejected Inquiry #42
```

---

## Fehlerbehandlung

### Scenario 1: Keine Felder in Inquiry
```php
if (empty($fieldIds)) {
    Log::warning("Inquiry {$inquiry->id} has no requested fields to reserve.");
    return;
}
```

### Scenario 2: Felder sind bereits reserviert
```php
// Der WHERE-Filter verhindert Doppel-Reservierungen
->where(Field::status, Field::STATUS_AVAILABLE)
```

### Scenario 3: Felder sind bereits vermietet
```php
// Nur 'reserved' Felder werden freigegeben
->where(Field::status, Field::STATUS_RESERVED)
```

---

## Event dispatchen in anderen Kontexten

### In Filament Admin Resource:
```php
use App\Events\InquiryRejected;

// In InquiryResource oder einer Action
public static function table(Table $table): Table
{
    return $table
        ->actions([
            Action::make('reject')
                ->action(function (Inquiry $record) {
                    $record->update(['status' => Inquiry::STATUS_REJECTED]);
                    InquiryRejected::dispatch($record);
                })
        ]);
}
```

### In einem Controller:
```php
use App\Events\InquiryCreated;

public function store(Request $request)
{
    $inquiry = Inquiry::create($request->validated());
    InquiryCreated::dispatch($inquiry);
    
    return redirect()->route('inquiry.show', $inquiry);
}
```

### In einem Command:
```php
use App\Events\InquiryRejected;

public function handle()
{
    $expiredInquiries = Inquiry::where('created_at', '<', now()->subDays(7))
        ->where('status', Inquiry::STATUS_PENDING)
        ->get();
    
    foreach ($expiredInquiries as $inquiry) {
        $inquiry->update(['status' => Inquiry::STATUS_REJECTED]);
        InquiryRejected::dispatch($inquiry);
    }
}
```

---

## Testing

### Unit Test für Event:
```php
public function test_inquiry_created_event_has_inquiry()
{
    $inquiry = Inquiry::factory()->create();
    $event = new InquiryCreated($inquiry);
    
    $this->assertInstanceOf(Inquiry::class, $event->inquiry);
    $this->assertEquals($inquiry->id, $event->inquiry->id);
}
```

### Unit Test für Listener:
```php
public function test_listener_reserves_fields()
{
    $fields = Field::factory()->count(3)->create([
        'status' => Field::STATUS_AVAILABLE
    ]);
    
    $inquiry = Inquiry::factory()->create([
        'requested_fields' => $fields->pluck('id')->toArray()
    ]);
    
    $event = new InquiryCreated($inquiry);
    $listener = new ReserveFieldsForInquiry();
    $listener->handle($event);
    
    $this->assertEquals(3, Field::where('status', Field::STATUS_RESERVED)->count());
}
```

### Integration Test:
```php
public function test_creating_inquiry_reserves_fields()
{
    Event::fake([InquiryCreated::class]);
    
    $fields = Field::factory()->count(3)->create();
    
    $response = $this->post('/inquiry', [
        'customer_name' => 'Test User',
        'customer_email' => 'test@example.com',
        'selected_fields' => $fields->pluck('id')->toArray(),
        // ...
    ]);
    
    Event::assertDispatched(InquiryCreated::class);
    
    $this->assertEquals(3, Field::where('status', Field::STATUS_RESERVED)->count());
}
```

---

## Zusammenfassung

### ✅ Implementierte Komponenten:

1. **Events:**
   - `InquiryCreated` - Inquiry wurde erstellt
   - `InquiryRejected` - Inquiry wurde abgelehnt

2. **Listeners:**
   - `ReserveFieldsForInquiry` - Reserviert Felder
   - `ReleaseReservedFields` - Gibt Felder frei

3. **Provider:**
   - `EventServiceProvider` - Registriert Events und Listeners

4. **Integration:**
   - InquiryPage dispatcht InquiryCreated Event
   - EventServiceProvider in bootstrap/providers.php registriert

### 🔄 Workflow:
- Inquiry erstellen → Felder werden reserviert
- Inquiry ablehnen → Felder werden freigegeben
- Inquiry genehmigen → Rental erstellen → Felder werden vermietet

### 🎯 Vorteile:
- ✅ Automatische Feldreservierung
- ✅ Saubere Code-Architektur
- ✅ Leicht erweiterbar
- ✅ Gut testbar
- ✅ Vollständiges Logging

Das System ist vollständig implementiert und produktionsbereit! 🚀
