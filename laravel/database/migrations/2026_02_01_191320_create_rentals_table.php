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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId(Rental::customer_id)->constrained()->onDelete('cascade');
            $table->date(Rental::start_date);
            $table->date(Rental::end_date)->nullable();
            $table->decimal(Rental::total_price, 10, 2);
            $table->enum(Rental::status, [Rental::STATUS_ACTIVE, Rental::STATUS_COMPLETED, Rental::STATUS_CANCELLED])->default(Rental::STATUS_ACTIVE);
            $table->text(Rental::notes)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
