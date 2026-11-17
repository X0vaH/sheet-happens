<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feature_options', function (Blueprint $table) {
            $table->foreignId('tool_id')->nullable()->constrained('tools')->restrictOnDelete();
        });

        // Convertit les anciennes associations textuelles une seule fois.
        $options = DB::table('feature_options')
            ->join('features', 'features.id', '=', 'feature_options.feature_id')
            ->where('features.name', 'Élève de guerre')
            ->select('feature_options.id', 'feature_options.name', 'features.ruleset_id')->get();
        foreach ($options as $option) {
            $toolId = DB::table('tools')->where('ruleset_id', $option->ruleset_id)->where('name', $option->name)->value('id');
            DB::table('feature_options')->where('id', $option->id)->update(['tool_id' => $toolId]);
        }
    }

    public function down(): void
    {
        Schema::table('feature_options', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tool_id');
        });
    }
};
