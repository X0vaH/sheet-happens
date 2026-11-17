<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\LevelUpController;
use App\Http\Controllers\RulesetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware(['auth:sanctum', 'throttle:global'])->group(function () {
    Route::post('/updateProfile', [AuthController::class, 'updateProfile']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('/characters', [CharacterController::class, 'index']);
    Route::get('/characters/{character}', [CharacterController::class, 'show']);
    Route::get('/rulesets/current/character-creation-options', [RulesetController::class, 'characterCreationOptions']);
    Route::post('/characters', [CharacterController::class, 'store']);
    Route::delete('/characters/{character}', [CharacterController::class, 'destroy']);
    Route::get('/characters/{character}/level-up/options', [LevelUpController::class, 'options']);
    Route::post('/characters/{character}/level-up', [LevelUpController::class, 'apply']);
});
