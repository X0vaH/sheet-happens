<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Un groupe = un choix d'équipement de départ pour une classe (ex : "armure", "arme
     * principale"). Un groupe avec une seule option n'est pas vraiment un choix : c'est
     * de l'équipement accordé automatiquement, modélisé de la même façon pour ne pas
     * dupliquer un mécanisme séparé.
     */
    public function up(): void
    {
        Schema::create('starting_equipment_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            // Sert aussi de clé naturelle pour le seeding idempotent (avec class_id) : pas
            // de colonne "position", donc rien d'autre ne distingue deux groupes d'une classe.
            $table->string('label');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starting_equipment_groups');
    }
};
