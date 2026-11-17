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
        Schema::table('classes', function (Blueprint $table) {
            $table->unsignedTinyInteger('skill_choice_count')->default(0)->after('hit_die');
            $table->unsignedTinyInteger('cantrip_choice_count')->default(0)->after('skill_choice_count');
            $table->unsignedTinyInteger('spell_choice_count')->default(0)->after('cantrip_choice_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn(['skill_choice_count', 'cantrip_choice_count', 'spell_choice_count']);
        });
    }
};
