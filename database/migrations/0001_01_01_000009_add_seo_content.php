<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plumbers', function (Blueprint $table) {
            $table->text('seo_content')->nullable()->after('description');
        });
        Schema::table('departments', function (Blueprint $table) {
            $table->text('seo_content')->nullable()->after('article');
        });
        Schema::table('cities', function (Blueprint $table) {
            $table->text('seo_content')->nullable()->after('population');
        });
    }

    public function down(): void
    {
        Schema::table('plumbers', fn (Blueprint $t) => $t->dropColumn('seo_content'));
        Schema::table('departments', fn (Blueprint $t) => $t->dropColumn('seo_content'));
        Schema::table('cities', fn (Blueprint $t) => $t->dropColumn('seo_content'));
    }
};
