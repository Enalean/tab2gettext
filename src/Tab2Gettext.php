<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

namespace Tab2Gettext;

use PhpParser\Lexer;
use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter;
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

    public function run($filepath, $primarykey, $domain, $langcachepath_en, $langcachepath_fr, $target)
    {
        $pofile = $target . '/fr_FR/LC_MESSAGES/' . $domain . '.po';
        if (! is_file($pofile)) {
            throw new \RuntimeException("$pofile does not exist");
        }
        $collector = new ConvertedKeysCollector();
        $dictionary_en = Dictionary::loadFromCache($langcachepath_en);
        $dictionary_fr = Dictionary::loadFromCache($langcachepath_fr);

        $rii = new FilterPhpFile(
            new \RecursiveIteratorIterator(
                new \RecursiveCallbackFilterIterator(
                    new \RecursiveDirectoryIterator($filepath),
                    function (\SplFileInfo $file, $key, $iterator) {
                        if ($iterator->hasChildren() && $file->getFilename() !== 'vendor') {
                            return true;
                        }
                        return $file->isFile();
                    }
                ),
                \RecursiveIteratorIterator::SELF_FIRST
            ),
            [$langcachepath_en, $langcachepath_fr]
        );

        foreach ($rii as $file) {
            $this->parseAndSave(
                $file->getPathname(),
                $primarykey,
                $domain,
                $dictionary_en,
                $collector
            );
        }
        $collector->dumpInFrPoFile($dictionary_en, $dictionary_fr, $domain, $pofile);
    }

    private function parseAndSave($path, $primarykey, $domain, Dictionary $dictionary_en, ConvertedKeysCollector $collector)
    {
        $this->load($path, $primarykey, $domain, $dictionary_en, $collector);
//        $this->printStatments();
        $this->save($path);
    }

    public function load($path, $primarykey, $domain, Dictionary $dictionary_en, ConvertedKeysCollector $collector)
    {
        $this->logger->info("Processing $path");
        $lexer = new Lexer\Emulative([
            'usedAttributes' => [
                'comments',
                'startLine',
                'endLine',
                'startTokenPos',
                'endTokenPos',
            ],
        ]);
        $parser = new Parser\Php7($lexer);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NodeVisitor\CloningVisitor());

        $traverser->addVisitor(new TabToGettextVisitor($this->logger, $path, $primarykey, $domain, $dictionary_en, $collector));

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
