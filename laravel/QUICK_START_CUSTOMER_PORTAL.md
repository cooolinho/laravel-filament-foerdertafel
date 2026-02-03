# 🎯 Quick Start Guide - Kundenportal

## Sofort loslegen

### 1️⃣ Setup (einmalig)

```bash
# Im Laravel-Verzeichnis
cd D:\Projekte\laravel-filament-foerdertafel\laravel

# Migrationen ausführen
php artisan migrate

# Email-Template erstellen
php artisan db:seed --class=RentalAccessCodeEmailTemplateSeeder

# Storage-Link erstellen (für Logo-Uploads)
php artisan storage:link
```

### 2️⃣ Test-Zugang erstellen

```php
// In tinker oder einem Seeder
use App\Models\Rental;
use App\Models\RentalContent;
use App\Models\Customer;

// Erstelle Test-Customer
$customer = Customer::first(); // oder create()

// Erstelle Test-Rental
$rental = Rental::create([
    'customer_id' => $customer->id,
    'start_date' => now(),
    'end_date' => now()->addMonths(6),
    'total_price' => 1000.00,
    'status' => Rental::STATUS_PAID,
    'paid_at' => now(),
]);

// Rental mit Feldern verknüpfen
$rental->fields()->attach([1, 2, 3]); // Field IDs

// RentalContent erstellen (Zugangscode wird automatisch generiert)
$content = RentalContent::create([
    'rental_id' => $rental->id,
    'is_private_person' => false,
]);

// Zugangscode anzeigen
echo "Zugangscode: " . $content->access_code;
// Output: ABCD-1234-EFGH (Beispiel)
```

### 3️⃣ Portal aufrufen

**Zugangscode-Seite:**
```
http://localhost/rental-content/access
```

**Oder direkt mit Code:**
```
http://localhost/rental-content/access/ABCD-1234-EFGH
```

### 4️⃣ Inhalte verwalten

Nach erfolgreicher Code-Eingabe:
1. Titel eingeben
2. Beschreibung verfassen
3. Kontaktdaten eintragen
4. Logo hochladen (wenn Firma)
5. "Veröffentlichen" aktivieren
6. Speichern!

---

## 🔧 Email-Versand aktivieren

### Manuell Email-Event auslösen

```php
use App\Events\RentalPaid;
use App\Models\Rental;

$rental = Rental::find(1); // Ihre Rental-ID
event(new RentalPaid($rental));
```

### Queue-Worker starten

```bash
# Für Email-Versand
php artisan queue:work
```

### Email-Konfiguration prüfen

```php
// .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 📋 Checkliste

### Backend ✅
- [x] RentalContent Model
- [x] Migrationen erstellt
- [x] RentalPaid Event
- [x] SendAccessCodeEmail Listener
- [x] RentalContentController
- [x] Routen registriert
- [x] Email-Template Seeder

### Frontend ✅
- [x] Access-Form View
- [x] Manage View
- [x] Responsive Design
- [x] JavaScript-Funktionen
- [x] Error-Handling
- [x] Success-Messages

### Testing ⚠️
- [ ] Zugangscode eingeben
- [ ] Content speichern
- [ ] Logo hochladen
- [ ] Email-Versand
- [ ] Responsive testen

---

## 🐛 Troubleshooting

### View nicht gefunden
```bash
# Cache leeren
php artisan view:clear
php artisan cache:clear
```

### Storage-Link fehlt
```bash
php artisan storage:link
```

### Zugangscode funktioniert nicht
```php
// In tinker prüfen
use App\Models\RentalContent;
RentalContent::all(); // Alle Codes anzeigen
```

### Email wird nicht versendet
```bash
# Prüfe Queue
php artisan queue:failed

# Prüfe Logs
tail -f storage/logs/laravel.log
```

---

## 🎨 Customization

### Farben ändern

**Primärfarbe ändern (von Grün zu Blau):**

In beiden Views suchen und ersetzen:
```
green-500 → blue-500
green-600 → blue-600
green-700 → blue-700
```

### Logo/Icon ändern

**Access-Form (Zeile ~14):**
```html
<i class="fas fa-key"></i>
<!-- Ändern zu: -->
<i class="fas fa-lock"></i>
```

**Manage-Page Header (Zeile ~22):**
```html
<i class="fas fa-th"></i>
<!-- Ändern zu: -->
<i class="fas fa-tachometer-alt"></i>
```

### Support-Email anpassen

**Manage-Page (Zeile ~398):**
```html
<a href="mailto:support@example.com">
<!-- Ändern zu Ihrer Email -->
<a href="mailto:ihr-support@ihre-domain.de">
```

---

## 📸 Screenshots-Vorschau

### Access-Form
```
┌─────────────────────────────────────┐
│                                     │
│          🔑                         │
│    Willkommen zurück!               │
│                                     │
│  ┌───────────────────────────────┐ │
│  │  XXXX-XXXX-XXXX            🔒│ │
│  └───────────────────────────────┘ │
│                                     │
│  ┌───────────────────────────────┐ │
│  │  ➜ Zugriff erhalten          │ │
│  └───────────────────────────────┘ │
│                                     │
│  ℹ️ Wie funktioniert das?          │
│  • Code per Email erhalten          │
│  • Code eingeben                    │
│  • Felder verwalten                 │
│                                     │
└─────────────────────────────────────┘
```

### Manage-Page
```
┌──────────────────────────────────────────────┐
│ 🎯 Feldverwaltung    Willkommen, Max M.     │
├──────────────────────────────────────────────┤
│                                              │
│  ┌─────────────────┐  ┌──────────────────┐ │
│  │ 📊 Ihre Felder │  │ 👤 Information   │ │
│  │ • A1 • B2 • C3  │  │  Code: ABCD...   │ │
│  └─────────────────┘  └──────────────────┘ │
│                                              │
│  ┌─────────────────┐  ┌──────────────────┐ │
│  │ ✏️ Bearbeiten   │  │ 📊 Status        │ │
│  │ [Formular...]   │  │  ✓ Veröffentl.  │ │
│  │                 │  └──────────────────┘ │
│  │ [💾 Speichern] │                        │
│  └─────────────────┘  ┌──────────────────┐ │
│                        │ 🆘 Hilfe         │ │
│                        └──────────────────┘ │
└──────────────────────────────────────────────┘
```

---

## 🚀 Produktions-Deployment

### Optimierungen

```bash
# Views cachen
php artisan view:cache

# Config cachen
php artisan config:cache

# Routes cachen
php artisan route:cache

# Tailwind für Produktion bauen (wenn lokal installiert)
npm run build
```

### CDN zu lokal wechseln

```bash
# Tailwind installieren
npm install -D tailwindcss
npx tailwindcss init

# Font Awesome installieren
npm install @fortawesome/fontawesome-free
```

**In Views ersetzen:**
```html
<!-- Statt CDN: -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Verwende: -->
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
```

---

## 📞 Support & Hilfe

**Dokumentation:**
- 📄 `docs/RENTAL_CONTENT_MANAGEMENT.md` - System-Übersicht
- 📄 `docs/CUSTOMER_PORTAL_VIEWS.md` - View-Details
- 📄 `RENTAL_CONTENT_IMPLEMENTATION.md` - Implementierung

**Bei Problemen:**
1. Prüfe Laravel Logs: `storage/logs/laravel.log`
2. Prüfe Browser Console (F12)
3. Validiere Routes: `php artisan route:list`
4. Teste in tinker: `php artisan tinker`

---

**✨ Viel Erfolg mit Ihrem Kundenportal! ✨**
