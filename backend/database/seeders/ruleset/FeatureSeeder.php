<?php

namespace Database\Seeders\ruleset;

use App\Models\FeatureOption;
use App\Models\ruleset\Feature;
use Database\Seeders\ruleset\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    use SeedsOnce;

    public function run(): void
    {
        $ruleset = $this->ruleset();
        Feature::where('ruleset_id', $ruleset->id)->where('name', 'Fureur implacable')
            ->update(['name' => 'Frénésie']);

        $featureDefinitions = [
            // Traits raciaux (espèce)
            'Vision dans le noir' => ['species', "Vous voyez dans l'obscurité comme s'il s'agissait d'une faible lumière, jusqu'à une certaine distance."],
            'Résilience naine' => ['species', "Vous bénéficiez d'un avantage aux jets de sauvegarde contre le poison et d'une résistance aux dégâts de poison."],
            'Ascendance féerique' => ['species', "Vous bénéficiez d'un avantage aux jets de sauvegarde contre être charmé, et la magie ne peut pas vous endormir."],
            'Transe' => ['species', "Vous n'avez pas besoin de dormir ; vous méditez profondément 4 heures par jour."],
            'Ruse gnome' => ['species', "Vous bénéficiez d'un avantage aux jets de sauvegarde d'Intelligence, de Sagesse et de Charisme contre la magie."],
            'Chanceux' => ['species', "Quand vous obtenez un 1 sur un dé de test, d'attaque ou de sauvegarde, vous pouvez relancer le dé."],
            'Brave' => ['species', "Vous bénéficiez d'un avantage aux jets de sauvegarde contre être effrayé."],
            'Ascendance draconique' => ['species', 'Vous avez une ascendance liée à un type de dragon qui détermine votre résistance aux dégâts et votre souffle.'],
            'Souffle du dragon' => ['species', 'Vous pouvez exhaler une énergie destructrice dans une zone déterminée par votre ascendance draconique.'],
            'Polyvalence' => ['species', 'Vous gagnez une maîtrise de compétence de votre choix.'],
            'Endurance implacable' => ['species', 'Quand vous tombez à 0 point de vie sans être tué sur le coup, vous pouvez tomber à 1 point de vie à la place.'],
            'Résistance infernale' => ['species', "Vous bénéficiez d'une résistance aux dégâts de feu."],
            'Legs infernal' => ['species', "Vous connaissez un sort mineur et gagnez progressivement d'autres sorts liés à votre héritage infernal."],
            'Ingéniosité' => ['species', 'Vous gagnez une Inspiration héroïque chaque fois que vous terminez un repos long.'],
            // Aptitudes de classe
            'Rage' => ['class', 'Au combat, vous puisez dans une rage primitive qui vous octroie avantage, résistance et dégâts bonus.'],
            'Défense sans armure' => ['class', "Tant que vous ne portez pas d'armure, votre Classe d'Armure inclut votre modificateur de Constitution."],
            'Attaque téméraire' => ['class', "Vous pouvez attaquer avec témérité pour bénéficier d'un avantage, au prix de votre propre défense."],
            'Sens du danger' => ['class', "Vous bénéficiez d'un avantage aux jets de sauvegarde de Dextérité contre les effets que vous pouvez voir."],
            'Incantation' => ['class', "Vous pouvez lancer des sorts en utilisant votre caractéristique d'incantation."],
            'Domaine divin' => ['class', 'Vous choisissez un domaine divin qui octroie des sorts et aptitudes supplémentaires.'],
            'Style de combat' => ['class', 'Vous adoptez un style de combat particulier comme spécialité.'],
            'Récupération au combat' => ['class', "Une fois par repos court ou long, vous pouvez récupérer des points de vie en tant qu'action bonus."],
            'Sursaut d\'action' => ['class', 'Vous pouvez prendre une action supplémentaire lors de votre tour.'],
            'Récupération arcanique' => ['class', "Une fois par jour lors d'un repos court, vous récupérez des emplacements de sorts dépensés."],
            'Expertise' => ['class', 'Vous doublez votre bonus de maîtrise pour certaines compétences ou outils maîtrisés.'],
            'Vol furtif' => ['class', "Vous infligez des dégâts supplémentaires lorsque vous touchez avec avantage ou en présence d'un allié."],
            'Argot des voleurs' => ['class', 'Vous connaissez un argot secret utilisé par les criminels, incluant un code chiffré.'],
            'Ruse' => ['class', "Vous pouvez vous précipiter, vous dégager ou vous cacher en tant qu'action bonus."],
            // Aptitudes de sous-classe
            'Frénésie' => ['subclass', 'Votre rage permet une attaque de mêlée en action bonus à partir du tour suivant, au prix d’un niveau d’épuisement à sa fin.'],
            'Disciple de la vie' => ['subclass', 'Vos sorts de soins rendent des points de vie supplémentaires à leurs cibles.'],
            'Manœuvres de combat' => ['subclass', 'Vous apprenez des manœuvres tactiques alimentées par des dés de supériorité.'],
            'Savant en évocation' => ['subclass', "Vos sorts d'évocation sont plus puissants et épargnent vos alliés."],
            'Mains agiles' => ['subclass', "Vous pouvez utiliser des outils de voleur ou désamorcer un piège en tant qu'action bonus."],
            // Aptitudes générales
            'Inspiration héroïque' => ['general', "Vous pouvez dépenser un point d'Inspiration héroïque pour obtenir un avantage sur un jet."],
            'Multiclassage' => ['general', "Vous pouvez, en gagnant un niveau, choisir d'acquérir un niveau dans une autre classe."],
        ];

        $features = $this->seedManyOnce(Feature::class, ['ruleset_id', 'name'], collect($featureDefinitions)
            ->map(fn (array $feature, string $name): array => [
                'ruleset_id' => $ruleset->id,
                'name' => $name,
                'feature_type' => $feature[0],
                'description' => $feature[1],
            ])
            ->values()
            ->all())
            ->keyBy('name');

        foreach ($featureDefinitions as $name => [$type, $description]) {
            $features[$name]->update(['description' => $description]);
        }

        // Options de la Feature "Style de combat" (Guerrier) : contrairement aux sous-classes
        // (Domaine de la Vie, etc.), ce choix ne mérite pas sa propre Subclass, donc il est
        // modélisé comme un simple choix parmi plusieurs FeatureOption d'une même Feature.
        $fightingStyles = [
            'Archerie' => "+2 aux jets d'attaque avec les armes à distance.",
            'Défense' => '+1 CA lorsque le personnage porte une armure.',
            'Duel' => "+2 dégâts avec une arme de mêlée tenue à une main lorsque l'autre main ne tient pas d'autre arme.",
            'Combat à deux armes' => "Ajoutez le modificateur de caractéristique aux dégâts de l'attaque effectuée avec la seconde arme.",
            'Combat avec arme à deux mains' => 'Permet de relancer certains résultats faibles sur les dés de dégâts d\'une arme utilisée à deux mains.',
            'Protection' => 'Utilisez votre réaction avec un bouclier pour gêner une attaque visant un allié proche.',
        ];

        $this->seedManyOnce(FeatureOption::class, ['feature_id', 'name'], collect($fightingStyles)
            ->map(fn (string $description, string $name): array => [
                'feature_id' => $features['Style de combat']->id,
                'name' => $name,
                'description' => $description,
            ])
            ->values()
            ->all());
    }
}
