<?php

use App\Models\Customer;
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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string(Customer::name);
            $table->string(Customer::company_name)->nullable();
            $table->string(Customer::email)->unique();
            $table->string(Customer::phone)->nullable();
            $table->text(Customer::address)->nullable();
            $table->string(Customer::payment_method)->nullable();
            $table->text(Customer::notes)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
