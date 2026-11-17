<?php

namespace Database\Factories;

use App\Models\CharacterFeature;
use App\Models\CharacterFeatureChoice;
use App\Models\FeatureOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterFeatureChoice>
 */
class CharacterFeatureChoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'character_feature_id' => CharacterFeature::factory(),
            'feature_option_id' => FeatureOption::factory(),
        ];
    }
}
