<?php

namespace App\Http\Resources;

use App\Services\LevelUpOptions;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LevelUpOptionsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return app(LevelUpOptions::class)->forCharacter($this->resource);
    }
}
