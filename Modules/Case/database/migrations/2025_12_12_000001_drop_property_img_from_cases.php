<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            if (Schema::hasColumn('cases', 'property_img')) {
                $table->dropColumn('property_img');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            if (!Schema::hasColumn('cases', 'property_img')) {
                $table->string('property_img')->nullable()->after('title');
            }
        });
    }
};
