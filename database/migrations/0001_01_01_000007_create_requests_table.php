<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Intervention requests / matching
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plumber_id')->nullable()->constrained('plumbers')->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20);
            $table->string('postal_code', 5);
            $table->string('city')->nullable();
            $table->text('description');
            $table->enum('urgency', ['normal', 'urgent', 'very_urgent'])->default('normal');
            $table->enum('type', ['repair', 'installation', 'maintenance', 'quote'])->default('repair');
            $table->enum('status', ['new', 'sent', 'accepted', 'refused', 'completed'])->default('new');
            $table->timestamps();

            $table->index(['postal_code', 'urgency']);
            $table->index('status');
        });

        // Direct contact messages
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plumber_id')->constrained('plumbers')->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('content');
            $table->timestamps();
        });

        // Google Places imports tracking
        Schema::create('google_imports', function (Blueprint $table) {
            $table->id();
            $table->string('place_id')->unique();
            $table->foreignId('plumber_id')->nullable()->constrained('plumbers')->nullOnDelete();
            $table->enum('status', ['imported', 'ignored', 'duplicate'])->default('imported');
            $table->timestamps();
        });

        Schema::create('google_import_progress', function (Blueprint $table) {
            $table->string('department', 3)->primary();
            $table->boolean('completed')->default(false);
            $table->unsignedInteger('total_imported')->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_import_progress');
        Schema::dropIfExists('google_imports');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('requests');
    }
};
