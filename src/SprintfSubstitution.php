<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */
declare(strict_types=1);

namespace Tab2Gettext;

final class SprintfSubstitution
{
    private const PATTERN = '/\$(\d+)/';

    public static function convertFromTabFormat(string $string): string
    {
        return preg_replace(self::PATTERN, '%\1\$s', $string);
    }

    public static function countSubstitutions(string $value): int
    {
        $nb = preg_match_all(self::PATTERN, $value, $matches, PREG_SET_ORDER);
        if (! $nb) {
            return 0;
        }

        $substitutions = array_column($matches, 1);

        return (int) max($substitutions);
    }
}
