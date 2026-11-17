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
        Schema::create('rule_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruleset_id')->constrained('rulesets')->cascadeOnDelete();
            $table->string('name');
            $table->enum('source_type', ['book', 'website', 'homebrew', 'official', 'pdf'])->default('book');
            $table->date('publication_date');
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rule_sources');
    }
};
