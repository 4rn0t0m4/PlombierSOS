<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Demandes d'intervention / mise en relation
        Schema::create('demandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plombier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nom');
            $table->string('email');
            $table->string('telephone', 20);
            $table->string('cp', 5);
            $table->string('ville')->nullable();
            $table->text('description');
            $table->enum('urgence', ['normale', 'urgente', 'tres_urgente'])->default('normale');
            $table->enum('type', ['depannage', 'installation', 'entretien', 'devis'])->default('depannage');
            $table->enum('statut', ['nouvelle', 'envoyee', 'acceptee', 'refusee', 'terminee'])->default('nouvelle');
            $table->timestamps();

            $table->index(['cp', 'urgence']);
            $table->index('statut');
        });

        // Messages de contact directs
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plombier_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('nom')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->text('contenu');
            $table->timestamps();
        });

        // Imports Google Places tracking
        Schema::create('google_imports', function (Blueprint $table) {
            $table->id();
            $table->string('place_id')->unique();
            $table->foreignId('plombier_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('statut', ['importe', 'ignore', 'doublon'])->default('importe');
            $table->timestamps();
        });

        Schema::create('google_import_progress', function (Blueprint $table) {
            $table->string('departement', 3)->primary();
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
        Schema::dropIfExists('demandes');
    }
};
