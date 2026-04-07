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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string(Customer::name);
            $table->string(Customer::company_name)->nullable();
            $table->boolean(Customer::is_company)->default(false);
            $table->string(Customer::street)->nullable();
            $table->string(Customer::street_nr)->nullable();
            $table->string(Customer::zip)->nullable();
            $table->string(Customer::city)->nullable();
            $table->string(Customer::email)->unique();
            $table->string(Customer::phone)->nullable();
            $table->string(Customer::payment_method)->nullable();

            // SEPA-Bankdaten
            $table->string(Customer::account_holder)->nullable();
            $table->string(Customer::iban)->nullable();
            $table->string(Customer::bic)->nullable();
            $table->string(Customer::bank_name)->nullable();
            $table->boolean(Customer::sepa_mandate_accepted)->default(false);

            // Rechnungsanschrift
            $table->boolean(Customer::billing_use_postal_address)->default(true);
            $table->string(Customer::billing_street)->nullable();
            $table->string(Customer::billing_address2)->nullable();
            $table->string(Customer::billing_zip)->nullable();
            $table->string(Customer::billing_city)->nullable();
            $table->string(Customer::billing_country)->nullable();

            $table->text(Customer::notes)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
