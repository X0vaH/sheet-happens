<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Une option = une alternative au sein d'un groupe (ex : "Cotte de mailles" OU "Armure
     * de cuir + arc long"). Elle liste ses objets via la table pivot
     * starting_equipment_option_items.
     */
    public function up(): void
    {
        Schema::create('starting_equipment_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('starting_equipment_group_id')->constrained('starting_equipment_groups')->cascadeOnDelete();
            // Texte d'affichage prêt pour le frontend (ex : "Cotte de mailles"), et sert aussi
            // de clé naturelle pour le seeding idempotent (avec starting_equipment_group_id).
            $table->string('label');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starting_equipment_options');
    }
};
