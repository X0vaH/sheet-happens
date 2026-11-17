<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->unsignedTinyInteger('order_index')->default(0);
        });

        // Les personnages existants reçoivent 0, 1, 2… par utilisateur, dans l'ordre de leur id,
        // pour ne pas tous se retrouver au même emplacement du carrousel.
        $characters = DB::table('characters')->orderBy('user_id')->orderBy('id')->get(['id', 'user_id']);
        foreach ($characters->groupBy('user_id') as $userCharacters) {
            foreach ($userCharacters->values() as $index => $character) {
                DB::table('characters')->where('id', $character->id)->update(['order_index' => $index]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn('order_index');
        });
    }
};
