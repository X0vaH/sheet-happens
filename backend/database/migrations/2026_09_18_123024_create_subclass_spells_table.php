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
        Schema::create('subclass_spells', function (Blueprint $table) {
            $table->foreignId('subclass_id')->constrained('subclasses')->cascadeOnDelete();
            $table->foreignId('spell_id')->constrained('spells')->cascadeOnDelete();
            $table->primary(['subclass_id', 'spell_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subclass_spells');
    }
};
