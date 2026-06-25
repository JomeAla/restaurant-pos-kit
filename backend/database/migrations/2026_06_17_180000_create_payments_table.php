<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->decimal('amount', 10, 2);
            $table->string('method');
            $table->string('reference')->nullable()->unique();
            $table->string('status')->default('completed');
            $table->text('notes')->nullable();
            $table->decimal('change_due', 10, 2)->default(0);
            $table->decimal('amount_tendered', 10, 2)->nullable();
            $table->foreignId('processed_by')->constrained('users');
            $table->dateTime('paid_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
