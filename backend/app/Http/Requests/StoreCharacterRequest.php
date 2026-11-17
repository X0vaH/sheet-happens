<?php

namespace App\Http\Requests;

use App\Actions\CreateCharacterAction;
use App\Exceptions\CharacterLimitReachedException;
use App\Models\Character;
use App\Models\ruleset\ClassLevel;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\StartingEquipmentOption;
use Database\Seeders\ruleset\RulesetSeeder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCharacterRequest extends FormRequest
{
    /**
     * L'authentification se fait au niveau de la route (middleware auth:sanctum). Ici on refuse
     * la création si la limite de personnages est atteinte, avant la validation : un utilisateur
     * à la limite reçoit toujours un 403, jamais un 422, donc un 422 signifie qu'il reste de la
     * place. Ce contrôle est répété sous verrou dans CreateCharacterAction, qui reste la garantie
     * contre les créations concurrentes.
     *
     * @throws CharacterLimitReachedException
     */
    public function authorize(): bool
    {
        if ($this->user()->characters()->count() >= Character::MAX_PER_USER) {
            throw new CharacterLimitReachedException;
        }

        return true;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'alignment.exists' => "Cet alignement n'existe pas.",
            'order_index.unique' => 'Cet emplacement est déjà occupé par un autre personnage.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'alignment' => [
                'nullable', 'string', 'max:255',
                Rule::exists('alignments', 'name')->whereIn(
                    'ruleset_id',
                    Ruleset::query()->select('id')
                        ->where('name', RulesetSeeder::NAME)
                        ->where('version', RulesetSeeder::VERSION),
                ),
            ],
            'notes' => ['nullable', 'string'],
            'order_index' => [
                'nullable', 'integer', 'min:0', 'max:'.(Character::MAX_PER_USER - 1),
                Rule::unique('characters', 'order_index')->where('user_id', $this->user()->id),
            ],

            'species_id' => ['required', 'integer', 'exists:species,id'],
            'background_id' => ['required', 'integer', 'exists:backgrounds,id'],
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'subclass_id' => ['nullable', 'integer', 'exists:subclasses,id'],

            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*.ability_id' => ['required', 'integer', 'distinct', 'exists:abilities,id'],
            'abilities.*.score' => ['required', 'integer', 'min:1', 'max:20'],

            'skill_proficiencies' => ['sometimes', 'array'],
            'skill_proficiencies.*.skill_id' => ['required', 'integer', 'distinct', 'exists:skills,id'],
            'skill_proficiencies.*.proficiency_level' => ['required', Rule::in(['none', 'proficient', 'expertise'])],

            'languages' => ['sometimes', 'array'],
            'languages.*.language_id' => ['required', 'integer', 'distinct', 'exists:languages,id'],
            'languages.*.source' => ['required', 'string', 'max:255'],

            'tools' => ['sometimes', 'array'],
            'tools.*.tool_id' => ['required', 'integer', 'distinct', 'exists:tools,id'],
            'tools.*.proficiency_level' => ['required', Rule::in(['none', 'proficient', 'expertise'])],

            'feature_choices' => ['sometimes', 'array'],
            'feature_choices.*.feature_id' => ['required', 'integer', 'exists:features,id'],
            'feature_choices.*.feature_option_id' => ['nullable', 'integer', 'exists:feature_options,id'],

            'spells' => ['sometimes', 'array'],
            'spells.*.spell_id' => ['required', 'integer', 'distinct', 'exists:spells,id'],

            'inventory' => ['sometimes', 'array'],
            'inventory.*.item_id' => ['required', 'integer', 'distinct', 'exists:items,id'],
            'inventory.*.quantity' => ['required', 'integer', 'min:1'],

            'starting_equipment_choices' => ['sometimes', 'array'],
            'starting_equipment_choices.*.starting_equipment_option_id' => ['required', 'integer', 'distinct', 'exists:starting_equipment_options,id'],
        ];
    }

    /**
     * Vérifie que la sous-classe choisie (si fournie) est bien disponible au niveau 1
     * pour la classe choisie — les deux existent indépendamment en base, rien ne garantit
     * autrement qu'elles sont liées.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('subclass_id') || ! $this->filled('class_id')) {
                return;
            }

            $availableAtLevelOne = ClassLevel::query()
                ->where('class_id', $this->integer('class_id'))
                ->whereHas('level', fn ($query) => $query->where('level_number', 1))
                ->whereHas('subclasses', fn ($query) => $query->where('id', $this->integer('subclass_id')))
                ->exists();

            if (! $availableAtLevelOne) {
                $validator->errors()->add(
                    'subclass_id',
                    "Cette sous-classe n'est pas disponible au niveau 1 pour la classe choisie.",
                );
            }
        });

        $validator->after(function (Validator $validator): void {
            $choices = $this->input('starting_equipment_choices', []);

            if ($choices === [] || ! $this->filled('class_id')) {
                return;
            }

            $optionIds = collect($choices)->pluck('starting_equipment_option_id')->filter();
            $options = StartingEquipmentOption::query()->with('group')->whereIn('id', $optionIds)->get()->keyBy('id');

            $seenGroupIds = [];

            foreach ($choices as $index => $choice) {
                $option = $options->get($choice['starting_equipment_option_id'] ?? null);

                if (! $option) {
                    continue;
                }

                if ($option->group->class_id !== $this->integer('class_id')) {
                    $validator->errors()->add(
                        "starting_equipment_choices.{$index}.starting_equipment_option_id",
                        "Cette option d'équipement ne fait pas partie de la classe choisie.",
                    );

                    continue;
                }

                if (in_array($option->starting_equipment_group_id, $seenGroupIds, true)) {
                    $validator->errors()->add(
                        "starting_equipment_choices.{$index}.starting_equipment_option_id",
                        "Un seul choix est permis par groupe d'équipement.",
                    );

                    continue;
                }

                $seenGroupIds[] = $option->starting_equipment_group_id;
            }
        });
    }
}
