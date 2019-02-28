<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

namespace Tab2Gettext;

use Iterator;

class FilterPhpFile extends \FilterIterator
{
    private $langcachepath;

    public function __construct(Iterator $iterator, $langcachepath)
    {
        parent::__construct($iterator);
        $this->langcachepath = $langcachepath;
    }

    public function accept()
    {
        $file = $this->getInnerIterator()->current();
        if ($this->fileCanBeSelected($file)) {
            return true;
        }
        return false;
    }

    private function fileCanBeSelected(\SplFileInfo $file)
    {
        if ($file->getExtension() !== 'php') {
            return false;
        }

        if ($file->getPathname() === $this->langcachepath) {
            return false;
        }

        return true;
    }
}
