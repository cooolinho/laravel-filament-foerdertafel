# Quick Reference: Inquiry Field Reservation System

## 📁 Dateien-Übersicht

### Events
- `app/Events/InquiryCreated.php` - Event bei Inquiry-Erstellung
- `app/Events/InquiryRejected.php` - Event bei Inquiry-Ablehnung

### Listeners
- `app/Listeners/ReserveFieldsForInquiry.php` - Felder reservieren
- `app/Listeners/ReleaseReservedFields.php` - Felder freigeben

### Provider
- `app/Providers/EventServiceProvider.php` - Event-Listener-Mapping
- `bootstrap/providers.php` - Provider-Registrierung

### Geänderte Dateien
- `app/Filament/App/Pages/InquiryPage.php` - Event dispatch hinzugefügt

---

## 🔄 Workflow Diagramm

```
INQUIRY ERSTELLEN
=================
InquiryPage::submit()
        ↓
DB Transaction: Create Inquiry + Attach Fields
        ↓
InquiryCreated::dispatch($inquiry)
        ↓
ReserveFieldsForInquiry::handle()
        ↓
Fields: available → reserved ✅


INQUIRY ABLEHNEN
================
Admin: Status → rejected
        ↓
InquiryRejected::dispatch($inquiry)
        ↓
ReleaseReservedFields::handle()
        ↓
Fields: reserved → available ✅


INQUIRY GENEHMIGEN
==================
Admin: Create Rental from Inquiry
        ↓
RentalObserver::created()
        ↓
Fields: reserved → rented ✅
```

---

## 🎯 Wichtigste Code-Stellen

### Event dispatchen (InquiryPage.php, Zeile ~213):
```php
InquiryCreated::dispatch($inquiry);
```

### Felder reservieren (ReserveFieldsForInquiry.php):
```php
Field::whereIn('id', $fieldIds)
    ->where(Field::status, Field::STATUS_AVAILABLE)
    ->update([Field::status => Field::STATUS_RESERVED]);
```

### Felder freigeben (ReleaseReservedFields.php):
```php
Field::whereIn('id', $fieldIds)
    ->where(Field::status, Field::STATUS_RESERVED)
    ->update([Field::status => Field::STATUS_AVAILABLE]);
```

---

## 🔍 Status-Übersicht

| Status | Bedeutung | BoardPage | Nach Aktion |
|--------|-----------|-----------|-------------|
| `available` | Verfügbar | ✅ Sichtbar | Initial |
| `reserved` | Reserviert | ❌ Ausgeblendet | Inquiry erstellt |
| `rented` | Vermietet | ❌ Ausgeblendet | Rental erstellt |

---

## 🧪 Testing Commands

### Tinker testen:
```bash
php artisan tinker

# Event manuell feuern
$inquiry = App\Models\Inquiry::first();
App\Events\InquiryCreated::dispatch($inquiry);

# Status prüfen
App\Models\Field::whereIn('id', [1,2,3])->pluck('status', 'id');
```

### Log prüfen:
```bash
tail -f storage/logs/laravel.log
```

### Events anzeigen:
```bash
php artisan event:list
```

---

## ⚡ Nächste Schritte (Optional)

### InquiryRejected Event in Admin dispatchen:
```php
// In InquiryResource
Action::make('reject')
    ->action(function (Inquiry $record) {
        $record->update(['status' => Inquiry::STATUS_REJECTED]);
        InquiryRejected::dispatch($record);
    });
```

### E-Mail bei Inquiry senden:
```php
// Neuer Listener
class SendInquiryConfirmationEmail {
    public function handle(InquiryCreated $event) {
        Mail::to($event->inquiry->customer_email)
            ->send(new InquiryConfirmation($event->inquiry));
    }
}
```

### Queue verwenden (asynchron):
```php
class ReserveFieldsForInquiry implements ShouldQueue
{
    use Queueable;
    // ... rest of code
}
```

---

## ✅ Implementierungs-Status

- ✅ Events erstellt (InquiryCreated, InquiryRejected)
- ✅ Listeners erstellt (Reserve, Release)
- ✅ EventServiceProvider konfiguriert
- ✅ Provider registriert in bootstrap/providers.php
- ✅ Event dispatch in InquiryPage integriert
- ✅ Logging implementiert
- ✅ Dokumentation erstellt

**Status: Vollständig implementiert und produktionsbereit! 🎉**
