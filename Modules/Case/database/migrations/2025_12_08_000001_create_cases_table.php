<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('property_title');
            $table->string('property_img')->nullable();
            $table->string('property_price')->nullable();
            $table->string('category')->nullable();
            $table->string('location')->nullable();
            $table->text('livingArea')->nullable();
            $table->string('tag')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
