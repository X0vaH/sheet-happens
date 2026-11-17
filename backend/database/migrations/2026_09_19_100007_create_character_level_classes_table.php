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
        Schema::create('character_level_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('character_level_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('resulting_class_level');
            $table->timestamps();

            $table->unique(['character_class_id', 'character_level_id'], 'character_level_classes_class_level_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_level_classes');
    }
};
