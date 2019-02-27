<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

namespace Tab2Gettext;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class Tab2GettextTest extends TestCase
{
    private $fixtures_dir;
    private $expected_dir;

    public function setUp()
    {
        $tmp_dir            = escapeshellarg(sys_get_temp_dir());
        $this->fixtures_dir = exec("mktemp -d -p $tmp_dir tab2gettextXXXXXX");
        $this->expected_dir = __DIR__ . '/_expected';
        $pristine           = __DIR__ . '/_fixtures';
        exec('cp -r ' . escapeshellarg($pristine) . '/* ' . escapeshellarg($this->fixtures_dir) . '/');
    }

    public function tearDown()
    {
        exec('rm -rf ' . escapeshellarg($this->fixtures_dir));
    }

    public function testConversion()
    {
        $logger = $this->createMock(LoggerInterface::class);

        $converter = new Tab2Gettext($logger);
        $converter->run(["", $this->fixtures_dir]);
        $this->assertEquals(
            file_get_contents($this->expected_dir . '/Foo.php'),
            file_get_contents($this->fixtures_dir . '/Foo.php')
        );
    }
}
