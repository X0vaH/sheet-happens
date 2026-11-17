<?php

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesCharacterWithStats;
use Tests\TestCase;

class CharacterStatsTest extends TestCase
{
    use CreatesCharacterWithStats;
    use LazilyRefreshDatabase;

    // -----------------------------
    // Caractéristiques et niveau
    // -----------------------------

    public function test_le_score_effectif_additionne_les_augmentations(): void
    {
        $character = $this->characterWithStats(['Force' => 14]);
        $this->addAbilityIncrease($character, 'Force', 2);
        $this->addAbilityIncrease($character, 'Force', 1);

        $stats = $this->statsFor($character);
        $force = $character->abilities->firstWhere('ability.name', 'Force');

        $this->assertSame(17, $stats->effectiveScore($force->ability_id));
        $this->assertSame(3, $stats->modifier('Force'));
    }

    public function test_le_bonus_de_maitrise_vient_du_niveau_du_personnage(): void
    {
        $this->assertSame(2, $this->statsFor($this->characterWithStats(level: 4))->proficiencyBonus());
        $this->assertSame(3, $this->statsFor($this->characterWithStats(level: 5))->proficiencyBonus());
    }

    public function test_le_prochain_niveau_est_nul_au_niveau_maximum(): void
    {
        $this->assertSame(5, $this->statsFor($this->characterWithStats(level: 4))->nextLevel());
        $this->assertNull($this->statsFor($this->characterWithStats(level: 5))->nextLevel());
    }

    public function test_l_initiative_est_le_modificateur_de_dexterite(): void
    {
        $this->assertSame(3, $this->statsFor($this->characterWithStats(['Dextérité' => 16]))->initiative());
        $this->assertSame(-1, $this->statsFor($this->characterWithStats(['Dextérité' => 8]))->initiative());
    }

    // -----------------------------
    // Compétences et jets de sauvegarde
    // -----------------------------

    public function test_le_bonus_de_competence_depend_du_niveau_de_maitrise(): void
    {
        $stats = $this->statsFor($this->characterWithStats(['Dextérité' => 14]));

        $this->assertSame(2, $stats->skillBonus('Dextérité', 'none'));
        $this->assertSame(4, $stats->skillBonus('Dextérité', 'proficient'));
        $this->assertSame(6, $stats->skillBonus('Dextérité', 'expertise'));
    }

    public function test_le_jet_de_sauvegarde_ajoute_la_maitrise_si_maitrise(): void
    {
        $stats = $this->statsFor($this->characterWithStats(['Constitution' => 12]));

        $this->assertSame(3, $stats->savingThrowBonus('Constitution', true));
        $this->assertSame(1, $stats->savingThrowBonus('Constitution', false));
    }

    // -----------------------------
    // Bonus d'attaque des armes
    // -----------------------------

    public function test_une_arme_de_melee_utilise_la_force(): void
    {
        $character = $this->characterWithStats(['Force' => 16, 'Dextérité' => 18]);
        $axe = $this->giveWeapon($character, "Hache d'armes");

        $this->assertSame(5, $this->statsFor($character)->weaponAttackBonus($axe));
    }

    public function test_une_arme_a_munitions_utilise_la_dexterite(): void
    {
        $character = $this->characterWithStats(['Force' => 16, 'Dextérité' => 12]);
        $bow = $this->giveWeapon($character, 'Arc long');

        $this->assertSame(3, $this->statsFor($character)->weaponAttackBonus($bow));
    }

    public function test_une_arme_de_finesse_utilise_la_meilleure_caracteristique(): void
    {
        $character = $this->characterWithStats(['Force' => 10, 'Dextérité' => 16]);
        $rapier = $this->giveWeapon($character, 'Rapière');

        $this->assertSame(5, $this->statsFor($character)->weaponAttackBonus($rapier));
    }

    // -----------------------------
    // Classe d'armure
    // -----------------------------

    public function test_sans_armure_la_ca_vaut_dix_plus_la_dexterite(): void
    {
        $character = $this->characterWithStats(['Dextérité' => 14]);

        $this->assertSame(12, $this->statsFor($character)->armorClass());
    }

    public function test_un_barbare_sans_armure_ajoute_sa_constitution(): void
    {
        $character = $this->characterWithStats(['Dextérité' => 14, 'Constitution' => 16], className: 'Barbare');

        $this->assertSame(15, $this->statsFor($character)->armorClass());
    }

    public function test_une_armure_legere_ajoute_toute_la_dexterite(): void
    {
        $character = $this->characterWithStats(['Dextérité' => 18]);
        $this->giveArmor($character, 'Cuir');

        $this->assertSame(15, $this->statsFor($character)->armorClass());
    }

    public function test_une_armure_intermediaire_plafonne_la_dexterite(): void
    {
        $character = $this->characterWithStats(['Dextérité' => 18]);
        $this->giveArmor($character, 'Cuirasse');

        $this->assertSame(16, $this->statsFor($character)->armorClass());
    }

    public function test_une_armure_lourde_ignore_la_dexterite(): void
    {
        $character = $this->characterWithStats(['Dextérité' => 18]);
        $this->giveArmor($character, 'Cotte de mailles');

        $this->assertSame(16, $this->statsFor($character)->armorClass());
    }

    public function test_une_armure_lourde_ignore_une_dexterite_negative(): void
    {
        $character = $this->characterWithStats(['Dextérité' => 8]);
        $this->giveArmor($character, 'Cotte de mailles');

        $this->assertSame(16, $this->statsFor($character)->armorClass());
    }

    public function test_une_armure_intermediaire_garde_une_dexterite_negative(): void
    {
        $character = $this->characterWithStats(['Dextérité' => 8]);
        $this->giveArmor($character, 'Cuirasse');

        $this->assertSame(13, $this->statsFor($character)->armorClass());
    }

    public function test_la_meilleure_armure_de_l_inventaire_est_retenue(): void
    {
        $character = $this->characterWithStats(['Dextérité' => 10]);
        $this->giveArmor($character, 'Cuir');
        $this->giveArmor($character, 'Cotte de mailles');

        $this->assertSame(16, $this->statsFor($character)->armorClass());
    }

    public function test_un_bouclier_s_ajoute_a_la_ca(): void
    {
        $character = $this->characterWithStats(['Dextérité' => 10]);
        $this->giveArmor($character, 'Cotte de mailles');
        $this->giveArmor($character, 'Bouclier');

        $this->assertSame(18, $this->statsFor($character)->armorClass());
    }

    public function test_le_style_defense_ajoute_un_avec_une_armure(): void
    {
        $character = $this->characterWithStats(['Dextérité' => 10]);
        $this->giveArmor($character, 'Cotte de mailles');
        $this->chooseFightingStyle($character, 'Défense');

        $this->assertSame(17, $this->statsFor($character)->armorClass());
    }

    public function test_le_style_defense_n_ajoute_rien_sans_armure(): void
    {
        $character = $this->characterWithStats(['Dextérité' => 10]);
        $this->chooseFightingStyle($character, 'Défense');

        $this->assertSame(10, $this->statsFor($character)->armorClass());
    }
}
