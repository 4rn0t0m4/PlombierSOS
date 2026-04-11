<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plombiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('type')->default(0); // 0=plombier, 1=chauffagiste, 2=plombier-chauffagiste, 3=dépanneur urgence
            $table->string('titre');
            $table->string('slug')->unique();
            $table->string('place_id')->nullable()->unique();

            // Contact
            $table->string('email')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('portable', 20)->nullable();
            $table->string('site_web')->nullable();
            $table->string('google_maps_url')->nullable();

            // Adresse
            $table->string('adresse')->nullable();
            $table->string('cp', 5)->nullable();
            $table->string('ville')->nullable();
            $table->string('dept', 3)->nullable();
            $table->foreignId('ville_id')->nullable()->constrained('villes')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('rayon_intervention')->default(20); // km

            // Contenu
            $table->text('description')->nullable();
            $table->text('horaires')->nullable();
            $table->text('tarifs')->nullable();
            $table->string('siret', 14)->nullable();
            $table->string('photo')->nullable();

            // Urgence
            $table->boolean('urgence_24h')->default(false);
            $table->boolean('devis_gratuit')->default(false);
            $table->boolean('agree_rge')->default(false);

            // Spécialités (JSON)
            $table->json('specialites')->nullable();

            // Notes
            $table->decimal('moyenne', 3, 1)->default(0);
            $table->unsignedInteger('nb_avis')->default(0);
            $table->decimal('google_rating', 2, 1)->nullable();
            $table->unsignedInteger('google_nb_avis')->default(0);

            // Admin
            $table->boolean('valide')->default(false);
            $table->unsignedInteger('classement_ville')->default(0);
            $table->timestamps();

            $table->index(['valide', 'type']);
            $table->index(['valide', 'ville_id']);
            $table->index(['latitude', 'longitude']);
            $table->index(['valide', 'urgence_24h']);
        });

        // Pivot plombier <-> user (propriétaires)
        Schema::create('plombier_user', function (Blueprint $table) {
            $table->foreignId('plombier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['plombier_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plombier_user');
        Schema::dropIfExists('plombiers');
    }
};
