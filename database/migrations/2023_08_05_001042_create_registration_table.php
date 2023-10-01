<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('channel_name');
            $table->string('description');
            $table->string('business_email')->unique();
            $table->boolean('accept_terms');
            $table->string('channel_language');
            $table->string('competitive_channels');
            $table->string('keywords');
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('registrations');
    }

};
