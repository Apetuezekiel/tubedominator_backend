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
        Schema::create('bookmarked_search_terms', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('email');
            $table->string('keyword');
            $table->string('search_volume');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmarked_search_terms');
    }
};
