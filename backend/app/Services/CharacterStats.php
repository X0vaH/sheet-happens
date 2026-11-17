<?php

namespace App\Services;

use App\Models\Character;
use App\Models\ruleset\Armor;
use App\Models\ruleset\Level;
use App\Models\ruleset\Weapon;
use App\Support\AbilityScore;

/**
 * Statistiques dérivées d'un personnage (classe d'armure, bonus, initiative…), calculées
 * à partir des données stockées. Les relations suivantes doivent être chargées :
 * `abilities.ability`, `abilityIncreases`, `level`, `classes.classDefinition`,
 * `inventory.item.armor`, `features.feature` et `features.choices.featureOption`.
 */
class CharacterStats
{
    /** Nom de la capacité dont l'option choisie est le style de combat (voir FeatureSeeder). */
    public const FIGHTING_STYLE_FEATURE = 'Style de combat';

    /** Classe d'armure de base sans armure. */
    private const UNARMORED_BASE = 10;

    /** Style de combat qui donne +1 à la CA lorsque le personnage porte une armure. */
    private const DEFENSE_FIGHTING_STYLE = 'Défense';

    /** Caractéristiques ajoutées à la CA sans armure (Défense sans armure), par classe. */
    private const UNARMORED_ABILITIES = [
        'Barbare' => ['Constitution'],
    ];

    /** Multiplicateur du bonus de maîtrise selon le niveau de maîtrise d'une compétence. */
    private const PROFICIENCY_MULTIPLIERS = [
        'none' => 0,
        'proficient' => 1,
        'expertise' => 2,
    ];

    private ?int $proficiencyBonus = null;

    public function __construct(private readonly Character $character) {}

    public function currentLevel(): int
    {
        return (int) ($this->character->level?->character_level ?? 1);
    }

    /** Prochain niveau du personnage, ou null s'il a atteint le niveau maximum. */
    public function nextLevel(): ?int
    {
        $level = $this->currentLevel();

        return $level < Level::MAX_LEVEL ? $level + 1 : null;
    }

    /** Score de base plus les augmentations acquises, pour la caractéristique donnée. */
    public function effectiveScore(int $abilityId): int
    {
        $score = (int) $this->character->abilities->firstWhere('ability_id', $abilityId)?->score;
        $increases = (int) $this->character->abilityIncreases->where('ability_id', $abilityId)->sum('increase_amount');

        return $score + $increases;
    }

    public function modifier(string $abilityName): int
    {
        $ability = $this->character->abilities->first(fn ($entry) => $entry->ability->name === $abilityName);

        return $ability ? AbilityScore::modifier($this->effectiveScore($ability->ability_id)) : 0;
    }

    public function proficiencyBonus(): int
    {
        return $this->proficiencyBonus ??= (int) Level::query()
            ->where('ruleset_id', $this->character->ruleset_id)
            ->where('level_number', $this->currentLevel())
            ->value('proficiency_bonus');
    }

    public function initiative(): int
    {
        return $this->modifier('Dextérité');
    }

    public function skillBonus(string $abilityName, string $proficiencyLevel): int
    {
        $multiplier = self::PROFICIENCY_MULTIPLIERS[$proficiencyLevel] ?? 0;

        return $this->modifier($abilityName) + $multiplier * $this->proficiencyBonus();
    }

    public function savingThrowBonus(string $abilityName, bool $proficient): int
    {
        return $this->modifier($abilityName) + ($proficient ? $this->proficiencyBonus() : 0);
    }

    /**
     * Bonus d'attaque d'une arme : Force, Dextérité pour une arme à munitions, le meilleur des deux
     * pour une arme de finesse ; plus la maîtrise (toutes les armes sont considérées maîtrisées).
     */
    public function weaponAttackBonus(Weapon $weapon): int
    {
        $properties = $weapon->properties ?? [];
        $strength = $this->modifier('Force');
        $dexterity = $this->modifier('Dextérité');

        $modifier = match (true) {
            in_array('finesse', $properties, true) => max($strength, $dexterity),
            in_array('ammunition', $properties, true) => $dexterity,
            default => $strength,
        };

        return $modifier + $this->proficiencyBonus();
    }

    /**
     * Classe d'armure : la meilleure armure de l'inventaire (toute armure est considérée portée),
     * sinon la CA sans armure ; plus les boucliers.
     */
    public function armorClass(): int
    {
        $armors = $this->character->inventory
            ->map(fn ($entry) => $entry->item?->armor)
            ->filter();

        $shieldBonus = (int) $armors->where('armor_category', 'shield')->sum('armor_class');
        $bodyArmors = $armors->where('armor_category', '!=', 'shield');

        $base = $bodyArmors->isEmpty()
            ? $this->unarmoredArmorClass()
            : $bodyArmors->map(fn (Armor $armor) => $this->armoredArmorClass($armor))->max();

        return $base + $shieldBonus;
    }

    /**
     * Style de combat choisi par le personnage (option de la capacité « Style de combat »).
     *
     * @return array{name: string, description: ?string}|null
     */
    public function fightingStyle(): ?array
    {
        $option = $this->character->features
            ->firstWhere('feature.name', self::FIGHTING_STYLE_FEATURE)
            ?->choices
            ->first()
            ?->featureOption;

        return $option ? ['name' => $option->name, 'description' => $option->description] : null;
    }

    private function armoredArmorClass(Armor $armor): int
    {
        $dexterity = $this->modifier('Dextérité');
        $limit = $armor->dexterity_bonus_limit;
        $dexterityBonus = match (true) {
            $limit === null => $dexterity,
            $limit === 0 => 0,
            default => min($dexterity, $limit),
        };
        $defenseBonus = ($this->fightingStyle()['name'] ?? null) === self::DEFENSE_FIGHTING_STYLE ? 1 : 0;

        return $armor->armor_class + $dexterityBonus + $defenseBonus;
    }

    private function unarmoredArmorClass(): int
    {
        $className = $this->character->classes->first()?->classDefinition?->name;
        $extraAbilities = self::UNARMORED_ABILITIES[$className] ?? [];

        return self::UNARMORED_BASE
            + $this->modifier('Dextérité')
            + array_sum(array_map(fn (string $ability) => $this->modifier($ability), $extraAbilities));
    }
}
