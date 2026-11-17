<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AddOrderIndexToCharactersMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): Migration
    {
        return require database_path('migrations/2026_09_30_000000_add_order_index_to_characters_table.php');
    }

    public function test_la_migration_numerote_les_personnages_existants_par_utilisateur(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $aliceFirst = Character::factory()->for($alice)->create();
        $bobFirst = Character::factory()->for($bob)->create();
        $aliceSecond = Character::factory()->for($alice)->create();
        $aliceThird = Character::factory()->for($alice)->create();

        $migration = $this->migration();
        $migration->down();
        $this->assertFalse(Schema::hasColumn('characters', 'order_index'));
        $migration->up();

        $orderIndexes = DB::table('characters')->pluck('order_index', 'id');
        $this->assertSame(0, (int) $orderIndexes[$aliceFirst->id]);
        $this->assertSame(1, (int) $orderIndexes[$aliceSecond->id]);
        $this->assertSame(2, (int) $orderIndexes[$aliceThird->id]);
        $this->assertSame(0, (int) $orderIndexes[$bobFirst->id]);
    }

    public function test_un_nouveau_personnage_a_zero_par_defaut(): void
    {
        $character = Character::factory()->create();

        $this->assertSame(0, (int) DB::table('characters')->where('id', $character->id)->value('order_index'));
    }
}
