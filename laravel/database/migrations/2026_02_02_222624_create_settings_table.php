<?php

use App\Models\Setting;
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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string(Setting::default_payment_method)->default(Setting::PAYMENT_METHOD_SEPA);
            $table->integer(Setting::default_rental_duration)->default(1); // in Monaten
            $table->integer(Setting::max_fields_per_customer)->default(10);
            $table->boolean(Setting::email_notifications_enabled)->default(true);
            $table->foreignId(Setting::default_email_template_id)->nullable()->constrained('email_templates')->nullOnDelete();
            $table->json(Setting::required_document_ids)->nullable();
            $table->text(Setting::sepa_mandate_text)->nullable();
            $table->text(Setting::data_confirmation_text)->nullable();
            $table->text(Setting::inquiry_overview_info_text)->nullable();
            $table->string(Setting::logo_path)->nullable();
            $table->decimal(Setting::field_width_cm, 5, 2)->default(8.9);
            $table->decimal(Setting::field_height_cm, 5, 2)->default(5.1);
            $table->decimal(Setting::field_gap_cm, 5, 2)->default(1.2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
