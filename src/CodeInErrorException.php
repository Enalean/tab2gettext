<?php
/**
 * Copyright (c) Enalean, 2019 - Present. All Rights Reserved.
 */

declare(strict_types=1);

namespace Tab2Gettext;

use PhpParser\Node;

class CodeInErrorException extends \RuntimeException
{
    public function __construct(string $message, Node $node, string $filepath)
    {
        $line = $node->getAttribute('startLine');
        parent::__construct("$message in: $filepath at L$line");
    }
}
