<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('subtitle')->nullable()->after('title');
            $table->string('category')->nullable()->after('subtitle');
            $table->string('series')->nullable()->after('category');
            $table->json('content_blocks')->nullable()->after('content');
            $table->string('language')->default('en')->after('content_blocks');
            $table->boolean('is_featured')->default(false)->after('language');
            $table->string('canonical_url')->nullable()->after('meta_description');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'subtitle',
                'category',
                'series',
                'content_blocks',
                'language',
                'is_featured',
                'canonical_url',
            ]);
        });
    }
};
