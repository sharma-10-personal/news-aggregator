<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_data')->onDelete('cascade');
            $table->json('preferred_sources')->nullable(); // JSON field to store preferred sources
            $table->json('preferred_categories')->nullable(); // JSON field to store preferred categories
            $table->json('preferred_authors')->nullable(); // JSON field to store preferred authors
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preferences');
    }
};
