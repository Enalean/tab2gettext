<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

declare(strict_types=1);

namespace Tab2Gettext;

use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class Tab2GettextTest extends TestCase
{
    private $fixtures_dir;
    private $expected_dir;
    /**
     * @var string
     */
    private $cachelangpath;

    public function setUp(): void
    {
        $tmp_dir            = escapeshellarg(sys_get_temp_dir());
        $this->fixtures_dir = exec("mktemp -d -p $tmp_dir tab2gettextXXXXXX");
        $this->expected_dir = __DIR__ . '/_expected';
        $pristine           = __DIR__ . '/_fixtures';
        exec('cp -r ' . escapeshellarg($pristine) . '/* ' . escapeshellarg($this->fixtures_dir) . '/');

        $this->cachelangpath = $this->fixtures_dir . "/cache.en_US.php";
    }

    public function tearDown(): void
    {
        exec('rm -rf ' . escapeshellarg($this->fixtures_dir));
    }

    public function testConversion()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->with("Processing $this->fixtures_dir/plugins/tracker/include/Foo.php")->once();
        $logger->shouldReceive('info')->with("Processing $this->fixtures_dir/plugins/docman/include/index.php")->once();

        $converter = new Tab2Gettext($logger);
        $converter->run($this->fixtures_dir, "plugin_tracker", "tuleap-tracker", $this->cachelangpath);
        $this->assertFileEquals(
            $this->expected_dir . '/plugins/tracker/include/Foo.php',
            $this->fixtures_dir . '/plugins/tracker/include/Foo.php'
        );
        $this->assertFileEquals(
            $this->expected_dir . '/plugins/docman/include/index.php',
            $this->fixtures_dir . '/plugins/docman/include/index.php'
        );
    }
}
