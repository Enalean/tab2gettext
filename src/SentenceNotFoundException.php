<?php
/**
 * Copyright (c) Enalean, 2019 - Present. All Rights Reserved.
 */

declare(strict_types=1);

namespace Tab2Gettext;

use PhpParser\Node;

class SentenceNotFoundException extends CodeInErrorException
{
    public function __construct(string $primary, string $secondary, Node $node, string $filepath)
    {
        parent::__construct("Cannot found ($primary, $secondary)", $node, $filepath);
    }
}
