<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_hours', function (Blueprint $table) {
            $table->id();
            $table->string('day_of_week');
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($days as $day) {
            DB::table('business_hours')->insert([
                'day_of_week' => $day,
                'open_time' => in_array($day, ['saturday', 'sunday']) ? null : '09:00',
                'close_time' => in_array($day, ['saturday', 'sunday']) ? null : '22:00',
                'is_closed' => in_array($day, ['saturday', 'sunday']),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('business_hours');
    }
};
