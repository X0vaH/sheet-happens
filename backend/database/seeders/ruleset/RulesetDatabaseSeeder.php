<?php

namespace Database\Seeders\ruleset;

use Illuminate\Database\Seeder;

class RulesetDatabaseSeeder extends Seeder
{
    /**
     * Peuple la base avec un catalogue Donjons & Dragons 5e (2024) basé sur des valeurs réelles :
     * 6 caractéristiques, 18 compétences, langues, outils, 9 espèces, 5 classes (Barbare, Clerc,
     * Guerrier, Magicien, Roublard) avec sous-classes, historiques, dons, sorts et équipement.
     */
    public function run(): void
    {
        $this->call([
            RulesetSeeder::class,
            AbilitySeeder::class,
            SkillSeeder::class,
            LanguageSeeder::class,
            AlignmentSeeder::class,
            ToolSeeder::class,
            FeatureSeeder::class,
            SpeciesSeeder::class,
            FeatSeeder::class,
            BackgroundSeeder::class,
            ClassSeeder::class,
            ClassProficiencySeeder::class,
            SpellSeeder::class,
            LevelUpSeeder::class,
            ItemSeeder::class,
            // Dépend des classes (ClassSeeder) et des objets/armes/armures (ItemSeeder) : doit
            // rester en dernier.
            StartingEquipmentSeeder::class,
        ]);
    }
}
