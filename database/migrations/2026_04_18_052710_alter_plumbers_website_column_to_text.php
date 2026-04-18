<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plumbers', function (Blueprint $table) {
            $table->text('website')->nullable()->change();
            $table->text('google_maps_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('plumbers', function (Blueprint $table) {
            $table->string('website')->nullable()->change();
            $table->string('google_maps_url')->nullable()->change();
        });
    }
};
