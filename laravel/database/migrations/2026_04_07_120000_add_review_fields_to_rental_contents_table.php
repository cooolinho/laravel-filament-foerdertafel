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
        Schema::table('rental_contents', function (Blueprint $table) {
            $table->boolean('needs_review')->default(false)->after('is_published');
            $table->timestamp('review_requested_at')->nullable()->after('needs_review');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_contents', function (Blueprint $table) {
            $table->dropColumn(['needs_review', 'review_requested_at']);
        });
    }
};
