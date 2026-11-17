<?php

namespace Database\Factories\ruleset;

use App\Models\ruleset\Language;
use App\Models\ruleset\Ruleset;
use Database\Seeders\ruleset\LanguageSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Language>
 */
class LanguageFactory extends Factory
{
    protected $model = Language::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(array_keys(LanguageSeeder::DEFINITIONS));

        return [
            'ruleset_id' => Ruleset::query()->first()?->id ?? Ruleset::factory()->create()->id,
            'name' => $name,
            'description' => LanguageSeeder::DEFINITIONS[$name],
        ];
    }
}
