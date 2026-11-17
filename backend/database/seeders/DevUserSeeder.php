<?php

namespace Database\Seeders;

use App\Models\Character;
use App\Models\CharacterClass;
use App\Models\FeatureOption;
use App\Models\ruleset\Ability;
use App\Models\ruleset\Background;
use App\Models\ruleset\CharacterClass as ClassDefinition;
use App\Models\ruleset\Feat;
use App\Models\ruleset\Feature;
use App\Models\ruleset\Item;
use App\Models\ruleset\Language;
use App\Models\ruleset\Ruleset;
use App\Models\ruleset\Skill;
use App\Models\ruleset\Species;
use App\Models\ruleset\Spell;
use App\Models\ruleset\Subclass;
use App\Models\ruleset\Tool;
use App\Models\User;
use Database\Seeders\ruleset\RulesetSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Compte de test avec des personnages complets (compétences, langues, outils, capacités,
 * dons, sorts, emplacements de sorts, équipement, augmentations de caractéristiques),
 * pour tester manuellement l'API/le frontend sans devoir créer un compte et des
 * personnages à la main.
 *
 * Les personnages couvrent les cas utiles à la fiche : armure lourde + bouclier + style
 * « Défense » (Aldric), lanceur de sorts avec sous-classe et emplacements utilisés (Elowen),
 * niveau maximum avec augmentation de caractéristique et défense sans armure (Skarr),
 * armure intermédiaire + bouclier (Thalia), expertise et armes de finesse/à distance (Vex).
 *
 * Non appelé par DatabaseSeeder (mot de passe connu) : à lancer explicitement
 * avec `php artisan db:seed --class=DevUserSeeder` (nécessite RulesetDatabaseSeeder).
 * Chaque exécution recrée les personnages du compte de test.
 */
class DevUserSeeder extends Seeder
{
    public const EMAIL = 'test@example.com';

    public const PASSWORD = 'password';

    /** Expérience minimale de chaque niveau (règles 5e). */
    private const EXPERIENCE_BY_LEVEL = [1 => 0, 2 => 300, 3 => 900, 4 => 2700, 5 => 6500];

    /** Emplacements de sorts d'un lanceur complet (Clerc, Magicien), par niveau de personnage. */
    private const SPELL_SLOTS_BY_LEVEL = [1 => [2], 2 => [3], 3 => [4, 2], 4 => [4, 3], 5 => [4, 3, 2]];

    public function run(): void
    {
        $ruleset = Ruleset::query()
            ->where('name', RulesetSeeder::NAME)
            ->where('version', RulesetSeeder::VERSION)
            ->firstOrFail();

        $user = User::query()->updateOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Testeur',
                'password' => Hash::make(self::PASSWORD),
                'email_verified_at' => now(),
            ],
        );

        $catalog = [
            'abilities' => Ability::query()->where('ruleset_id', $ruleset->id)->get()->keyBy('name'),
            'species' => Species::query()->where('ruleset_id', $ruleset->id)->with(['features', 'languages'])->get()->keyBy('name'),
            'backgrounds' => Background::query()->where('ruleset_id', $ruleset->id)->with(['skills', 'tools', 'feats'])->get()->keyBy('name'),
            'classes' => ClassDefinition::query()->where('ruleset_id', $ruleset->id)->get()->keyBy('name'),
            'skills' => Skill::query()->where('ruleset_id', $ruleset->id)->get()->keyBy('name'),
            'languages' => Language::query()->where('ruleset_id', $ruleset->id)->get()->keyBy('name'),
            'tools' => Tool::query()->where('ruleset_id', $ruleset->id)->get()->keyBy('name'),
            'features' => Feature::query()->where('ruleset_id', $ruleset->id)->get()->keyBy('name'),
            'feats' => Feat::query()->where('ruleset_id', $ruleset->id)->get()->keyBy('name'),
            'spells' => Spell::query()->where('ruleset_id', $ruleset->id)->get()->keyBy('name'),
            'items' => Item::query()->where('ruleset_id', $ruleset->id)->get()->keyBy('name'),
        ];

        DB::transaction(function () use ($user, $ruleset, $catalog) {
            // Recrée les personnages à chaque exécution (les tables du personnage sont en cascade).
            $user->characters()->get()->each->delete();

            foreach ($this->characters() as $position => $data) {
                $this->createCharacter($user, $ruleset, $catalog, $data['order_index'] ?? $position, $data);
            }
        });
    }

    /**
     * Personnages du compte de test. `order_index` est l'emplacement dans le carrousel de
     * l'accueil (0 à 4) : il peut être modifié ici, laissé en double ou avec des trous pour tester
     * le placement ; sans ce champ, la position dans cette liste est utilisée.
     * Les compétences, outils, langues, dons et capacités
     * d'historique et d'espèce viennent du catalogue ; ceux listés ici s'y ajoutent.
     *
     * @return list<array<string, mixed>>
     */
    private function characters(): array
    {
        return [
            [
                'name' => 'Aldric',
                'order_index' => 0,
                'class' => 'Guerrier',
                'subclass' => 'Maître de guerre',
                'specie' => 'Nain',
                'background' => 'Criminel',
                'alignment' => 'Loyal bon',
                'level' => 1,
                'hp' => 12,
                'stats' => ['Force' => 16, 'Dextérité' => 13, 'Constitution' => 14, 'Intelligence' => 10, 'Sagesse' => 12, 'Charisme' => 8],
                'skills' => ['Athlétisme' => 'proficient', 'Perception' => 'proficient'],
                'languages' => [],
                'tools' => ['Jeu de dés'],
                'feature_choices' => ['Style de combat' => ['Défense']],
                'spells' => [],
                'inventory' => [
                    'Cotte de mailles' => 1,
                    'Bouclier' => 1,
                    'Épée longue' => 1,
                    'Arbalète légère' => 1,
                    "Carreaux d'arbalète (20)" => 1,
                    "Paquetage d'exploration souterraine" => 1,
                ],
                'detail' => [
                    'personality_traits' => 'Parle peu, mais tient toujours parole.',
                    'ideals' => 'Justice : les coupables doivent payer.',
                    'bonds' => 'A une dette envers la guilde qui l\'a sorti de prison.',
                    'flaws' => 'Ne fait jamais confiance aux elfes.',
                    'backstory' => 'Ancien voleur de la cité de Brasefer, reconverti en garde de caravane.',
                ],
            ],
            [
                'name' => 'Elowen',
                'order_index' => 1,
                'class' => 'Magicien',
                'subclass' => "École d'évocation",
                'specie' => 'Elfe',
                'background' => 'Sage',
                'alignment' => 'Neutre bon',
                'level' => 3,
                'hp' => 17,
                'stats' => ['Force' => 8, 'Dextérité' => 14, 'Constitution' => 12, 'Intelligence' => 17, 'Sagesse' => 13, 'Charisme' => 10],
                'skills' => ['Investigation' => 'proficient', 'Perception' => 'proficient'],
                'languages' => ['Draconique', 'Céleste'],
                'tools' => [],
                'feature_choices' => [],
                'spells' => [
                    1 => ['Trait de feu', 'Rayon de givre', 'Main du mage', 'Lumière', 'Bouclier', 'Vague tonnante', 'Charme-personne', 'Identification'],
                    2 => ['Nappe de brouillard', 'Grande foulée'],
                ],
                'extra_spell_level' => 2,
                'expended_slots' => [1 => 1],
                'inventory' => [
                    'Bâton' => 1,
                    'Dague' => 1,
                    'Grimoire' => 1,
                    'Sacoche à composantes' => 1,
                    'Sac à dos' => 1,
                    'Potion de soins' => 2,
                ],
                'detail' => [
                    'personality_traits' => 'Cite des ouvrages obscurs à tout propos.',
                    'ideals' => 'Connaissance : le savoir doit être partagé.',
                    'bonds' => 'Cherche le grimoire perdu de son ancien maître.',
                    'flaws' => 'Oublie tout le reste lorsqu\'un mystère l\'intéresse.',
                    'backstory' => 'Archiviste de la bibliothèque de Château-Suif, partie étudier la magie des ruines.',
                ],
            ],
            /*
            [
                'name' => 'Skarr',
                'order_index' => 2,
                'class' => 'Barbare',
                'subclass' => 'Voie du Berserker',
                'specie' => 'Demi-orc',
                'background' => 'Soldat',
                'alignment' => 'Chaotique mauvais',
                'level' => 5,
                'hp' => 55,
                'stats' => ['Force' => 16, 'Dextérité' => 12, 'Constitution' => 16, 'Intelligence' => 9, 'Sagesse' => 11, 'Charisme' => 8],
                'ability_increases' => [4 => ['Force' => 2]],
                'skills' => ['Survie' => 'proficient', 'Perception' => 'proficient'],
                'languages' => [],
                'tools' => ['Jeu de cartes'],
                'feature_choices' => [],
                'spells' => [],
                'inventory' => [
                    "Hache d'armes" => 1,
                    'Hachette' => 4,
                    "Paquetage d'explorateur" => 1,
                    'Rations de voyage (1 jour)' => 5,
                ],
                'detail' => [
                    'personality_traits' => 'Rit bruyamment au milieu des combats.',
                    'ideals' => 'Force : seuls les forts méritent de diriger.',
                    'bonds' => 'Porte la hache de son ancien capitaine.',
                    'flaws' => 'Ne recule jamais, même quand il le faudrait.',
                    'backstory' => 'Vétéran des guerres du Nord, devenu mercenaire après la chute de sa compagnie.',
                ],
            ],
            */
            [
                'name' => 'Thalia',
                'order_index' => 3,
                'class' => 'Clerc',
                'subclass' => 'Domaine de la Vie',
                'specie' => 'Humain',
                'background' => 'Acolyte',
                'alignment' => 'Loyal bon',
                'level' => 1,
                'hp' => 10,
                'stats' => ['Force' => 12, 'Dextérité' => 10, 'Constitution' => 14, 'Intelligence' => 11, 'Sagesse' => 16, 'Charisme' => 13],
                'skills' => ['Médecine' => 'proficient', 'Persuasion' => 'proficient'],
                'languages' => ['Céleste', 'Abyssal'],
                'tools' => [],
                'feature_choices' => [],
                'spells' => [
                    1 => ['Flamme sacrée', 'Thaumaturgie', 'Assistance', 'Mot de guérison', 'Soins', 'Bénédiction', 'Bouclier de la foi'],
                ],
                'inventory' => [
                    'Chemise de mailles' => 1,
                    'Bouclier' => 1,
                    "Masse d'armes" => 1,
                    'Symbole sacré' => 1,
                    "Paquetage d'explorateur" => 1,
                ],
                'detail' => [
                    'personality_traits' => 'Voit un signe divin dans chaque événement.',
                    'ideals' => 'Charité : aider ceux qui en ont besoin, sans rien attendre.',
                    'bonds' => 'Protège le temple où elle a grandi.',
                    'flaws' => 'Juge durement ceux qui ne partagent pas sa foi.',
                    'backstory' => 'Prêtresse du temple de Lathandre, envoyée soigner les villages frontaliers.',
                ],
            ],
            [
                'name' => 'Vex',
                'order_index' => 4,
                'class' => 'Roublard',
                'subclass' => null,
                'specie' => 'Demi-elfe',
                'background' => 'Guide',
                'alignment' => 'Neutre mauvais',
                'level' => 2,
                'hp' => 15,
                'stats' => ['Force' => 10, 'Dextérité' => 18, 'Constitution' => 13, 'Intelligence' => 12, 'Sagesse' => 11, 'Charisme' => 13],
                'skills' => [
                    'Discrétion' => 'expertise',
                    'Escamotage' => 'expertise',
                    'Acrobaties' => 'proficient',
                    'Tromperie' => 'proficient',
                ],
                'languages' => ['Halfelin'],
                'tools' => ['Outils de voleur'],
                'feature_choices' => [],
                'spells' => [],
                'inventory' => [
                    'Rapière' => 1,
                    'Arc court' => 1,
                    'Flèches (20)' => 1,
                    'Dague' => 2,
                    'Cuir' => 1,
                    'Outils de voleur' => 1,
                ],
                'detail' => [
                    'personality_traits' => 'Toujours une pièce qui roule entre les doigts.',
                    'ideals' => 'Liberté : aucune chaîne ne le retiendra.',
                    'bonds' => 'Doit de l\'argent à un receleur dangereux.',
                    'flaws' => 'Ne résiste pas à un objet brillant laissé sans surveillance.',
                    'backstory' => 'Guide des marais devenu pilleur de tombes quand les clients se sont faits rares.',
                ],
            ],
        ];
    }

    /**
     * @param  array<string, Collection>  $catalog
     * @param  array<string, mixed>  $data
     */
    private function createCharacter(User $user, Ruleset $ruleset, array $catalog, int $orderIndex, array $data): void
    {
        $classDefinition = $catalog['classes'][$data['class']];
        $specie = $catalog['species'][$data['specie']];
        $background = $catalog['backgrounds'][$data['background']];
        $level = $data['level'];

        /** @var Character $character */
        $character = $user->characters()->create([
            'ruleset_id' => $ruleset->id,
            'name' => $data['name'],
            'current_health_points' => $data['hp'],
            'max_health_points' => $data['hp'],
            'alignment' => $data['alignment'],
            'order_index' => $orderIndex,
        ]);

        $character->detail()->create($data['detail']);

        $character->origin()->create([
            'specie_id' => $specie->id,
            'background_id' => $background->id,
        ]);

        // La sous-classe n'est attribuée que si le niveau du personnage la débloque.
        $subclass = $data['subclass'] === null ? null : Subclass::query()
            ->where('name', $data['subclass'])
            ->whereHas('classLevel', fn ($query) => $query
                ->where('class_id', $classDefinition->id)
                ->whereHas('level', fn ($level) => $level->where('level_number', '<=', $data['level'])))
            ->first();

        /** @var CharacterClass $characterClass */
        $characterClass = $character->classes()->create([
            'class_id' => $classDefinition->id,
            'subclass_id' => $subclass?->id,
            'first_taken_at_character_level' => 1,
        ]);

        // Un historique de niveau par niveau atteint, comme après des montées de niveau.
        foreach (range(1, $level) as $number) {
            $characterLevel = $character->levels()->create([
                'character_level' => $number,
                'experience_points' => self::EXPERIENCE_BY_LEVEL[$number],
            ]);
            $characterLevel->levelClasses()->create([
                'character_class_id' => $characterClass->id,
                'resulting_class_level' => $number,
            ]);
        }

        foreach ($data['stats'] as $abilityName => $score) {
            $character->abilities()->create([
                'ability_id' => $catalog['abilities'][$abilityName]->id,
                'score' => $score,
            ]);
        }

        foreach ($data['ability_increases'] ?? [] as $acquiredAt => $increases) {
            foreach ($increases as $abilityName => $amount) {
                $character->abilityIncreases()->create([
                    'ability_id' => $catalog['abilities'][$abilityName]->id,
                    'increase_amount' => $amount,
                    'acquired_at_character_level' => $acquiredAt,
                ]);
            }
        }

        $this->createProficiencies($character, $catalog, $background, $specie, $data);
        $this->createFeatures($character, $catalog, $classDefinition, $subclass, $specie, $data);
        $this->createSpells($character, $catalog, $classDefinition, $data);

        foreach ($background->feats as $feat) {
            $character->feats()->create(['feat_id' => $feat->id, 'acquired_at_character_level' => 1]);
        }

        foreach ($data['inventory'] as $itemName => $quantity) {
            $character->inventory()->create([
                'item_id' => $catalog['items'][$itemName]->id,
                'quantity' => $quantity,
            ]);
        }
    }

    /**
     * Compétences, langues et outils : ceux de l'historique et de l'espèce, plus ceux du personnage.
     *
     * @param  array<string, Collection>  $catalog
     * @param  array<string, mixed>  $data
     */
    private function createProficiencies(Character $character, array $catalog, Background $background, Species $specie, array $data): void
    {
        $skills = $background->skills->mapWithKeys(fn (Skill $skill) => [$skill->name => 'proficient'])->all();
        foreach ($data['skills'] as $skillName => $proficiencyLevel) {
            $skills[$skillName] = $proficiencyLevel;
        }
        foreach ($skills as $skillName => $proficiencyLevel) {
            $character->skillProficiencies()->create([
                'skill_id' => $catalog['skills'][$skillName]->id,
                'proficiency_level' => $proficiencyLevel,
            ]);
        }

        $languages = $specie->languages->mapWithKeys(fn (Language $language) => [$language->name => 'species'])->all();
        foreach ($data['languages'] as $languageName) {
            $languages[$languageName] ??= 'background';
        }
        foreach ($languages as $languageName => $source) {
            $character->languages()->create([
                'language_id' => $catalog['languages'][$languageName]->id,
                'source' => $source,
            ]);
        }

        $tools = $background->tools->pluck('name')->merge($data['tools'])->unique();
        foreach ($tools as $toolName) {
            $character->tools()->create([
                'tool_id' => $catalog['tools'][$toolName]->id,
                'proficiency_level' => 'proficient',
            ]);
        }
    }

    /**
     * Capacités d'espèce, de classe et de sous-classe jusqu'au niveau du personnage, avec les
     * options choisies (ex. le style de combat).
     *
     * @param  array<string, Collection>  $catalog
     * @param  array<string, mixed>  $data
     */
    private function createFeatures(Character $character, array $catalog, ClassDefinition $classDefinition, ?Subclass $subclass, Species $specie, array $data): void
    {
        $features = $specie->features->map(fn (Feature $feature) => [$feature, 1]);

        $classLevels = $classDefinition->classLevels()
            ->with(['level', 'features'])
            ->get()
            ->filter(fn ($classLevel) => $classLevel->level->level_number <= $data['level']);
        foreach ($classLevels as $classLevel) {
            foreach ($classLevel->features as $feature) {
                $features->push([$feature, $classLevel->level->level_number]);
            }
        }

        if ($subclass) {
            $subclassLevels = $subclass->subclassLevels()
                ->with(['level', 'features'])
                ->get()
                ->filter(fn ($subclassLevel) => $subclassLevel->level->level_number <= $data['level']);
            foreach ($subclassLevels as $subclassLevel) {
                foreach ($subclassLevel->features as $feature) {
                    $features->push([$feature, $subclassLevel->level->level_number]);
                }
            }
        }

        foreach ($features->unique(fn ($entry) => $entry[0]->id) as [$feature, $acquiredAt]) {
            $characterFeature = $character->features()->create([
                'feature_id' => $feature->id,
                'acquired_at_character_level' => $acquiredAt,
            ]);

            foreach ($data['feature_choices'][$feature->name] ?? [] as $optionName) {
                $option = FeatureOption::query()
                    ->where('feature_id', $feature->id)
                    ->where('name', $optionName)
                    ->firstOrFail();
                $characterFeature->choices()->create(['feature_option_id' => $option->id]);
            }
        }
    }

    /**
     * Sorts connus (par niveau d'acquisition) et emplacements de sorts d'un lanceur.
     *
     * @param  array<string, Collection>  $catalog
     * @param  array<string, mixed>  $data
     */
    private function createSpells(Character $character, array $catalog, ClassDefinition $classDefinition, array $data): void
    {
        if ($data['spells'] === []) {
            return;
        }

        foreach ($data['spells'] as $acquiredAt => $spellNames) {
            foreach ($spellNames as $spellName) {
                $character->spells()->create([
                    'spell_id' => $catalog['spells'][$spellName]->id,
                    'acquired_at_character_level' => $acquiredAt,
                ]);
            }
        }

        // Quelques sorts du niveau de sort le plus élevé accessible, pris dans la liste de la classe.
        if (isset($data['extra_spell_level'])) {
            $known = $character->spells()->pluck('spell_id');
            $extraSpells = $classDefinition->spells()
                ->where('spell_level', $data['extra_spell_level'])
                ->whereNotIn('spells.id', $known)
                ->orderBy('spells.id')
                ->limit(2)
                ->get();
            foreach ($extraSpells as $spell) {
                $character->spells()->create([
                    'spell_id' => $spell->id,
                    'acquired_at_character_level' => $data['level'],
                ]);
            }
        }

        foreach (self::SPELL_SLOTS_BY_LEVEL[$data['level']] as $index => $maxSlots) {
            $spellLevel = $index + 1;
            $character->spellSlots()->create([
                'spell_level' => $spellLevel,
                'max_slots' => $maxSlots,
                'expended_slots' => $data['expended_slots'][$spellLevel] ?? 0,
            ]);
        }
    }
}
