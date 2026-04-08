# Laravel Filament Fördertafel

![Version](https://img.shields.io/badge/version-1.0.0-blue)

Digitales Board-Management-System für Fußballfeld-Werbeflächen mit Laravel 12 und Filament 4.

## 📋 Projektübersicht

Dieses Projekt ist ein Verwaltungssystem für digitale Werbetafeln auf Fußballfeldern. Stell dir ein Schachbrettmuster vor, bei dem jedes Feld von Personen oder Firmen gemietet werden kann. Premium-Positionen wie "Tor", "Strafraum" oder "Mittelkreis" haben höhere Preise als Standard-Felder.

### Hauptfunktionen

- 📍 **Location Management** - Verwaltung mehrerer Standorte
- 📊 **Board Management** - Konfigurierbare Spielfeld-Grids (Rows x Columns)
- 🎲 **Field Management** - Einzelne Felder mit Position, Größe und Preis
- 👥 **Customer Management** - Kunden- und Firmenverwaltung
- 📄 **Rental Management** - Vermietung von Feldern mit Zeiträumen

## 🚀 Installation

### 1. Repository klonen
```bash
git clone git@github.com:cooolinho/laravel-filament-foerdertafel.git
cd laravel-filament-foerdertafel
```

### Manual init steps (if needed)
```
docker-compose up -d

# oder die prod variante
docker compose -f docker-compose-prod.yml up -d
```

### 2. Umgebungsvariablen konfigurieren
```bash
cp .env.example .env
```

Passe folgende Werte in der `.env` Datei an:
```bash
LARAVEL_CONTAINER_NAME=laravel-filament-foerdertafel
```

### 3. Initialisierung ausführen
```bash
sh init.sh
```

Das Init-Script führt folgende Schritte aus:
- Docker Container starten
- Composer Dependencies installieren
- NPM Packages installieren
- Laravel Application Key generieren
- Datenbank-Migrationen ausführen
- Demo-Daten laden (optional)

### 4. Demo-Daten laden (optional)
```bash
docker exec -it laravel-filament-foerdertafel bash
cd laravel
php artisan migrate:fresh --seed
```

Dies erstellt:
- 5 Locations (Berlin, Hamburg, München, etc.)
- 6 Boards mit verschiedenen Größen
- ~180-300 Fields mit realistischen Preisen
- 12 Customers (Firmen und Privatkunden)
- 10 Rentals (verschiedene Status und Zeiträume)

## 🎯 Admin Dashboard öffnen

**URL:** http://localhost/admin/login

**Standard-Login:**
```
E-Mail:   admin@example.com
Password: secret
```

## 📁 Projektstruktur

```
laravel-filament-foerdertafel/
├── laravel/                    # Laravel Application
│   ├── app/
│   │   ├── Filament/
│   │   │   └── Resources/     # Filament Resources
│   │   └── Models/            # Eloquent Models
│   ├── database/
│   │   ├── migrations/        # Database Migrations
│   │   └── seeders/           # Database Seeders
│   ├── docs/                  # 📚 Projekt-Dokumentation
│   │   ├── DATABASE_SCHEMA.md
│   │   ├── MODEL_CONSTANTS.md
│   │   ├── FILAMENT_RESOURCES_OVERVIEW.md
│   │   ├── SEEDERS_DOCUMENTATION.md
│   │   └── NAVIGATION_DOCUMENTATION.md
│   └── README.md              # Laravel-spezifische Dokumentation
├── docker-compose.yml         # Docker Services
└── README.md                  # Diese Datei
```

## 📚 Dokumentation

Detaillierte Dokumentation findest du im `laravel/docs/` Verzeichnis:

- **[DATABASE_SCHEMA.md](laravel/docs/DATABASE_SCHEMA.md)** - Datenbank-Schema und Beziehungen
- **[MODEL_CONSTANTS.md](laravel/docs/MODEL_CONSTANTS.md)** - Model-Konstanten Pattern
- **[FILAMENT_RESOURCES_OVERVIEW.md](laravel/docs/FILAMENT_RESOURCES_OVERVIEW.md)** - Filament Resources Übersicht
- **[SEEDERS_DOCUMENTATION.md](laravel/docs/SEEDERS_DOCUMENTATION.md)** - Demo-Daten Dokumentation
- **[NAVIGATION_DOCUMENTATION.md](laravel/docs/NAVIGATION_DOCUMENTATION.md)** - Navigation & Icons

## 🛠️ Technologie-Stack

- **Backend:** Laravel 12
- **Admin Panel:** Filament 4
- **Database:** MySQL 8.0
- **Cache:** Redis 7
- **Search:** Meilisearch
- **Mail:** Mailpit (Development)
- **Testing:** Selenium (Browser Testing)

## 🐳 Docker Services

Das Projekt verwendet folgende Docker Container:

| Service | Port | Beschreibung |
|---------|------|--------------|
| **nginx** | 80 | Web Server |
| **php** | - | PHP 8.3 FPM |
| **mysql** | 3306 | MySQL Database |
| **redis** | 6379 | Cache & Sessions |
| **meilisearch** | 7700 | Search Engine |
| **mailpit** | 8025 | Mail Testing |
| **selenium** | 4444 | Browser Testing |

## 🔧 Nützliche Befehle

### Docker Container verwalten
```bash
# Container starten
docker-compose up -d

# Container stoppen
docker-compose down

# Logs anzeigen
docker-compose logs -f
```

### Laravel Befehle ausführen
```bash
# In Container einloggen
docker exec -it laravel-filament-foerdertafel bash
cd laravel

# Artisan Befehle
php artisan migrate
php artisan db:seed
php artisan tinker

# Tests ausführen
php artisan test
```

### Datenbank zurücksetzen
```bash
docker exec -it laravel-filament-foerdertafel bash
cd laravel
php artisan migrate:fresh --seed
```

## 🎨 Features

### Board Management
- ✅ Locations mit vollständiger Adressverwaltung
- ✅ Konfigurierbare Boards (Rows x Columns)
- ✅ Automatische Field-Generierung
- ✅ Intelligente Status-Verwaltung

### Field Management
- ✅ Positionsbasierte Felder (Row, Column)
- ✅ Flexible Größen (Width, Height)
- ✅ Preisbasierte Kategorien (100€ - 500€/Monat)
- ✅ Status: Available, Rented, Reserved
- ✅ Premium-Positionen (Tor, Strafraum, Mittelkreis)

### Customer & Rental Management
- ✅ Firmen- und Privatkunden
- ✅ Vollständige Kontaktverwaltung
- ✅ Multiple Fields pro Rental
- ✅ Zeitbasierte Vermietungen
- ✅ Status-Tracking (Active, Completed, Cancelled)

### Filament Admin Features
- ✅ Intuitive Navigation mit Icons
- ✅ Intelligente Badges (Verfügbarkeit, Status)
- ✅ Detaillierte Tables mit Filtern
- ✅ Umfangreiche Infolists
- ✅ Relationship Management

## 📊 Demo-Daten

Nach dem Seeding stehen folgende Demo-Daten zur Verfügung:

- **5 Locations** - Berlin, Hamburg, München, Düsseldorf, Dresden
- **6 Boards** - Verschiedene Größen (8x6 bis 15x10)
- **~200 Fields** - Premium bis Standard-Felder
- **12 Customers** - Mix aus Firmen und Privatkunden
- **10 Rentals** - Verschiedene Status und Laufzeiten

## 🔐 Sicherheit

- CSRF Protection aktiviert
- XSS Protection durch Laravel Blade
- SQL Injection Prevention durch Eloquent ORM
- Password Hashing mit bcrypt
- Rate Limiting für API Endpoints

## 📝 Lizenz

Dieses Projekt ist für interne Zwecke entwickelt.

## 🙏 Credits

Basierend auf:
- [Laravel Filament Template](https://github.com/cooolinho/laravel-filament-template)

## 📞 Support

Bei Fragen oder Problemen:
1. Prüfe die [Dokumentation](laravel/docs/)
2. Schaue in die [Filament Docs](https://filamentadmin.com/)
3. Kontaktiere das Entwicklerteam

## 🔗 Referenzen

- [Filament 4](https://filamentadmin.com/)
- [Laravel 12](https://laravel.com/)
- [Docker](https://www.docker.com/)
- [Docker-Compose](https://docs.docker.com/compose/)
- [MySQL](https://hub.docker.com/r/mysql/mysql-server)
- [Redis](https://hub.docker.com/_/redis)
- [Meilisearch](https://hub.docker.com/r/getmeili/meilisearch)
- [Mailpit](https://hub.docker.com/r/axllent/mailpit)
- [Selenium](https://hub.docker.com/r/selenium/standalone-chromium)
