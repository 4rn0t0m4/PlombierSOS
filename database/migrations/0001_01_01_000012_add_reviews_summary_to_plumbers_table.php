<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plumbers', function (Blueprint $table) {
            $table->text('reviews_summary')->nullable()->after('google_reviews');
        });
    }

    public function down(): void
    {
        Schema::table('plumbers', function (Blueprint $table) {
            $table->dropColumn('reviews_summary');
        });
    }
};
