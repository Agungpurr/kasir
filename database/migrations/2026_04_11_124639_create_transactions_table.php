<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
     Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('total_price', 12, 2)->default(0);
            $table->decimal('total_payment', 12, 2)->default(0);
            $table->decimal('change', 12, 2)->default(0);
            $table->timestamps();
        });
}

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};