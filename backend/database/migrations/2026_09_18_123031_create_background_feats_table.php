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
        Schema::create('background_feats', function (Blueprint $table) {
            $table->foreignId('background_id')->constrained('backgrounds')->cascadeOnDelete();
            $table->foreignId('feat_id')->constrained('feats')->cascadeOnDelete();
            $table->primary(['background_id', 'feat_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('background_feats');
    }
};
