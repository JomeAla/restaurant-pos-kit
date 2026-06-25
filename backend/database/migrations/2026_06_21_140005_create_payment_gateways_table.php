<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('gateway')->unique();
            $table->string('label');
            $table->text('credentials')->nullable();
            $table->boolean('is_sandbox')->default(true);
            $table->boolean('is_active')->default(false);
            $table->string('webhook_secret')->nullable();
            $table->string('webhook_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
