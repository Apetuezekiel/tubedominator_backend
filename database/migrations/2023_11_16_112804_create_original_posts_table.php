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
        if (!Schema::hasTable('original_posts')) {
            Schema::create('original_posts', function (Blueprint $table) {
                $table->id();
                $table->string('user_id');
                $table->string('search_term');
                $table->string('video_title');
                $table->string('video_description');
                $table->string('video_tags');
                $table->string('video_thumbnail');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('original_posts');
    }
};
