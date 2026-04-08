<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Inquiry;
use App\Settings\GeneralSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    public function __construct(
        protected GeneralSettings $settings
    ) {}

    /**
     * Generiert eine Rechnungs-PDF für eine Unternehmensanfrage und speichert sie im Storage.
     *
     * @param  Inquiry $inquiry  Die Anfrage, für die die Rechnung erstellt werden soll.
     * @return string            Relativer Pfad im Storage (app/-Disk) zur generierten PDF.
     */
    public function generate(Inquiry $inquiry): string
    {
        // Beziehungen sicherstellen
        $inquiry->loadMissing(['board.location', 'fields']);

        $board    = $inquiry->board;
        $location = $board?->location;
        $fields   = $inquiry->fields;

        // Rechnungsnummer generieren
        $prefix        = $this->settings->invoice_number_prefix ?? 'RE-';
        $invoiceNumber = $prefix . date('Y') . '-' . str_pad($inquiry->id, 5, '0', STR_PAD_LEFT);

        // Zeilenposten der Rechnung aufbauen
        $lineItems = [];
        foreach ($fields as $field) {
            $lineTotal = (float) $field->price_per_month * $inquiry->rental_months;
            $lineItems[] = [
                'description' => sprintf(
                    'Feld %s – %s (%s × %s cm)',
                    $field->getIdentifier(),
                    $field->name ?? $field->getIdentifier(),
                    $field->width ?? 1,
                    $field->height ?? 1,
                ),
                'price_per_month' => (float) $field->price_per_month,
                'months'          => $inquiry->rental_months,
                'total'           => $lineTotal,
                'is_setup'        => false,
            ];
        }

        // Einrichtungskosten als eigene Position
        $setupCost = (float) $this->settings->initial_setup_cost;
        if ($setupCost > 0) {
            $lineItems[] = [
                'description'     => 'Einmalige Einrichtungskosten',
                'price_per_month' => null,
                'months'          => null,
                'total'           => $setupCost,
                'is_setup'        => true,
            ];
        }

        $baseTotal = array_sum(array_column($lineItems, 'total'));

        // MwSt. berechnen – abhängig vom konfigurierten Modus
        $vatRate      = (float) ($this->settings->invoice_vat_rate ?? 0);
        $vatInclusive = $this->settings->isVatInclusive();

        if ($vatRate > 0) {
            if ($vatInclusive) {
                // MwSt. ist im Preis enthalten → herausrechnen
                $grossTotal = $baseTotal;
                $netTotal   = round($baseTotal / (1 + $vatRate / 100), 2);
                $vatAmount  = round($grossTotal - $netTotal, 2);
            } else {
                // MwSt. wird aufgeschlagen
                $netTotal   = $baseTotal;
                $vatAmount  = round($baseTotal * $vatRate / 100, 2);
                $grossTotal = round($netTotal + $vatAmount, 2);
            }
        } else {
            $netTotal   = $baseTotal;
            $vatAmount  = 0.0;
            $grossTotal = $baseTotal;
        }

        // Rechnungsadresse zusammenstellen
        if ($inquiry->billing_use_postal_address) {
            $billingAddress = [
                'name'    => $inquiry->is_company ? $inquiry->company_name : $inquiry->customer_name,
                'street'  => trim(($inquiry->street ?? '') . ' ' . ($inquiry->street_nr ?? '')),
                'city'    => trim(($inquiry->zip ?? '') . ' ' . ($inquiry->city ?? '')),
                'country' => '',
            ];
        } else {
            $billingAddress = [
                'name'    => $inquiry->is_company ? $inquiry->company_name : $inquiry->customer_name,
                'street'  => $inquiry->billing_street ?? '',
                'city'    => trim(($inquiry->billing_zip ?? '') . ' ' . ($inquiry->billing_city ?? '')),
                'country' => $inquiry->billing_country ?? '',
            ];
        }

        // PDF über DomPDF rendern
        $pdf = Pdf::loadView('pdf.invoice', [
            'inquiry'           => $inquiry,
            'board'             => $board,
            'location'          => $location,
            'lineItems'         => $lineItems,
            'netTotal'          => $netTotal,
            'vatRate'           => $vatRate,
            'vatAmount'         => $vatAmount,
            'vatInclusive'      => $vatInclusive,
            'grossTotal'        => $grossTotal,
            'invoiceNumber'     => $invoiceNumber,
            'invoiceDate'       => $inquiry->created_at?->format('d.m.Y') ?? now()->format('d.m.Y'),
            'startDate'         => Carbon::parse($inquiry->start_date)->format('d.m.Y'),
            'endDate'           => Carbon::parse($inquiry->end_date)->format('d.m.Y'),
            'billingAddress'    => $billingAddress,
            'customerName'      => $inquiry->is_company && $inquiry->company_name
                                       ? $inquiry->company_name
                                       : $inquiry->customer_name,
            'settings'          => $this->settings,
            'appName'           => config('app.name'),
        ]);

        $pdf->setPaper('A4', 'portrait');

        $directory = "documents/generated/invoices";
        $filename = "invoice_{$inquiry->id}_" . now()->format('Ymd_His') . '.pdf';
        $file_path = $directory . '/' . $filename;
        Storage::disk(Document::STORAGE)->put($file_path, $pdf->output());

        $document = Document::query()->create([
            Document::title       => "Rechnung für Anfrage #{$inquiry->id}",
            Document::type        => Document::TYPE_INVOICE,
            Document::file_path   => $file_path,
        ]);

        return $document->id;
    }
}

