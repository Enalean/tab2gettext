<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Tab2Gettext;

class ConvertedKeysCollector
{
    /**
     * @var array
     */
    private $entries = [];

    public function add(string $primary, string $secondary): void
    {
        if (! isset($this->entries[$primary])) {
            $this->entries[$primary] = [];
        }
        $this->entries[$primary][$secondary] = true;
    }

    public function dumpInFrPoFile(Dictionary $dictionary_en, Dictionary $dictionary_fr, string $domain, string $target_po): void
    {
        foreach ($this->entries as $primary => $keys) {
            foreach ($keys as $secondary => $nop) {
                $msgid = $this->escape($dictionary_en->get($primary, $secondary));
                $msgstr = $this->escape($dictionary_fr->get($primary, $secondary));
                $poentry = "msgid \"$msgid\"\nmsgstr \"$msgstr\"\n\n";
                file_put_contents($target_po, $poentry, FILE_APPEND | LOCK_EX);
            }
        }
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
