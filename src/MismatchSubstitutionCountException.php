<?php
/**
 * Copyright (c) Enalean, 2019 - Present. All Rights Reserved.
 */

declare(strict_types=1);

namespace Tab2Gettext;

use PhpParser\Node;

class MismatchSubstitutionCountException extends CodeInErrorException
{
    public function __construct(int $expected_count, int $actual_count, string $sentence, Node $node, string $filepath)
    {
        parent::__construct(
            "Expected substitution count differs (expected: $expected_count, actual: $actual_count) for: «${sentence}»",
            $node,
            $filepath
        );
    }
}
