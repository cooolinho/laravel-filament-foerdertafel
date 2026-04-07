<?php

use App\Models\Customer;
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
        Schema::table('customers', function (Blueprint $table) {
            // SEPA-Bankdaten
            $table->string(Customer::account_holder)->nullable()->after(Customer::payment_method);
            $table->string(Customer::iban)->nullable()->after(Customer::account_holder);
            $table->string(Customer::bic)->nullable()->after(Customer::iban);
            $table->string(Customer::bank_name)->nullable()->after(Customer::bic);
            $table->boolean(Customer::sepa_mandate_accepted)->default(false)->after(Customer::bank_name);

            // Rechnungsanschrift
            $table->boolean(Customer::billing_use_postal_address)->default(true)->after(Customer::sepa_mandate_accepted);
            $table->string(Customer::billing_street)->nullable()->after(Customer::billing_use_postal_address);
            $table->string(Customer::billing_address2)->nullable()->after(Customer::billing_street);
            $table->string(Customer::billing_zip)->nullable()->after(Customer::billing_address2);
            $table->string(Customer::billing_city)->nullable()->after(Customer::billing_zip);
            $table->string(Customer::billing_country)->nullable()->after(Customer::billing_city);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                Customer::account_holder,
                Customer::iban,
                Customer::bic,
                Customer::bank_name,
                Customer::sepa_mandate_accepted,
                Customer::billing_use_postal_address,
                Customer::billing_street,
                Customer::billing_address2,
                Customer::billing_zip,
                Customer::billing_city,
                Customer::billing_country,
            ]);
        });
    }
};

