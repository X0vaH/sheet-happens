<?php

namespace App\Exceptions;

use App\Models\Character;
use RuntimeException;

/**
 * Levée quand un utilisateur tente de créer un personnage alors qu'il a déjà atteint
 * le maximum de personnages sauvegardés (Character::MAX_PER_USER).
 */
class CharacterLimitReachedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'Vous avez atteint la limite de '.Character::MAX_PER_USER.' personnages. Supprimez-en un avant d\'en créer un nouveau.'
        );
    }
}
