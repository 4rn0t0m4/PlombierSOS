<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opening_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plumber_id')->constrained('plumbers')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('morning_open')->nullable();
            $table->time('morning_close')->nullable();
            $table->time('afternoon_open')->nullable();
            $table->time('afternoon_close')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->unique(['plumber_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opening_hours');
    }
};
