<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Drop old unique index on slug if it exists
            try {
                $table->dropUnique('posts_slug_unique');
            } catch (\Throwable $e) {
                // ignore if not present
            }

            if (!Schema::hasColumn('posts', 'language')) {
                $table->string('language')->default('en')->after('content_blocks');
            }

            $table->unique(['slug', 'language'], 'posts_slug_language_unique');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            try {
                $table->dropUnique('posts_slug_language_unique');
            } catch (\Throwable $e) {
                //
            }
            $table->unique('slug');
        });
    }
};
