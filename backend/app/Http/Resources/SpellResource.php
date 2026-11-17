<?php

namespace App\Http\Resources;

use App\Models\ruleset\Spell;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Spell */
class SpellResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'spell_level' => $this->spell_level,
            'school' => $this->school,
            'casting_time' => $this->casting_time,
            'range' => $this->range,
            'duration' => $this->duration,
            'concentration' => $this->concentration,
            'ritual' => $this->ritual,
            'description' => $this->description,
        ];
    }
}
