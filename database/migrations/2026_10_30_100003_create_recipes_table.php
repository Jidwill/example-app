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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            // the user who posted it (one user can have many recipes)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('ingredients');
            $table->text('method');
            $table->unsignedSmallInteger('prep_minutes');
            $table->unsignedTinyInteger('servings');
            // macros are grams per serving, calories get worked out in the Recipe model
            $table->unsignedSmallInteger('protein');
            $table->unsignedSmallInteger('carbs');
            $table->unsignedSmallInteger('fat');
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
