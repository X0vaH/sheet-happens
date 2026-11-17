<?php

use App\Models\Character;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$request = Request::create('/api/characters/'.$argv[1].'/level-up', 'POST', server: [
    'HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json',
], content: json_encode([
    'target_level' => 2, 'feature_choices' => [], 'ability_increases' => [], 'spell_ids' => [],
], JSON_THROW_ON_ERROR));
$app->instance('request', $request);
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
$hero = Character::findOrFail($argv[1]);
Sanctum::actingAs($hero->user);
DB::connection()->beforeExecuting(function ($query, $bindings, $connection) use ($argv) {
    if ($connection->transactionLevel() > 0 && str_contains($query, 'select') && str_contains($query, '`characters`')) {
        file_put_contents($argv[2], 'ready');
    }
});
$response = $kernel->handle($request);
echo json_encode(['status' => $response->getStatusCode(), 'body' => json_decode($response->getContent(), true)], JSON_THROW_ON_ERROR);
$kernel->terminate($request, $response);
