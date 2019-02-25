#!/usr/bin/env php
<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

require_once 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;

$log = new Logger('log');
$log->pushHandler(new ErrorLogHandler());

try {
    $reflector = new \Tab2Gettext\Tab2Gettext($log);
    $reflector->run($argv);
} catch (Exception $exception) {
    $log->critical($exception->getMessage());
    exit(1);
}
