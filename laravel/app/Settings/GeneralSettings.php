<?php

namespace App\Settings;

use App\Models\Document;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    // Payment Methods
    const string PAYMENT_METHOD_SEPA = 'sepa';
    const string PAYMENT_METHOD_DONATION = 'donation';

    // constants for properties
    const string default_payment_method = 'default_payment_method';
    const string default_rental_duration = 'default_rental_duration';
    const string max_fields_per_customer = 'max_fields_per_customer';
    const string email_notifications_enabled = 'email_notifications_enabled';
    const string default_email_template_id = 'default_email_template_id';
    const string required_document_ids = 'required_document_ids';
    const string sepa_mandate_text = 'sepa_mandate_text';
    const string data_confirmation_text = 'data_confirmation_text';
    const string inquiry_overview_info_text = 'inquiry_overview_info_text';
    const string logo_path = 'logo_path';
    const string field_width_cm = 'field_width_cm';
    const string field_height_cm = 'field_height_cm';
    const string field_gap_cm = 'field_gap_cm';
    const string initial_setup_cost = 'initial_setup_cost';

    // properties
    public string $default_payment_method;
    public int $default_rental_duration;
    public int $max_fields_per_customer;
    public bool $email_notifications_enabled;
    public ?int $default_email_template_id;
    public ?array $required_document_ids;
    public ?string $sepa_mandate_text;
    public ?string $data_confirmation_text;
    public ?string $inquiry_overview_info_text;
    public ?string $logo_path;
    public float $field_width_cm;
    public float $field_height_cm;
    public float $field_gap_cm;
    public float $initial_setup_cost;

    /**
     * @return string
     */
    public static function group(): string
    {
        return 'general';
    }

    public static function paymentMethods(): array
    {
        return [
            self::PAYMENT_METHOD_SEPA => 'Überweisung (SEPA)',
            self::PAYMENT_METHOD_DONATION => 'Spendenquittung nur auf Anfrage',
        ];
    }

    /**
     * Gibt die öffentlich zugängliche URL des Logos zurück.
     * Das Logo wird auf dem public-Disk gespeichert, damit E-Mail-Clients
     * es direkt per URL einbinden können.
     */
    public function getLogoUrl(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    /**
     * Gibt alle Pflicht-Dokumente zurück, die bei der Anfrage akzeptiert werden müssen.
     *
     * @return Collection<int, Document>
     */
    public function getRequiredDocuments(): Collection
    {
        $ids = $this->required_document_ids ?? [];

        if (empty($ids)) {
            return new Collection();
        }

        return Document::query()
            ->whereIn('id', $ids)
            ->where(Document::is_current_version, true)
            ->orderBy(Document::title)
            ->get();
    }

    /**
     * @return int
     */
    public function getMaxFieldsPerCustomer(): int
    {
        return (int) $this->max_fields_per_customer;
    }

    /**
     * Berechnet die maximale Anzahl auswählbarer Zeilen (Rows) aus max_fields_per_customer.
     * Formel: floor(sqrt(max_fields))
     */
    public function getMaxSelectionRows(): int
    {
        $max = $this->getMaxFieldsPerCustomer();
        return max(1, (int) floor(sqrt($max)));
    }

    /**
     * Berechnet die maximale Anzahl auswählbarer Spalten (Cols) aus max_fields_per_customer.
     * Formel: ceil(max_fields / max_rows)
     */
    public function getMaxSelectionCols(): int
    {
        $max = $this->getMaxFieldsPerCustomer();
        $maxRows = $this->getMaxSelectionRows();
        return max(1, (int) ceil($max / $maxRows));
    }

    /**
     * Gibt alle gültigen Rechteck-Konfigurationen zurück, die bei max_fields_per_customer
     * möglich sind. Jeder Eintrag enthält rows, cols, total und die physischen Maße.
     *
     * @return array<int, array{rows: int, cols: int, total: int, width_cm: float, height_cm: float}>
     */
    public function getValidRectangles(): array
    {
        $maxRows = $this->getMaxSelectionRows();
        $maxCols = $this->getMaxSelectionCols();
        $maxFields = $this->getMaxFieldsPerCustomer();

        $rectangles = [];
        for ($r = 1; $r <= $maxRows; $r++) {
            for ($c = 1; $c <= $maxCols; $c++) {
                $total = $r * $c;
                if ($total <= $maxFields) {
                    $rectangles[] = [
                        'rows'      => $r,
                        'cols'      => $c,
                        'total'     => $total,
                        'width_cm'  => $this->calculatePhysicalWidth($c),
                        'height_cm' => $this->calculatePhysicalHeight($r),
                    ];
                }
            }
        }

        return $rectangles;
    }

    /**
     * Berechnet die physische Breite (cm) für eine gegebene Spaltenanzahl.
     */
    public function calculatePhysicalWidth(int $cols): float
    {
        $width = (float) $this->field_width_cm;
        $gap   = (float) $this->field_gap_cm;
        return round($cols * $width + ($cols - 1) * $gap, 2);
    }

    /**
     * Berechnet die physische Höhe (cm) für eine gegebene Zeilenanzahl.
     */
    public function calculatePhysicalHeight(int $rows): float
    {
        $height = (float) $this->field_height_cm;
        $gap    = (float) $this->field_gap_cm;
        return round($rows * $height + ($rows - 1) * $gap, 2);
    }
}
