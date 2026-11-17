<?php

namespace Tests\Concerns;

use App\Models\Character;
use App\Models\CharacterAbility;
use App\Models\CharacterAbilityIncrease;
use App\Models\CharacterClass;
use App\Models\CharacterFeature;
use App\Models\CharacterFeatureChoice;
use App\Models\CharacterInventory;
use App\Models\CharacterLevel;
use App\Models\FeatureOption;
use App\Models\ruleset\Ability;
use App\Models\ruleset\Armor;
use App\Models\ruleset\CharacterClass as ClassDefinition;
use App\Models\ruleset\Feature;
use App\Models\ruleset\Level;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Weapon;
use App\Models\User;
use App\Services\CharacterStats;
use Database\Factories\ruleset\ArmorFactory;
use Database\Factories\ruleset\WeaponFactory;

/**
 * Construit un personnage complet (caractéristiques, niveau, classe, équipement) pour tester
 * les statistiques calculées.
 */
trait CreatesCharacterWithStats
{
    private const ABILITIES = [
        'Force' => 'FOR',
        'Dextérité' => 'DEX',
        'Constitution' => 'CON',
        'Intelligence' => 'INT',
        'Sagesse' => 'SAG',
        'Charisme' => 'CHA',
    ];

    /**
     * @param  array<string, int>  $scores  score par nom de caractéristique (10 par défaut)
     */
    protected function characterWithStats(
        array $scores = [],
        int $level = 1,
        string $className = 'Guerrier',
        ?User $user = null,
    ): Character {
        $ruleset = Ruleset::factory()->create();

        foreach (range(1, Level::MAX_LEVEL) as $number) {
            Level::factory()->for($ruleset)->create([
                'level_number' => $number,
                'proficiency_bonus' => 2 + intdiv($number - 1, 4),
            ]);
        }

        $character = Character::factory()
            ->for($user ?? User::factory()->create())
            ->for($ruleset)
            ->create();

        foreach (self::ABILITIES as $name => $abbreviation) {
            $ability = Ability::factory()->for($ruleset)->create(['name' => $name, 'abbreviation' => $abbreviation]);
            CharacterAbility::factory()->for($character)->create([
                'ability_id' => $ability->id,
                'score' => $scores[$name] ?? 10,
            ]);
        }

        CharacterLevel::factory()->for($character)->create(['character_level' => $level]);

        $class = ClassDefinition::factory()->for($ruleset)->create(['name' => $className]);
        CharacterClass::factory()->for($character)->create(['class_id' => $class->id, 'subclass_id' => null]);

        return $character;
    }

    protected function addAbilityIncrease(Character $character, string $abilityName, int $amount): void
    {
        $ability = Ability::query()
            ->where('ruleset_id', $character->ruleset_id)
            ->where('name', $abilityName)
            ->firstOrFail();

        CharacterAbilityIncrease::factory()->for($character)->create([
            'ability_id' => $ability->id,
            'increase_amount' => $amount,
            'acquired_at_character_level' => 4,
        ]);
    }

    /** Ajoute une armure du catalogue (voir ArmorFactory::catalog) à l'inventaire. */
    protected function giveArmor(Character $character, string $name): Armor
    {
        $armor = Armor::factory()->create(ArmorFactory::attributesForArmor($name, $character->ruleset));
        CharacterInventory::factory()->for($character)->create(['item_id' => $armor->item_id, 'quantity' => 1]);

        return $armor;
    }

    /** Ajoute une arme du catalogue (voir WeaponFactory::catalog) à l'inventaire. */
    protected function giveWeapon(Character $character, string $name): Weapon
    {
        $weapon = Weapon::factory()->create(WeaponFactory::attributesForWeapon($name, $character->ruleset));
        CharacterInventory::factory()->for($character)->create(['item_id' => $weapon->item_id, 'quantity' => 1]);

        return $weapon;
    }

    protected function chooseFightingStyle(Character $character, string $styleName): void
    {
        $feature = Feature::factory()->for($character->ruleset)->create(['name' => CharacterStats::FIGHTING_STYLE_FEATURE]);
        $option = FeatureOption::factory()->for($feature)->create(['name' => $styleName]);
        $characterFeature = CharacterFeature::factory()->for($character)->create(['feature_id' => $feature->id]);
        CharacterFeatureChoice::factory()->create([
            'character_feature_id' => $characterFeature->id,
            'feature_option_id' => $option->id,
        ]);
    }

    /** Charge les relations nécessaires au service, comme le fait CharacterController::show. */
    protected function statsFor(Character $character): CharacterStats
    {
        $character->load([
            'abilities.ability',
            'abilityIncreases',
            'level',
            'classes.classDefinition',
            'inventory.item.armor',
            'features.feature',
            'features.choices.featureOption',
        ]);

        return new CharacterStats($character);
    }
}
