<?php
/**
 * Copyright (c) Enalean, 2019 - Present. All Rights Reserved.
 */

declare(strict_types=1);

namespace Tab2Gettext;

use PhpParser\NodeDumper;
use PhpParser\PrettyPrinter;

class FileToBeSaved
{
    /**
     * @var string
     */
    private $path;
    /**
     * @var array|\PhpParser\Node[]
     */
    private $new_statements;
    /**
     * @var array|\PhpParser\Node\Stmt[]
     */
    private $old_statements;
    /**
     * @var array
     */
    private $old_tokens;

    /**
     * @param string $path
     * @param \PhpParser\Node[] $new_statements
     * @param \PhpParser\Node\Stmt[] $old_statements
     * @param array $old_tokens
     */
    public function __construct(string $path, array $new_statements, array $old_statements, array $old_tokens)
    {
        $this->path = $path;
        $this->new_statements = $new_statements;
        $this->old_statements = $old_statements;
        $this->old_tokens = $old_tokens;
    }

    public function save()
    {
        $printer = new PrettyPrinter\Standard();
        $new_code = $printer->printFormatPreserving($this->new_statements, $this->old_statements, $this->old_tokens);

        file_put_contents($this->path, $new_code);
    }

    public function printStatments()
    {
        $dumper = new NodeDumper;
        echo $dumper->dump($this->new_statements) . "\n";
    }
}
