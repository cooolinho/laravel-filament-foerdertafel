<?php

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
        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('field_width_cm', 5, 2)->default(8.9)->after('max_fields_per_customer');
            $table->decimal('field_height_cm', 5, 2)->default(5.1)->after('field_width_cm');
            $table->decimal('field_gap_cm', 5, 2)->default(1.2)->after('field_height_cm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['field_width_cm', 'field_height_cm', 'field_gap_cm']);
        });
    }
};

