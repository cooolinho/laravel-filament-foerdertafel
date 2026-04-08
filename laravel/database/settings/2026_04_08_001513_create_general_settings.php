<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.default_payment_method', 'sepa');
        $this->migrator->add('general.default_rental_duration', 12);
        $this->migrator->add('general.max_fields_per_customer', 9);
        $this->migrator->add('general.email_notifications_enabled', true);
        $this->migrator->add('general.default_email_template_id', null);
        $this->migrator->add('general.required_document_ids', []);
        $this->migrator->add('general.sepa_mandate_text', 'Durch Klicken des \'SEPA-Mandat akzeptieren\'-Buttons und Absenden des Formulars unterschreiben Sie das Mandatsformular. Somit ermächtigen Sie (A) Ihren Verein, Ihrer Bank Anweisungen zur Belastung Ihres Kontos zu senden und (B) Ihre Bank, Ihr Konto gemäß den Anweisungen Ihres Vereins zu belasten. Als Teil Ihrer Rechte haben Sie gemäß den Bedingungen Ihrer Vereinbarung mit Ihrer Bank Anspruch auf eine Rückerstattung durch Ihre Bank. Eine Rückerstattung muss innerhalb von 8 Wochen ab dem Datum der Belastung Ihres Kontos beantragt werden.');
        $this->migrator->add('general.data_confirmation_text', 'Durch Angabe meiner Daten und Anklicken des Buttons \'Anmelden\' erkläre ich meine Daten als korrekt.');
        $this->migrator->add('general.inquiry_overview_info_text', 'Der Vertrag hat immer eine Laufzeit von einem Jahr und verlängert sich automatisch um ein weiteres Jahr, sofern nicht gekündigt wird.');
        $this->migrator->add('general.logo_path', null);
        $this->migrator->add('general.field_width_cm', 8.9);
        $this->migrator->add('general.field_height_cm', 5.1);
        $this->migrator->add('general.field_gap_cm', 1.2);
        $this->migrator->add('general.initial_setup_cost', 10);

        $this->migrator->add('general.invoice_organisation_name', null);
        $this->migrator->add('general.invoice_organisation_address', null);
        $this->migrator->add('general.invoice_bank_account_holder', null);
        $this->migrator->add('general.invoice_bank_iban', null);
        $this->migrator->add('general.invoice_bank_bic', null);
        $this->migrator->add('general.invoice_bank_name', null);
        $this->migrator->add('general.invoice_number_prefix', 'RE-');
        $this->migrator->add('general.invoice_vat_rate', 19.0);

        // 'inclusive' = MwSt. ist im Preis enthalten (Standard)
        // 'exclusive' = MwSt. wird auf den Preis aufgeschlagen
        $this->migrator->add('general.invoice_vat_mode', 'inclusive');
        $this->migrator->add('general.imprint_text', null);
    }
};
