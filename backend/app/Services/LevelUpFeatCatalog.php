<?php

namespace App\Services;

class LevelUpFeatCatalog
{
    // PHB 2014 : dons sans prérequis, hausse de score ni choix supplémentaire.
    public const FEATS = [
        'Combattant à deux armes' => 'Vous maniez deux armes non légères et renforcez votre défense en les tenant ensemble.',
        'Combattant monté' => 'Vous protégez votre monture et prenez l’avantage contre certains adversaires à pied.',
        'Expert de la charge' => 'Après une ruée, une action bonus permet de frapper ou de bousculer avec un élan suffisant.',
        'Explorateur de donjons' => 'Vous repérez mieux les passages secrets et résistez davantage aux pièges.',
        'Maître-arbalétrier' => 'Vous maniez les arbalètes plus rapidement et pouvez tirer efficacement au contact.',
        'Maître des armes à deux mains' => 'Vos armes lourdes peuvent sacrifier leur précision pour infliger davantage de dégâts.',
        'Mobile' => 'Votre vitesse augmente et vos attaques de mêlée facilitent votre retrait.',
        'Sentinelle' => 'Vos réactions de mêlée entravent les déplacements ennemis et protègent vos alliés proches.',
        'Tireur d’élite' => 'Vos tirs surmontent distance et abris partiels, avec la possibilité de privilégier les dégâts à la précision.',
        'Tueur de mages' => 'Vous gênez les incantations proches et fragilisez la concentration des lanceurs que vous blessez.',
    ];
}
