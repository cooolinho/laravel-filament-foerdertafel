<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rechnung {{ $invoiceNumber }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            color: #1f2937;
            background: #ffffff;
            line-height: 1.5;
            padding: 50px;
        }

        /* ── Kopfbereich ── */
        .header {
            width: 100%;
            margin-bottom: 28px;
        }
        .header-org {
            font-size: 14pt;
            font-weight: bold;
            color: #16a34a;
        }
        .header-org-address {
            font-size: 8.5pt;
            color: #6b7280;
            margin-top: 3px;
        }
        .header-right {
            text-align: right;
        }
        .invoice-label {
            font-size: 22pt;
            font-weight: bold;
            color: #16a34a;
            letter-spacing: -0.5px;
        }
        .invoice-meta {
            font-size: 9pt;
            color: #6b7280;
            margin-top: 4px;
        }

        /* Trennlinie */
        .divider {
            border: none;
            border-top: 2px solid #16a34a;
            margin: 16px 0;
        }
        .divider-light {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 12px 0;
        }

        /* ── Adressblock ── */
        .address-section {
            margin-bottom: 24px;
        }
        .address-label {
            font-size: 7.5pt;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
        }
        .address-name {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .address-line {
            font-size: 9.5pt;
            color: #374151;
        }

        /* ── Auftragsdetails ── */
        .details-section {
            margin-bottom: 24px;
            background: #f9fafb;
            border-radius: 4px;
            padding: 12px 14px;
        }
        .details-title {
            font-size: 9pt;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 8px;
        }
        .details-grid {
            width: 100%;
        }
        .details-grid td {
            font-size: 9pt;
            padding: 2px 8px 2px 0;
            vertical-align: top;
        }
        .details-grid .label {
            color: #6b7280;
            white-space: nowrap;
            width: 160px;
        }
        .details-grid .value {
            font-weight: bold;
            color: #111827;
        }

        /* ── Positionstabelle ── */
        .positions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .positions-table thead th {
            background: #16a34a;
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: bold;
            padding: 8px 10px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .positions-table thead th.text-right {
            text-align: right;
        }
        .positions-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        .positions-table tbody td {
            padding: 7px 10px;
            font-size: 9pt;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        .positions-table tbody td.text-right {
            text-align: right;
        }
        .positions-table tfoot td {
            padding: 8px 10px;
            font-size: 9.5pt;
        }
        .total-row td {
            background: #f0fdf4;
            border-top: 2px solid #16a34a;
            font-weight: bold;
            font-size: 11pt;
            color: #14532d;
        }
        .total-row td.text-right {
            text-align: right;
        }

        /* ── Zahlungshinweis / Bankverbindung ── */
        .payment-section {
            margin-top: 28px;
            border: 1px solid #d1fae5;
            border-left: 4px solid #16a34a;
            background: #f0fdf4;
            border-radius: 4px;
            padding: 14px 16px;
        }
        .payment-title {
            font-size: 10pt;
            font-weight: bold;
            color: #15803d;
            margin-bottom: 8px;
        }
        .payment-table {
            width: 100%;
        }
        .payment-table td {
            font-size: 9pt;
            padding: 2px 0;
            vertical-align: top;
        }
        .payment-table .label {
            color: #166534;
            white-space: nowrap;
            width: 180px;
        }
        .payment-table .value {
            font-weight: bold;
            color: #14532d;
        }
        .payment-amount {
            font-size: 12pt;
            font-weight: bold;
            color: #15803d;
            margin-top: 10px;
        }

        /* ── Hinweistext ── */
        .notice-section {
            margin-top: 20px;
            font-size: 8.5pt;
            color: #6b7280;
            line-height: 1.6;
        }

        /* ── Footer ── */
        .footer {
            margin-top: 40px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
        }
    </style>
</head>
<body>

{{-- ═══ HEADER ═══ --}}
<table class="header" width="100%">
    <tr>
        <td width="60%">
            <div class="header-org">{{ $settings->invoice_organisation_name ?? $appName }}</div>
            @if($settings->invoice_organisation_address)
                <div class="header-org-address">{!! nl2br(e($settings->invoice_organisation_address)) !!}</div>
            @endif
        </td>
        <td width="40%" class="header-right">
            <div class="invoice-label">RECHNUNG</div>
            <div class="invoice-meta">
                Nr.: <strong>{{ $invoiceNumber }}</strong><br>
                Datum: <strong>{{ $invoiceDate }}</strong>
            </div>
        </td>
    </tr>
</table>

<hr class="divider">

{{-- ═══ ADRESSBLOCK ═══ --}}
<div class="address-section">
    <div class="address-label">Rechnungsempfänger</div>
    <div class="address-name">{{ $billingAddress['name'] }}</div>
    @if(!empty($billingAddress['street']))
        <div class="address-line">{{ $billingAddress['street'] }}</div>
    @endif
    @if(!empty($billingAddress['city']))
        <div class="address-line">{{ $billingAddress['city'] }}</div>
    @endif
    @if(!empty($billingAddress['country']))
        <div class="address-line">{{ $billingAddress['country'] }}</div>
    @endif
    <div class="address-line" style="margin-top: 4px; color: #6b7280;">
        E-Mail: {{ $inquiry->customer_email }}
        @if($inquiry->customer_phone)
            &nbsp;·&nbsp; Tel.: {{ $inquiry->customer_phone }}
        @endif
    </div>
</div>

{{-- ═══ AUFTRAGSDETAILS ═══ --}}
<div class="details-section">
    <div class="details-title">Auftragsdetails</div>
    <table class="details-grid">
        <tr>
            <td class="label">Anfrage-Nr.:</td>
            <td class="value">#{{ $inquiry->id }}</td>
            <td class="label">Fördertafel:</td>
            <td class="value">{{ $board?->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Mietbeginn:</td>
            <td class="value">{{ $startDate }}</td>
            <td class="label">Standort:</td>
            <td class="value">{{ $location?->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Mietende:</td>
            <td class="value">{{ $endDate }}</td>
            <td class="label">Mietdauer:</td>
            <td class="value">{{ $inquiry->rental_months }} Monat(e)</td>
        </tr>
    </table>
</div>

{{-- ═══ POSITIONSTABELLE ═══ --}}
<table class="positions-table">
    <thead>
        <tr>
            <th width="40%">Beschreibung</th>
            <th width="20%" class="text-right">Preis/Monat</th>
            <th width="15%" class="text-right">Monate</th>
            <th width="25%" class="text-right">Gesamt (netto)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($lineItems as $item)
            <tr>
                <td>{{ $item['description'] }}</td>
                @if($item['is_setup'])
                    <td class="text-right" colspan="2" style="color: #6b7280; font-size: 8.5pt;">einmalig</td>
                @else
                    <td class="text-right">{{ number_format($item['price_per_month'], 2, ',', '.') }} €</td>
                    <td class="text-right">{{ $item['months'] }}</td>
                @endif
                <td class="text-right">{{ number_format($item['total'], 2, ',', '.') }} €</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4"><hr class="divider-light" style="margin: 4px 0;"></td>
        </tr>
        {{-- Zwischensumme --}}
        <tr>
            <td colspan="3" style="text-align: right; padding-right: 10px; color: #6b7280; font-size: 9pt;">
                {{ ($vatRate > 0 && $vatInclusive) ? 'Gesamtbetrag (brutto):' : 'Zwischensumme (netto):' }}
            </td>
            <td class="text-right" style="color: #374151; font-size: 9pt;">
                {{ number_format($netTotal + $vatAmount, 2, ',', '.') }} €
            </td>
        </tr>
        {{-- MwSt.-Zeile (nur wenn vatRate > 0) --}}
        @if($vatRate > 0)
        <tr>
            <td colspan="3" style="text-align: right; padding-right: 10px; color: #6b7280; font-size: 9pt;">
                {{ $vatInclusive ? 'inkl.' : 'zzgl.' }} {{ number_format($vatRate, 0, ',', '.') }}&#8239;% MwSt.:
            </td>
            <td class="text-right" style="color: #374151; font-size: 9pt;">
                {{ number_format($vatAmount, 2, ',', '.') }} €
            </td>
        </tr>
        @endif
        {{-- Gesamtbetrag (Brutto) --}}
        <tr class="total-row">
            <td colspan="3">
                @if($vatRate > 0)
                    <strong>Gesamtbetrag ({{ $vatInclusive ? 'inkl.' : 'brutto, zzgl.' }} MwSt.):</strong>
                @else
                    <strong>Gesamtbetrag:</strong>
                @endif
            </td>
            <td class="text-right">
                <strong>{{ number_format($grossTotal, 2, ',', '.') }} €</strong>
            </td>
        </tr>
    </tfoot>
</table>

{{-- ═══ ZAHLUNGSHINWEIS ═══ --}}
<div class="payment-section">
    <div class="payment-title">&#128179; Zahlungsaufforderung</div>

    <p style="font-size: 9pt; color: #166534; margin-bottom: 10px;">
        Bitte überweisen Sie den Gesamtbetrag auf folgendes Konto:
    </p>

    <table class="payment-table">
        @if($settings->invoice_bank_account_holder)
        <tr>
            <td class="label">Kontoinhaber:</td>
            <td class="value">{{ $settings->invoice_bank_account_holder }}</td>
        </tr>
        @endif
        @if($settings->invoice_bank_name)
        <tr>
            <td class="label">Bank:</td>
            <td class="value">{{ $settings->invoice_bank_name }}</td>
        </tr>
        @endif
        @if($settings->invoice_bank_iban)
        <tr>
            <td class="label">IBAN:</td>
            <td class="value">{{ $settings->invoice_bank_iban }}</td>
        </tr>
        @endif
        @if($settings->invoice_bank_bic)
        <tr>
            <td class="label">BIC:</td>
            <td class="value">{{ $settings->invoice_bank_bic }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Verwendungszweck:</td>
            <td class="value">{{ $invoiceNumber }} – {{ $customerName }}</td>
        </tr>
    </table>

    <div class="payment-amount">
        Betrag: {{ number_format($grossTotal, 2, ',', '.') }} €
    </div>
</div>

{{-- ═══ HINWEIS ═══ --}}
<div class="notice-section">
    <p>Diese Rechnung wurde automatisch erstellt. Bei Fragen wenden Sie sich bitte an uns.
    Der Betrag ist fällig nach Erhalt dieser Rechnung.</p>
</div>

{{-- ═══ FOOTER ═══ --}}
<div class="footer">
    {{ $settings->invoice_organisation_name ?? $appName }}
    @if($settings->invoice_organisation_address)
        &nbsp;·&nbsp; {{ str_replace("\n", ' · ', $settings->invoice_organisation_address) }}
    @endif
    &nbsp;·&nbsp; Rechnungsnummer: {{ $invoiceNumber }}
</div>

</body>
</html>

