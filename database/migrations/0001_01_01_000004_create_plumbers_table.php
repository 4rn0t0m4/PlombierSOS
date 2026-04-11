<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plumbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('type')->default(0);
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('place_id')->nullable()->unique();

            // Contact
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile_phone', 20)->nullable();
            $table->string('website')->nullable();
            $table->string('google_maps_url')->nullable();

            // Address
            $table->string('address')->nullable();
            $table->string('postal_code', 5)->nullable();
            $table->string('city')->nullable();
            $table->string('department', 3)->nullable();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('service_radius')->default(20);

            // Content
            $table->text('description')->nullable();
            $table->text('opening_hours')->nullable();
            $table->text('pricing')->nullable();
            $table->string('siret', 14)->nullable();
            $table->string('photo')->nullable();

            // Emergency
            $table->boolean('emergency_24h')->default(false);
            $table->boolean('free_quote')->default(false);
            $table->boolean('rge_certified')->default(false);

            // Specialties (JSON)
            $table->json('specialties')->nullable();

            // Ratings
            $table->decimal('average_rating', 3, 1)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->decimal('google_rating', 2, 1)->nullable();
            $table->unsignedInteger('google_reviews_count')->default(0);

            // Admin
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('city_ranking')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'type']);
            $table->index(['is_active', 'city_id']);
            $table->index(['latitude', 'longitude']);
            $table->index(['is_active', 'emergency_24h']);
        });

        // Pivot plumber <-> user (owners)
        Schema::create('plumber_user', function (Blueprint $table) {
            $table->foreignId('plumber_id')->constrained('plumbers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['plumber_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plumber_user');
        Schema::dropIfExists('plumbers');
    }
};
