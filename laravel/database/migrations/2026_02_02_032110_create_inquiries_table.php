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
            $table->date(Inquiry::start_date);
            $table->date(Inquiry::end_date);
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
