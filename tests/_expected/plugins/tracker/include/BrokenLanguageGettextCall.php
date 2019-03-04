<?php
/**
 * Copyright (c) Enalean, 2019-Present. All Rights Reserved.
 */

declare(strict_types=1);

final class BrokenLanguageGettextCall
{
    public function foo() : void
    {
        $GLOBALS['Language']->getText();
        $value = new class { };
        $GLOBALS['Language']->getText('plugin_tracker', $value);
    }
}
