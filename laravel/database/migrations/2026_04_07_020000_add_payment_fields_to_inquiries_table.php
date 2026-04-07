<?php

use App\Models\Inquiry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            // Zahlungsmethode
            $table->string(Inquiry::payment_method)->default('sepa')->after(Inquiry::city);

            // SEPA-Bankdaten
            $table->string(Inquiry::account_holder)->nullable()->after(Inquiry::payment_method);
            $table->string(Inquiry::iban)->nullable()->after(Inquiry::account_holder);
            $table->string(Inquiry::bic)->nullable()->after(Inquiry::iban);
            $table->string(Inquiry::bank_name)->nullable()->after(Inquiry::bic);
            $table->boolean(Inquiry::sepa_mandate_accepted)->default(false)->after(Inquiry::bank_name);

            // Rechnungsanschrift
            $table->boolean(Inquiry::billing_use_postal_address)->default(true)->after(Inquiry::sepa_mandate_accepted);
            $table->string(Inquiry::billing_street)->nullable()->after(Inquiry::billing_use_postal_address);
            $table->string(Inquiry::billing_address2)->nullable()->after(Inquiry::billing_street);
            $table->string(Inquiry::billing_zip)->nullable()->after(Inquiry::billing_address2);
            $table->string(Inquiry::billing_city)->nullable()->after(Inquiry::billing_zip);
            $table->string(Inquiry::billing_country)->nullable()->default('Deutschland')->after(Inquiry::billing_city);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn([
                Inquiry::payment_method,
                Inquiry::account_holder,
                Inquiry::iban,
                Inquiry::bic,
                Inquiry::bank_name,
                Inquiry::sepa_mandate_accepted,
                Inquiry::billing_use_postal_address,
                Inquiry::billing_street,
                Inquiry::billing_address2,
                Inquiry::billing_zip,
                Inquiry::billing_city,
                Inquiry::billing_country,
            ]);
        });
    }
};

