<?php

use App\Models\RentalContent;
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
        Schema::create('rental_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId(RentalContent::rental_id)->constrained()->onDelete('cascade');
            $table->string(RentalContent::access_code, 14)->unique();
            $table->string(RentalContent::company_logo)->nullable();
            $table->string(RentalContent::title)->nullable();
            $table->text(RentalContent::description)->nullable();
            $table->string(RentalContent::website_url)->nullable();
            $table->string(RentalContent::contact_email)->nullable();
            $table->string(RentalContent::contact_phone)->nullable();
            $table->boolean(RentalContent::is_private_person)->default(false);
            $table->boolean(RentalContent::is_published)->default(false);
            $table->timestamp(RentalContent::last_accessed_at)->nullable();
            $table->timestamps();

            // Index für schnelle Suche nach Zugangscode
            $table->index(RentalContent::access_code);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_contents');
    }
};
