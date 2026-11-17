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
        Schema::create('spells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruleset_id')->constrained('rulesets')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('spell_level');
            $table->enum('school', [
                'abjuration',
                'conjuration',
                'divination',
                'enchantment',
                'evocation',
                'illusion',
                'necromancy',
                'transmutation',
            ]);
            $table->string('casting_time');
            $table->string('range');
            $table->string('duration');
            $table->boolean('concentration')->default(false);
            $table->boolean('ritual')->default(false);
            $table->boolean('verbal')->default(false);
            $table->boolean('somatic')->default(false);
            $table->boolean('material')->default(false);
            $table->string('material_description')->nullable();
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spells');
    }
};
