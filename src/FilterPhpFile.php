<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

namespace Tab2Gettext;

class FilterPhpFile extends \FilterIterator
{
    public function accept()
    {
        $file = $this->getInnerIterator()->current();
        if ($this->fileCanBeSelected($file)) {
            return true;
        }
        return false;
    }

    private function fileCanBeSelected($file)
    {
        return (strpos($file->getPathname(), '/_') === false &&
            (preg_match('/.php$/', $file->getFilename()))
        );
    }
}
