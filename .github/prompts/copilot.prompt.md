# Laravel Filament Projekt Richtlinien

## Code-Konventionen
- Verwende Laravel 12 Standards
- Nutze Filament v4 Components
- Models in `app/Models/`
- Resources in `app/Filament/Resources/`

## Spezifische Anforderungen
- Alle Datumswerte als Carbon-Instanzen
- Status-Konstanten in Models definieren
- Property-Konstanten in Models definieren und diese auch an Stellen verwenden wie bspw. Migrationen oder Validierungen
- Deutsche Sprachausgabe für Benutzer
- Englische Code-Kommentare

## Naming Conventions
- Models: Singular (z.B. `Board`, nicht `Boards`)
- Migrations: Plural (z.B. `create_boards_table`)
- Resource-Klassen: `ModelResource`

## Filament Navigation
- Ressourcen in der Navigation alphabetisch sortieren
- Icons aus der Filament Icon Bibliothek verwenden
- Gruppiere verwandte Ressourcen in Menüs

## Auszuführende Commands (Docker)
- alle Commands im Docker-Container ausführen (`docker-compose exec laravel-filament-foerdertafel`)
- Migrationen: `docker-compose exec laravel-filament-foerdertafel php artisan migrate`
- Seeders: `docker-compose exec app laravel-filament-foerdertafel artisan db:seed`
- Tests: `docker-compose exec app laravel-filament-foerdertafel artisan test`
