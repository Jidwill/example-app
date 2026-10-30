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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            // unique() makes this one to one: each user can only have one profile.
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('goal'); // bulk, cut or maintain
            $table->unsignedSmallInteger('daily_calories');
            $table->unsignedSmallInteger('daily_protein');
            $table->text('bio')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
