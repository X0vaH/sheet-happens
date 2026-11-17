<?php

namespace App\Http\Controllers;

use App\Actions\ApplyLevelUpAction;
use App\Http\Requests\StoreLevelUpRequest;
use App\Http\Resources\LevelUpOptionsResource;
use App\Models\Character;
use Illuminate\Http\JsonResponse;

class LevelUpController extends Controller
{
    public function options(Character $character): JsonResponse
    {
        if ($character->user_id !== auth()->id()) {
            abort(404);
        }

        return response()->json(new LevelUpOptionsResource($character));
    }

    public function apply(Character $character, StoreLevelUpRequest $request, ApplyLevelUpAction $action): JsonResponse
    {
        $action->execute($character, $request->validated());

        return response()->json([
            'message' => 'Montée de niveau appliquée avec succès',
        ]);
    }
}
