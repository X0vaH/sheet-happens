<?php

namespace App\Http\Resources;

use App\Models\ruleset\Tool;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Tool */
class ToolResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tool_category' => $this->tool_category,
        ];
    }
}
