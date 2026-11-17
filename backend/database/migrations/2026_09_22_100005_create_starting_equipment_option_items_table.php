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
        Schema::create('starting_equipment_option_items', function (Blueprint $table) {
            // Nom de contrainte raccourci explicitement : le nom auto-généré par Laravel
            // (starting_equipment_option_items_starting_equipment_option_id_foreign, 70
            // caractères) dépasse la limite MySQL de 64 caractères pour un identifiant.
            $table->foreignId('starting_equipment_option_id')
                ->constrained('starting_equipment_options', indexName: 'seq_option_items_option_id_foreign')
                ->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->primary(['starting_equipment_option_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starting_equipment_option_items');
    }
};
