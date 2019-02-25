<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

namespace Tab2Gettext;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter;
use PhpParser\NodeDumper;
use Psr\Log\LoggerInterface;

class Tab2Gettext
{
    private $oldTokens;
    private $oldStmts;
    private $newStmts;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function run(array $argv)
    {
        if (! isset($argv[1])) {
            throw new \RuntimeException("Please provide a file or directory as first parameter");
        }
        $filepath = $argv[1];

        if (is_dir($filepath)) {
            $rii = new FilterPhpFile(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($filepath),
                    \RecursiveIteratorIterator::SELF_FIRST
                )
            );
            foreach ($rii as $file) {
                $this->parseAndSave($file->getPathname());
            }
            return;
        } elseif (file_exists($filepath)) {
            $this->parseAndSave($filepath);
            return;
        }
        throw new \RuntimeException("$filepath is neither a file nor a directory");
    }

    private function parseAndSave($path)
    {
        $this->load($path);
//        $this->printStatments();
        $this->save($path);
    }

    public function load($path)
    {
        $this->logger->info("Processing $path");
        $lexer = new Lexer\Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);
        $parser = new Parser\Php5($lexer);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NodeVisitor\CloningVisitor());

        $traverser->addVisitor(new TabToGettextVisitor($this->logger, $path));

        $this->oldStmts = $parser->parse(file_get_contents($path));
        $this->oldTokens = $lexer->getTokens();

        $this->newStmts = $traverser->traverse($this->oldStmts);
    }

    public function printStatments()
    {
        $dumper = new NodeDumper;
        echo $dumper->dump($this->newStmts) . "\n";
    }

    public function save($path)
    {
        $printer = new PrettyPrinter\Standard();
        $newCode = $printer->printFormatPreserving($this->newStmts, $this->oldStmts, $this->oldTokens);

        file_put_contents($path, $newCode);
    }
}
