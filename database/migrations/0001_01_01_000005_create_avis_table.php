<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plombier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('pseudo_auteur')->nullable();
            $table->string('email_auteur')->nullable();
            $table->string('token_validation', 64)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('titre');
            $table->text('contenu');
            $table->string('ip', 45)->nullable();
            $table->boolean('valide')->default(false);
            $table->boolean('refus')->default(false);
            $table->text('reponse')->nullable();
            $table->timestamp('reponse_date')->nullable();

            // Notes 1-5
            $table->unsignedTinyInteger('note_ponctualite');
            $table->unsignedTinyInteger('note_qualite');
            $table->unsignedTinyInteger('note_prix');
            $table->unsignedTinyInteger('note_proprete');
            $table->unsignedTinyInteger('note_conseil');

            // Type d'intervention
            $table->string('type_intervention')->nullable(); // dépannage, installation, entretien

            $table->timestamps();

            $table->index(['plombier_id', 'valide', 'refus']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avis');
    }
};
