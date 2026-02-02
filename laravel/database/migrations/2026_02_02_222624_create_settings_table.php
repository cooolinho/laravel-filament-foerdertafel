<?php

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
            $table->string('default_payment_method')->default('bank_transfer');
            $table->integer('default_rental_duration')->default(1); // in Monaten
            $table->integer('max_fields_per_customer')->default(10);
            $table->boolean('email_notifications_enabled')->default(true);
            $table->foreignId('default_email_template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->timestamps();
        });

        // Standardwerte in die Tabelle einfügen
        DB::table('settings')->insert([
            'default_payment_method' => 'bank_transfer',
            'default_rental_duration' => 1,
            'max_fields_per_customer' => 10,
            'email_notifications_enabled' => true,
            'default_email_template_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
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
