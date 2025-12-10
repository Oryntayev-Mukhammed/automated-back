<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            if (!Schema::hasColumn('cases', 'language')) {
                $table->string('language')->default('en')->after('slug');
            }

            try {
                $table->dropUnique('cases_slug_unique');
            } catch (\Throwable $e) {
                // ignore if missing
            }

            $table->unique(['slug', 'language'], 'cases_slug_language_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            try {
                $table->dropUnique('cases_slug_language_unique');
            } catch (\Throwable $e) {
                //
            }
            $table->unique('slug');

            try {
                $table->dropColumn('language');
            } catch (\Throwable $e) {
                //
            }
        });
    }
};
