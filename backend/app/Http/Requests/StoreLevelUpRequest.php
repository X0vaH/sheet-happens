<?php

namespace App\Http\Requests;

use App\Models\Character;
use App\Models\ruleset\Level;
use Illuminate\Foundation\Http\FormRequest;

class StoreLevelUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        $character = $this->route('character');

        return $character instanceof Character && $this->user()?->id === $character->user_id;
    }

    public function rules(): array
    {
        return [
            'target_level' => ['required', 'integer', 'between:2,'.Level::MAX_LEVEL],
            'hit_point_method' => ['sometimes', 'in:fixed'],
            'hit_point_roll' => ['prohibited'],
            'feat_id' => ['nullable', 'integer'],
            'feature_choices' => ['present', 'array'],
            'feature_choices.*.feature_id' => ['required', 'integer', 'distinct'],
            'feature_choices.*.option_ids' => ['required', 'array', 'min:1'],
            'feature_choices.*.option_ids.*' => ['required', 'integer', 'distinct'],
            'ability_increases' => ['present', 'array'],
            'ability_increases.*.ability_id' => ['required', 'integer', 'distinct'],
            'ability_increases.*.increase_amount' => ['required', 'integer', 'in:1,2'],
            'subclass_id' => ['nullable', 'integer'],
            'spell_ids' => ['present', 'array'],
            'spell_ids.*' => ['required', 'integer', 'distinct'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(404);
    }

    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'present' => 'Le champ :attribute doit être fourni.',
            'integer' => 'Le champ :attribute doit être un entier.',
            'array' => 'Le champ :attribute doit être une liste.',
            'distinct' => 'Les choix doivent être différents.',
            'in' => 'Le choix fourni pour :attribute est invalide.',
            'between' => 'Le champ :attribute doit être compris entre :min et :max.',
            'min' => 'Au moins un choix est requis pour :attribute.',
            'prohibited' => 'Le lancer de dés ne fait pas partie de la montée de niveau.',
        ];
    }
}
