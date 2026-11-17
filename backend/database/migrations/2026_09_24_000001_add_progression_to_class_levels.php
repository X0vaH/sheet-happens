<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_levels', function (Blueprint $table) {
            $table->json('progression')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('class_levels', fn (Blueprint $table) => $table->dropColumn('progression'));
    }
};
