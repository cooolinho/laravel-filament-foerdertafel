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
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId(Inquiry::board_id)->constrained()->onDelete('cascade');
            $table->string(Inquiry::customer_name);
            $table->string(Inquiry::customer_email);
            $table->string(Inquiry::customer_phone)->nullable();
            $table->boolean(Inquiry::is_company)->default(false);
            $table->string(Inquiry::company_name)->nullable();
            $table->string(Inquiry::street)->nullable();
            $table->string(Inquiry::street_nr)->nullable();
            $table->string(Inquiry::zip)->nullable();
            $table->string(Inquiry::city)->nullable();

            // Zahlungsmethode
            $table->string(Inquiry::payment_method)->default('sepa');

            // SEPA-Bankdaten
            $table->string(Inquiry::account_holder)->nullable();
            $table->string(Inquiry::iban)->nullable();
            $table->string(Inquiry::bic)->nullable();
            $table->string(Inquiry::bank_name)->nullable();
            $table->boolean(Inquiry::sepa_mandate_accepted)->default(false);

            // Rechnungsanschrift
            $table->boolean(Inquiry::billing_use_postal_address)->default(true);
            $table->string(Inquiry::billing_street)->nullable();
            $table->string(Inquiry::billing_address2)->nullable();
            $table->string(Inquiry::billing_zip)->nullable();
            $table->string(Inquiry::billing_city)->nullable();
            $table->string(Inquiry::billing_country)->nullable()->default('Deutschland');

            $table->json(Inquiry::attachments)->nullable();
            $table->date(Inquiry::start_date);
            $table->date(Inquiry::end_date);
            $table->unsignedTinyInteger(Inquiry::rental_months)->default(1);
            $table->json(Inquiry::requested_fields); // Array of field IDs
            $table->enum(Inquiry::status, [
                Inquiry::STATUS_PENDING,
                Inquiry::STATUS_APPROVED,
                Inquiry::STATUS_REJECTED,
                Inquiry::STATUS_CONVERTED
            ])->default(Inquiry::STATUS_PENDING);
            $table->text(Inquiry::message)->nullable();
            $table->text(Inquiry::admin_notes)->nullable();
            $table->foreignId(Inquiry::rental_id)->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
