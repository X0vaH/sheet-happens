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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruleset_id')->constrained('rulesets')->cascadeOnDelete();
            $table->string('name');
            $table->enum('item_type', ['weapon', 'armor', 'gear', 'tool', 'consumable', 'treasure'])->default('gear');
            $table->foreignId('tool_id')->nullable()->constrained('tools')->nullOnDelete();
            $table->text('description');
            $table->float('weight');
            $table->decimal('value', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
