<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */
declare(strict_types=1);

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

    public function get(string $primarykey, string $secondarykey): string {
        if (! isset($this->entries[$primarykey][$secondarykey])) {
            throw new EntryNotFoundException();
        }
        return $this->entries[$primarykey][$secondarykey];
    }
}
