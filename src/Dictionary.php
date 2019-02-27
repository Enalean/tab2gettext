<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

namespace Tab2Gettext;

class Dictionary
{
    /**
     * @var array
     */
    private $entries;

    public function __construct(array $entries)
    {
        $this->entries = $entries;
    }

    public static function loadFromCache($filepath)
    {
        return new self(include $filepath);
    }

    public function get($primarykey, $secondarykey) {
        return $this->entries[$primarykey][$secondarykey];
    }
}
