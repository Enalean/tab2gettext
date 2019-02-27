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

    public function run($filepath, $primarykey, $domain, $langcachepath)
    {
        $rii = new FilterPhpFile(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($filepath),
                \RecursiveIteratorIterator::SELF_FIRST
            )
        );
        foreach ($rii as $file) {
            $this->parseAndSave($file->getPathname(), $primarykey, $domain, Dictionary::loadFromCache($langcachepath));
        }
    }

    private function parseAndSave($path, $primarykey, $domain, Dictionary $dictionary)
    {
        $this->load($path, $primarykey, $domain, $dictionary);
//        $this->printStatments();
        $this->save($path);
    }

    public function load($path, $primarykey, $domain, Dictionary $dictionary)
    {
        $this->logger->info("Processing $path");
        $lexer = new Lexer\Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);
        $parser = new Parser\Php7($lexer);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NodeVisitor\CloningVisitor());

        $traverser->addVisitor(new TabToGettextVisitor($this->logger, $path, $primarykey, $domain, $dictionary));

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
