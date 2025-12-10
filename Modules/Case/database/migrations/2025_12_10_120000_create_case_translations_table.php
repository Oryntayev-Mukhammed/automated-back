<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('case_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            $table->string('lang', 5)->index();
            $table->string('slug')->index();
            $table->string('property_title');
            $table->string('property_price')->nullable();
            $table->string('location')->nullable();
            $table->text('livingArea')->nullable();
            $table->string('tag')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
            $table->unique(['case_id', 'lang']);
            $table->unique(['slug', 'lang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_translations');
    }
};
