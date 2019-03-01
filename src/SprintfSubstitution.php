<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */
declare(strict_types=1);

namespace Tab2Gettext;

final class SprintfSubstitution
{
    public static function convertFromTabFormat(string $string): string
    {
        return preg_replace('/\$(\d+)/', '%\1\$s', $string);
    }
}
