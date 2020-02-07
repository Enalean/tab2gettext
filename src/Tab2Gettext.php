<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

namespace Tab2Gettext;

use OuterIterator;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use Psr\Log\LoggerInterface;

class Tab2Gettext
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function run($filepath, $primarykey, $domain, $langcachepath_en, $langcachepath_fr, $target, $tabfile)
    {
        $pofile = $target . '/fr_FR/LC_MESSAGES/' . $domain . '.po';
        if (!is_file($pofile)) {
            throw new \RuntimeException("$pofile does not exist");
        }
        $collector = new ConvertedKeysCollector($this->logger);
        $dictionary_en = Dictionary::loadFromCache($langcachepath_en);
        $dictionary_fr = Dictionary::loadFromCache($langcachepath_fr);


        $this->logger->info("Starting conversion in .php files");
        try {
            $files_to_be_saved = $this->parseAllFiles(
                $this->getFilesIterator($filepath, $langcachepath_en, $langcachepath_fr),
                $primarykey,
                $domain,
                $dictionary_en,
                $collector
            );
            $this->saveFiles($files_to_be_saved);

            $this->logger->info(".php files parsed and converted.");
            $this->logger->info("Dump localized sentences in .po file $pofile");
            $collector->dumpInFrPoFile($dictionary_en, $dictionary_fr, $pofile);
            $this->logger->info("Remove old entries from en_US $tabfile");
            $collector->purgeTabFile($target . '/en_US/' . $tabfile);
            $this->logger->info("Remove old entries from fr_FR $tabfile");
            $collector->purgeTabFile($target . '/fr_FR/' . $tabfile);
            $this->logger->info("done");
        } catch (MismatchSubstitutionCountException $exception) {
            $this->logger->critical("Mismatch substitution count!");
            $this->logger->error($exception->getMessage());
        } catch (SentenceNotFoundException $exception) {
            $this->logger->critical("Sentence not found!");
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @return FileToBeSaved[]
     */
    private function parseAllFiles(
        OuterIterator $files_iterator,
        string $primarykey,
        string $domain,
        Dictionary $dictionary_en,
        ConvertedKeysCollector $collector
    ): array {
        $this->logger->info("Parsing files");
        $files_to_be_saved = [];

        $i = 0;
        foreach ($files_iterator as $file) {
            echo ".";
            if ($i++ % 80 === 0) {
                echo "\n";
            }
            $files_to_be_saved[] = $this->parse(
                $file->getPathname(),
                $primarykey,
                $domain,
                $dictionary_en,
                $collector
            );
        }
        echo "\n";

        return $files_to_be_saved;
    }

    /**
     * @param FileToBeSaved[] $files_to_be_saved
     */
    private function saveFiles(array $files_to_be_saved): void
    {
        $this->logger->info("Saving files");
        $i = 0;
        foreach ($files_to_be_saved as $file) {
            echo ".";
            if ($i++ % 80 === 0) {
                echo "\n";
            }
            $file->save();
        }
        echo "\n";
    }

    public function parse(
        string $path,
        string $primarykey,
        string $domain,
        Dictionary $dictionary_en,
        ConvertedKeysCollector $collector
    ): FileToBeSaved {
        $this->logger->debug("Processing $path");
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

        $traverser->addVisitor(new TabToGettextVisitor($path, $primarykey, $domain, $dictionary_en,
            $collector));

        $old_statements = $parser->parse(file_get_contents($path));
        $old_tokens = $lexer->getTokens();

        $new_statements = $traverser->traverse($old_statements);
        return new FileToBeSaved($path, $new_statements, $old_statements, $old_tokens);
    }

    private function getFilesIterator(
        string $filepath,
        string $langcachepath_en,
        string $langcachepath_fr
    ): OuterIterator {
        return new FilterPhpFile(
            new \RecursiveIteratorIterator(
                new \RecursiveCallbackFilterIterator(
                    new \RecursiveDirectoryIterator($filepath),
                    static function (\SplFileInfo $file, string $key, \RecursiveDirectoryIterator $iterator): bool {
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
    }
}
