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
        Schema::create('armor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->enum('armor_category', ['light', 'medium', 'heavy', 'shield'])->default('light');
            $table->unsignedTinyInteger('armor_class');
            $table->unsignedTinyInteger('dexterity_bonus_limit')->nullable();
            $table->unsignedTinyInteger('strength_requirement')->nullable();
            $table->boolean('stealth_disadvantage')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('armor');
    }
};
