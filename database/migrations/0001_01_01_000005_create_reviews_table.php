<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plumber_id')->constrained('plumbers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('author_username')->nullable();
            $table->string('author_email')->nullable();
            $table->string('validation_token', 64)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('title');
            $table->text('content');
            $table->string('ip', 45)->nullable();
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_rejected')->default(false);
            $table->text('response')->nullable();
            $table->timestamp('response_date')->nullable();

            // Ratings 1-5
            $table->unsignedTinyInteger('punctuality_rating');
            $table->unsignedTinyInteger('quality_rating');
            $table->unsignedTinyInteger('price_rating');
            $table->unsignedTinyInteger('cleanliness_rating');
            $table->unsignedTinyInteger('advice_rating');

            // Intervention type
            $table->string('intervention_type')->nullable();

            $table->timestamps();

            $table->index(['plumber_id', 'is_approved', 'is_rejected']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
