<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('google_import_progress', function (Blueprint $table) {
            $table->unsignedInteger('city_offset')->default(0)->after('total_imported');
        });
    }

    public function down(): void
    {
        Schema::table('google_import_progress', function (Blueprint $table) {
            $table->dropColumn('city_offset');
        });
    }
};
