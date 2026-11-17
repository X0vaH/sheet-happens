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
        Schema::create('weapons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->enum('weapon_category', ['simple', 'martial'])->default('simple');
            $table->string('damage_dice', 10);
            $table->enum('damage_type', ['bludgeoning', 'piercing', 'slashing']);
            $table->string('versatile_damage_dice', 10)->nullable();
            $table->unsignedSmallInteger('range_normal')->nullable();
            $table->unsignedSmallInteger('range_long')->nullable();
            $table->json('properties')->nullable();
            $table->enum('mastery_property', [
                'cleave',
                'graze',
                'nick',
                'push',
                'sap',
                'slow',
                'topple',
                'vex',
            ])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weapons');
    }
};
