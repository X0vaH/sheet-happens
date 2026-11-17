<?php

namespace Tests\Integration;

use App\Models\Character;
use App\Models\ruleset\CharacterClass;
use Database\Seeders\ruleset\RulesetDatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class LevelUpConcurrencyTest extends TestCase
{
    public function test_simultaneous_requests_apply_the_level_and_hit_points_only_once(): void
    {
        // Une base aléatoire dédiée est créée puis supprimée, jamais la base .env.
        $database = 'level_up_test_'.bin2hex(random_bytes(8));
        $connection = [
            'driver' => 'mysql', 'host' => getenv('TEST_MYSQL_HOST') ?: '127.0.0.1',
            'port' => getenv('TEST_MYSQL_PORT') ?: '3306',
            'username' => getenv('TEST_MYSQL_USER') ?: 'root',
            'password' => getenv('TEST_MYSQL_PASSWORD') ?: '',
            'database' => null, 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci',
        ];
        config(['database.connections.concurrency_admin' => $connection]);
        $admin = DB::connection('concurrency_admin');
        $admin->statement("CREATE DATABASE `$database`");
        config(['database.default' => 'concurrency', 'database.connections.concurrency' => [...$connection, 'database' => $database]]);
        $processes = [];
        $signals = [];
        try {
            $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
            $this->seed(RulesetDatabaseSeeder::class);
            $class = CharacterClass::where('name', 'Guerrier')->firstOrFail();
            $hero = Character::factory()->create(['ruleset_id' => $class->ruleset_id, 'max_health_points' => 10, 'current_health_points' => 8]);
            $hero->classes()->create(['class_id' => $class->id, 'first_taken_at_character_level' => 1]);
            $hero->levels()->create(['character_level' => 1, 'experience_points' => 0]);
            foreach ($class->ruleset->abilities as $ability) {
                $hero->abilities()->create(['ability_id' => $ability->id, 'score' => 14]);
            }

            DB::beginTransaction();
            Character::whereKey($hero->id)->lockForUpdate()->firstOrFail();
            for ($i = 0; $i < 2; $i++) {
                $signal = tempnam(sys_get_temp_dir(), 'level-up-');
                $signals[] = $signal;
                $process = new Process([PHP_BINARY, base_path('tests/Support/level-up-request.php'), (string) $hero->id, $signal], base_path(), [
                    'APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_URL' => '',
                    'DB_HOST' => $connection['host'], 'DB_PORT' => $connection['port'],
                    'DB_DATABASE' => $database, 'DB_USERNAME' => $connection['username'],
                    'DB_PASSWORD' => $connection['password'], 'SESSION_DRIVER' => 'array',
                    'CACHE_STORE' => 'array', 'BCRYPT_ROUNDS' => '4',
                ]);
                $process->setTimeout(30);
                $process->start();
                $processes[] = $process;
            }

            $deadline = microtime(true) + 20;
            do {
                $ready = count(array_filter($signals, fn ($path) => file_get_contents($path) === 'ready'));
                if ($ready === 2) {
                    break;
                }
                foreach ($processes as $process) {
                    if (! $process->isRunning()) {
                        $this->fail($process->getErrorOutput().$process->getOutput());
                    }
                }
                usleep(10000);
            } while (microtime(true) < $deadline);
            $this->assertSame(2, $ready, 'Les deux requêtes doivent atteindre la transaction pendant le verrouillage.');
            DB::commit();

            $responses = [];
            foreach ($processes as $process) {
                $process->wait();
                $this->assertTrue($process->isSuccessful(), $process->getErrorOutput().$process->getOutput());
                $responses[] = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            }
            $this->assertEqualsCanonicalizing([200, 422], array_column($responses, 'status'));
            $rejected = collect($responses)->firstWhere('status', 422);
            $this->assertSame(['Rechargez le personnage avant de passer au niveau suivant.'], $rejected['body']['errors']['target_level']);
            $this->assertSame(2, $hero->levels()->count());
            $this->assertSame(1, $hero->levels()->where('character_level', 2)->count());
            $this->assertSame(18, $hero->fresh()->max_health_points);
            $this->assertSame(16, $hero->fresh()->current_health_points);
        } finally {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
            foreach ($signals as $signal) {
                unlink($signal);
            }
            DB::purge('concurrency');
            $admin->statement("DROP DATABASE `$database`");
            DB::purge('concurrency_admin');
        }
    }
}
