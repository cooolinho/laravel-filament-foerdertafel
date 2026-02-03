<?php

use App\Models\Rental;
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
        Schema::table('rentals', function (Blueprint $table) {
            $table->timestamp(Rental::paid_at)->nullable()->after(Rental::status);
        });

        // Aktualisiere den Status ENUM um 'pending' und 'paid' hinzuzufügen
        DB::statement("ALTER TABLE rentals MODIFY COLUMN status ENUM('pending', 'paid', 'active', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn(Rental::paid_at);
        });

        // Setze den Status ENUM zurück
        DB::statement("ALTER TABLE rentals MODIFY COLUMN status ENUM('active', 'completed', 'cancelled') DEFAULT 'active'");
    }
};
