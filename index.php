<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
 */

declare(strict_types=1);

use Symfony\Component\Console\Application;
use Tab2Gettext\Command\ConvertCommand;
use Tab2Gettext\BrokenGetTextUsage\Command\BrokenGetTextUsageCommand;

require_once __DIR__ . '/vendor/autoload.php';

$application = new Application('tab2gettext');
$application->add(new ConvertCommand());
$application->add(new BrokenGetTextUsageCommand());
$application->run();
