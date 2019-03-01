<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */
declare(strict_types=1);

namespace Tab2Gettext;

use Psr\Log\LoggerInterface;

class ConvertedKeysCollector
{
    /**
     * @var array
     */
    private $entries = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function add(string $primary, string $secondary): void
    {
        if (! isset($this->entries[$primary])) {
            $this->entries[$primary] = [];
        }
        $this->entries[$primary][$secondary] = true;
    }

    public function dumpInFrPoFile(Dictionary $dictionary_en, Dictionary $dictionary_fr, string $target_po): void
    {
        $content = [];
        foreach ($this->entries as $primary => $keys) {
            foreach ($keys as $secondary => $nop) {
                $msgid = $this->escape($dictionary_en->get($primary, $secondary));
                $msgstr = $this->escape($dictionary_fr->get($primary, $secondary));
                $poentry = "msgid \"$msgid\"\nmsgstr \"$msgstr\"\n";

                if (isset($content[$msgid]) && $poentry !== $content[$msgid]) {
                    $this->logger->error("Duplicated key $msgid");
                    do {
                        $msgid .= '.';
                    } while (isset($content[$msgid]) && $poentry !== $content[$msgid]);
                }
                $content[$msgid] = $poentry;
            }
        }
        ksort($content);
        file_put_contents($target_po, implode("\n", $content) . "\n", FILE_APPEND | LOCK_EX);
    }

    private function escape(string $string): string
    {
        $search  = array('\\',   "\n",  '"');
        $replace = array('\\\\', "\\n", '\\"');

        return str_replace($search, $replace, SprintfSubstitution::convertFromTabFormat($string));
    }

    public function purgeTabFile(string $tabfile)
    {
        $content = file($tabfile, FILE_IGNORE_NEW_LINES);
        foreach ($content as $key => $line) {
            if (substr_count($line, "\t") < 2) {
                continue;
            }
            list($primary, $secondary) = explode("\t", $line);
            if (isset($this->entries[$primary][$secondary])) {
                unset($content[$key]);
            }
        }
        file_put_contents($tabfile, implode("\n", $content) . "\n");
    }
}
