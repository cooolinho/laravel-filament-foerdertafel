<?php

namespace Database\Seeders\System;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::firstOrCreate(
            [],  // Es gibt immer nur einen Datensatz
            [
                Setting::default_payment_method      => Setting::PAYMENT_METHOD_SEPA,
                Setting::default_rental_duration     => 1,
                Setting::max_fields_per_customer     => 10,
                Setting::field_width_cm              => 8.9,
                Setting::field_height_cm             => 5.1,
                Setting::field_gap_cm                => 1.2,
                Setting::email_notifications_enabled => true,
                Setting::default_email_template_id   => null,
                Setting::required_document_ids       => null,
                Setting::sepa_mandate_text           => 'Durch Klicken des \'SEPA-Mandat akzeptieren\'-Buttons und Absenden des Formulars unterschreiben Sie das Mandatsformular. Somit ermächtigen Sie (A) Ihren Verein, Ihrer Bank Anweisungen zur Belastung Ihres Kontos zu senden und (B) Ihre Bank, Ihr Konto gemäß den Anweisungen Ihres Vereins zu belasten. Als Teil Ihrer Rechte haben Sie gemäß den Bedingungen Ihrer Vereinbarung mit Ihrer Bank Anspruch auf eine Rückerstattung durch Ihre Bank. Eine Rückerstattung muss innerhalb von 8 Wochen ab dem Datum der Belastung Ihres Kontos beantragt werden.',
                Setting::data_confirmation_text     => 'Durch Angabe meiner Daten und Anklicken des Buttons \'Anmelden\' erkläre ich meine Daten als korrekt.',
                Setting::inquiry_overview_info_text => 'Bitte prüfen Sie Ihre Angaben sorgfältig. Nach dem Absenden erhalten Sie eine Bestätigung per E-Mail. Wir melden uns in Kürze bei Ihnen.',
            ]
        );
    }
}
