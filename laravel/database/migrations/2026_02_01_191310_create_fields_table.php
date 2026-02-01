<?php

use App\Models\Field;
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
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId(Field::board_id)->constrained()->onDelete('cascade');
            $table->string(Field::name);
            $table->integer(Field::row);
            $table->integer(Field::column);
            $table->integer(Field::width)->default(1);
            $table->integer(Field::height)->default(1);
            $table->decimal(Field::price_per_month, 10, 2);
            $table->enum(Field::status, [Field::STATUS_AVAILABLE, Field::STATUS_RENTED, Field::STATUS_RESERVED])->default(Field::STATUS_AVAILABLE);
            $table->text(Field::description)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
