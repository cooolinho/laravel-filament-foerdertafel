<?php

use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->string(Setting::default_payment_method)->default(Setting::PAYMENT_METHOD_BANK_TRANSFER);
            $table->integer(Setting::default_rental_duration)->default(1); // in Monaten
            $table->integer(Setting::max_fields_per_customer)->default(10);
            $table->boolean(Setting::email_notifications_enabled)->default(true);
            $table->foreignId(Setting::default_email_template_id)->nullable()->constrained('email_templates')->nullOnDelete();
            $table->foreignId(Setting::terms_conditions_document_id)->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        // Standardwerte in die Tabelle einfügen
        DB::table('settings')->insert([
            Setting::default_payment_method => Setting::PAYMENT_METHOD_BANK_TRANSFER,
            Setting::default_rental_duration => 1,
            Setting::max_fields_per_customer => 10,
            Setting::email_notifications_enabled => true,
            Setting::default_email_template_id => null,
            Setting::terms_conditions_document_id => null,
            Model::CREATED_AT => now(),
            Model::UPDATED_AT => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
