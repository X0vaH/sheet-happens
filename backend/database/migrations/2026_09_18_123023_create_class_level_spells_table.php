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
        Schema::create('class_level_spells', function (Blueprint $table) {
            $table->foreignId('class_level_id')->constrained('class_levels')->cascadeOnDelete();
            $table->foreignId('spell_id')->constrained('spells')->cascadeOnDelete();
            $table->primary(['class_level_id', 'spell_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_level_spells');
    }
};
