<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->string('title')->nullable()->after('property_title');
            $table->string('cover_image')->nullable()->after('property_img');
            $table->string('domain')->nullable()->after('property_price');
            $table->text('summary')->nullable()->after('livingArea');
        });

        Schema::table('case_translations', function (Blueprint $table) {
            $table->string('title')->nullable()->after('property_title');
            $table->string('domain')->nullable()->after('property_price');
            $table->text('summary')->nullable()->after('livingArea');
            $table->string('cover_image')->nullable()->after('domain');
        });
    }

    public function down(): void
    {
        Schema::table('case_translations', function (Blueprint $table) {
            $table->dropColumn(['title', 'domain', 'summary', 'cover_image']);
        });
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn(['title', 'cover_image', 'domain', 'summary']);
        });
    }
};
