#!/usr/bin/env php
<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

require_once 'vendor/autoload.php';

use Composer\XdebugHandler\XdebugHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use \Bramus\Monolog\Formatter\ColoredLineFormatter;
use Tab2Gettext\Tab2Gettext;

$xdebug = new XdebugHandler('tab2gettext');
$xdebug->check();
unset($xdebug);

$log = new Logger('log');
$handler = new StreamHandler('php://stdout', Logger::INFO);
$handler->setFormatter(new ColoredLineFormatter());
$log->pushHandler($handler);

try {
    $reflector = new Tab2Gettext($log);

    if (! isset($argv[1]) && ! is_dir($argv[1])) {
        throw new RuntimeException("Please provide a directory as first parameter");
    }
    $filepath = $argv[1];

    if (! isset($argv[2])) {
        throw new RuntimeException("Please provide a primary key as second parameter");
    }
    $primarykey = $argv[2];

    if (! isset($argv[3])) {
        throw new RuntimeException("Please provide a domain as third parameter");
    }
    $domain = $argv[3];

    if (! isset($argv[4]) && ! is_file($argv[4])) {
        throw new RuntimeException("Please provide a en_US cache lang path as fourth parameter");
    }
    $cachelangpath_en = $argv[4];

    if (! isset($argv[5]) && ! is_file($argv[5])) {
        throw new RuntimeException("Please provide a fr_FR cache lang path as fifth parameter");
    }
    $cachelangpath_fr = $argv[5];

    if (! isset($argv[6]) && ! is_dir($argv[6])) {
        throw new RuntimeException("Please provide a target site-content directory as sixth parameter");
    }
    $target = $argv[6];

    if (! isset($argv[7]) && ! is_file($argv[6] ."/". $argv[7])) {
        throw new RuntimeException("Please provide a tab file name as seventh parameter");
    }
    $tabfile = $argv[7];

    $reflector->run($filepath, $primarykey, $domain, $cachelangpath_en, $cachelangpath_fr, $target, $tabfile);
} catch (Exception $exception) {
    $log->critical($exception->getMessage());
    exit(1);
}
