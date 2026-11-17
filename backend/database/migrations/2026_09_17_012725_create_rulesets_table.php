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
        Schema::create('rulesets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('version', 20);
            $table->string('edition', 50);
            $table->text('description');
            $table->enum('status', ['draft', 'active', 'published', 'retired'])->default('active');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('retired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rulesets');
    }
};
