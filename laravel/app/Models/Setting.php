<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\Setting
 *
 * @property int $id
 * @property string|null $default_payment_method
 * @property int|null $default_rental_duration
 * @property int|null $max_fields_per_customer
 * @property bool|null $email_notifications_enabled
 * @property int|null $default_email_template_id
 * @property array|null $required_document_ids
 * @property string|null $sepa_mandate_text
 * @property string|null $data_confirmation_text
 * @property string|null $inquiry_overview_info_text
 * @property array|null $inquiry_confirmation_attachment_ids
 * @property float|null $field_width_cm
 * @property float|null $field_height_cm
 * @property float|null $field_gap_cm
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read EmailTemplate|null $defaultEmailTemplate
 */
class Setting extends Model
{
    // Konstanten für Felder
    const string default_payment_method = 'default_payment_method';
    const string default_rental_duration = 'default_rental_duration';
    const string max_fields_per_customer = 'max_fields_per_customer';
    const string field_width_cm = 'field_width_cm';
    const string field_height_cm = 'field_height_cm';
    const string field_gap_cm = 'field_gap_cm';
    const string email_notifications_enabled = 'email_notifications_enabled';
    const string default_email_template_id = 'default_email_template_id';
    const string required_document_ids = 'required_document_ids';
    const string sepa_mandate_text = 'sepa_mandate_text';
    const string data_confirmation_text = 'data_confirmation_text';
    const string inquiry_overview_info_text = 'inquiry_overview_info_text';


    // Zahlungsmethoden Konstanten
    const string PAYMENT_METHOD_SEPA = 'sepa';
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
        self::field_width_cm,
        self::field_height_cm,
        self::field_gap_cm,
        self::email_notifications_enabled,
        self::default_email_template_id,
        self::required_document_ids,
        self::sepa_mandate_text,
        self::data_confirmation_text,
        self::inquiry_overview_info_text,
    ];

    protected $casts = [
        self::email_notifications_enabled => 'boolean',
        self::default_rental_duration => 'integer',
        self::max_fields_per_customer => 'integer',
        self::field_width_cm => 'float',
        self::field_height_cm => 'float',
        self::field_gap_cm => 'float',
        self::default_email_template_id => 'integer',
        self::required_document_ids => 'array',
    ];

    /**
     * Beziehung zum Standard-E-Mail-Template
     */
    public function defaultEmailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, self::default_email_template_id);
    }

    /**
     * Gibt alle Pflicht-Dokumente zurück, die bei der Anfrage akzeptiert werden müssen.
     *
     * @return Collection<int, Document>
     */
    public static function getRequiredDocuments(): Collection
    {
        $ids = self::get(self::required_document_ids, []);

        if (empty($ids)) {
            return new Collection();
        }

        return Document::whereIn('id', $ids)
            ->where(Document::is_current_version, true)
            ->orderBy(Document::title)
            ->get();
    }


    /**
     * @return int
     */
    public static function getMaxFieldsPerCustomer(): int
    {
        return (int) self::get(self::max_fields_per_customer, 9);
    }

    /**
     * Berechnet die maximale Anzahl auswählbarer Zeilen (Rows) aus max_fields_per_customer.
     * Formel: floor(sqrt(max_fields))
     */
    public static function getMaxSelectionRows(): int
    {
        $max = self::getMaxFieldsPerCustomer();
        return max(1, (int) floor(sqrt($max)));
    }

    /**
     * Berechnet die maximale Anzahl auswählbarer Spalten (Cols) aus max_fields_per_customer.
     * Formel: ceil(max_fields / max_rows)
     */
    public static function getMaxSelectionCols(): int
    {
        $max = self::getMaxFieldsPerCustomer();
        $maxRows = self::getMaxSelectionRows();
        return max(1, (int) ceil($max / $maxRows));
    }

    /**
     * Gibt alle gültigen Rechteck-Konfigurationen zurück, die bei max_fields_per_customer
     * möglich sind. Jeder Eintrag enthält rows, cols, total und die physischen Maße.
     *
     * @return array<int, array{rows: int, cols: int, total: int, width_cm: float, height_cm: float}>
     */
    public static function getValidRectangles(): array
    {
        $maxRows = self::getMaxSelectionRows();
        $maxCols = self::getMaxSelectionCols();
        $maxFields = self::getMaxFieldsPerCustomer();

        $rectangles = [];
        for ($r = 1; $r <= $maxRows; $r++) {
            for ($c = 1; $c <= $maxCols; $c++) {
                $total = $r * $c;
                if ($total <= $maxFields) {
                    $rectangles[] = [
                        'rows'      => $r,
                        'cols'      => $c,
                        'total'     => $total,
                        'width_cm'  => self::calculatePhysicalWidth($c),
                        'height_cm' => self::calculatePhysicalHeight($r),
                    ];
                }
            }
        }

        return $rectangles;
    }

    /**
     * Berechnet die physische Breite (cm) für eine gegebene Spaltenanzahl.
     */
    public static function calculatePhysicalWidth(int $cols): float
    {
        $width = (float) self::get(self::field_width_cm, 8.9);
        $gap   = (float) self::get(self::field_gap_cm, 1.2);
        return round($cols * $width + ($cols - 1) * $gap, 2);
    }

    /**
     * Berechnet die physische Höhe (cm) für eine gegebene Zeilenanzahl.
     */
    public static function calculatePhysicalHeight(int $rows): float
    {
        $height = (float) self::get(self::field_height_cm, 5.1);
        $gap    = (float) self::get(self::field_gap_cm, 1.2);
        return round($rows * $height + ($rows - 1) * $gap, 2);
    }

    /**
     * Verfügbare Zahlungsmethoden
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::PAYMENT_METHOD_SEPA => 'Überweisung',
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
