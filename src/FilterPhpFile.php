<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

namespace Tab2Gettext;

use Iterator;

class FilterPhpFile extends \FilterIterator
{
    private $exclude_files;

    public function __construct(Iterator $iterator, $exclude_files)
    {
        parent::__construct($iterator);
        $this->exclude_files = $exclude_files;
    }

    public static function self(string $filepath, array $exclude_files): \OuterIterator
    {
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
            $exclude_files
        );
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

        return ! in_array($file->getPathname(), $this->exclude_files, true);
    }
}
