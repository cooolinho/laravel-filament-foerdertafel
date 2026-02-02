<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    // Konstanten für Felder
    const string default_payment_method = 'default_payment_method';
    const string default_rental_duration = 'default_rental_duration';
    const string max_fields_per_customer = 'max_fields_per_customer';
    const string email_notifications_enabled = 'email_notifications_enabled';
    const string default_email_template_id = 'default_email_template_id';

    // Zahlungsmethoden Konstanten
    const string PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';
    const string PAYMENT_METHOD_CREDIT_CARD = 'credit_card';
    const string PAYMENT_METHOD_PAYPAL = 'paypal';
    const string PAYMENT_METHOD_CASH = 'cash';

    // Cache-Schlüssel
    const string CACHE_KEY = 'app_settings';
    const int CACHE_TTL = 3600; // 1 Stunde

    protected $fillable = [
        self::default_payment_method,
        self::default_rental_duration,
        self::max_fields_per_customer,
        self::email_notifications_enabled,
        self::default_email_template_id,
    ];

    protected $casts = [
        self::email_notifications_enabled => 'boolean',
        self::default_rental_duration => 'integer',
        self::max_fields_per_customer => 'integer',
    ];

    /**
     * Beziehung zum Standard-E-Mail-Template
     */
    public function defaultEmailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, self::default_email_template_id);
    }

    /**
     * Verfügbare Zahlungsmethoden
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::PAYMENT_METHOD_BANK_TRANSFER => 'Überweisung',
            self::PAYMENT_METHOD_CREDIT_CARD => 'Kreditkarte',
            self::PAYMENT_METHOD_PAYPAL => 'PayPal',
            self::PAYMENT_METHOD_CASH => 'Bar',
        ];
    }

    /**
     * Aktuelle Einstellungen abrufen (Singleton-Pattern mit Cache)
     */
    public static function current(): ?Setting
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return static::first();
        });
    }

    /**
     * Einzelne Einstellung abrufen
     *
     * @param string $key Der Schlüssel der Einstellung
     * @param mixed $default Fallback-Wert, falls Setting nicht existiert
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = self::current();

        if (!$settings) {
            return $default;
        }

        return $settings->$key ?? $default;
    }

    /**
     * Cache leeren
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Cache nach dem Speichern automatisch leeren
     */
    protected static function booted(): void
    {
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}
