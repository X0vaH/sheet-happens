<?php

namespace App\Support;

final class AbilityScore
{
    public static function modifier(int $score): int
    {
        return (int) floor(($score - 10) / 2);
    }
}
