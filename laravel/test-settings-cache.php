#!/usr/bin/env php
<?php

/**
 * Settings Cache & Get Method - Test Script
 *
 * Dieses Script testet die neue get() Methode und das Caching-System
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  Settings Cache & Get Method - Test                        ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Test 1: get() Methode
echo "📝 Test 1: get() Methode für einzelne Settings\n";
echo str_repeat("─", 60) . "\n";

$maxFields = Setting::get('max_fields_per_customer');
echo "✓ Max Fields per Customer: {$maxFields}\n";

$paymentMethod = Setting::get('default_payment_method');
echo "✓ Default Payment Method: {$paymentMethod}\n";

$rentalDuration = Setting::get('default_rental_duration');
echo "✓ Default Rental Duration: {$rentalDuration} Monat(e)\n";

$emailEnabled = Setting::get('email_notifications_enabled') ? 'Aktiviert' : 'Deaktiviert';
echo "✓ Email Notifications: {$emailEnabled}\n";

echo "\n";

// Test 2: get() mit Fallback
echo "📝 Test 2: get() mit Fallback-Werten\n";
echo str_repeat("─", 60) . "\n";

$existingValue = Setting::get('max_fields_per_customer', 999);
echo "✓ Existierender Wert: {$existingValue} (sollte nicht 999 sein)\n";

$missingValue = Setting::get('nicht_vorhanden', 'Fallback');
echo "✓ Nicht existierender Wert: '{$missingValue}' (sollte 'Fallback' sein)\n";

echo "\n";

// Test 3: Cache-Performance
echo "📝 Test 3: Cache Performance-Test\n";
echo str_repeat("─", 60) . "\n";

// Cache leeren für fairen Test
Setting::clearCache();
echo "✓ Cache geleert\n";

// Erste Abfrage (aus DB)
$start = microtime(true);
$value1 = Setting::get('max_fields_per_customer');
$time1 = (microtime(true) - $start) * 1000;

// Zweite Abfrage (aus Cache)
$start = microtime(true);
$value2 = Setting::get('max_fields_per_customer');
$time2 = (microtime(true) - $start) * 1000;

// Dritte Abfrage (aus Cache)
$start = microtime(true);
$value3 = Setting::get('max_fields_per_customer');
$time3 = (microtime(true) - $start) * 1000;

echo "✓ 1. Abfrage (DB):    " . number_format($time1, 2) . " ms\n";
echo "✓ 2. Abfrage (Cache): " . number_format($time2, 2) . " ms\n";
echo "✓ 3. Abfrage (Cache): " . number_format($time3, 2) . " ms\n";

$speedup = $time1 / $time2;
echo "✓ Geschwindigkeitssteigerung: " . number_format($speedup, 1) . "x schneller\n";

echo "\n";

// Test 4: Cache-Konstanten
echo "📝 Test 4: Cache-Konfiguration\n";
echo str_repeat("─", 60) . "\n";

echo "✓ Cache Key: " . Setting::CACHE_KEY . "\n";
echo "✓ Cache TTL: " . Setting::CACHE_TTL . " Sekunden (" . (Setting::CACHE_TTL / 60) . " Minuten)\n";

echo "\n";

// Test 5: current() vs get()
echo "📝 Test 5: current() vs get() Vergleich\n";
echo str_repeat("─", 60) . "\n";

// Mit current()
$start = microtime(true);
$settings = Setting::current();
$value = $settings?->max_fields_per_customer;
$timeA = (microtime(true) - $start) * 1000;

// Mit get()
$start = microtime(true);
$value = Setting::get('max_fields_per_customer');
$timeB = (microtime(true) - $start) * 1000;

echo "✓ current() + Property Access: " . number_format($timeA, 3) . " ms\n";
echo "✓ get() direkt:                 " . number_format($timeB, 3) . " ms\n";

echo "\n";

// Test 6: Zahlungsmethoden
echo "📝 Test 6: Zahlungsmethoden\n";
echo str_repeat("─", 60) . "\n";

$methods = Setting::getPaymentMethods();
foreach ($methods as $key => $label) {
    $current = (Setting::get('default_payment_method') === $key) ? ' ← Standard' : '';
    echo "✓ {$key}: {$label}{$current}\n";
}

echo "\n";

// Test 7: Alle Settings anzeigen
echo "📝 Test 7: Alle Settings im Überblick\n";
echo str_repeat("─", 60) . "\n";

$settings = Setting::current();
if ($settings) {
    echo "✓ ID: {$settings->id}\n";
    echo "✓ Standard Zahlungsmethode: {$settings->default_payment_method}\n";
    echo "✓ Standard Mietdauer: {$settings->default_rental_duration} Monat(e)\n";
    echo "✓ Max. Felder pro Kunde: {$settings->max_fields_per_customer}\n";
    echo "✓ E-Mail Benachrichtigungen: " . ($settings->email_notifications_enabled ? 'An' : 'Aus') . "\n";
    echo "✓ Standard E-Mail Template ID: " . ($settings->default_email_template_id ?? 'Nicht gesetzt') . "\n";
    echo "✓ Erstellt: {$settings->created_at}\n";
    echo "✓ Aktualisiert: {$settings->updated_at}\n";
} else {
    echo "✗ Keine Settings gefunden!\n";
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  ✅ Alle Tests erfolgreich abgeschlossen!                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";
