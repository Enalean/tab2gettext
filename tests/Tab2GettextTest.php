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
    private $cachelangpath_en;
    /**
     * @var string
     */
    private $cachelangpath_fr;

    public function setUp(): void
    {
        $tmp_dir            = escapeshellarg(sys_get_temp_dir());
        $this->fixtures_dir = exec("mktemp -d -p $tmp_dir tab2gettextXXXXXX");
        $this->expected_dir = __DIR__ . '/_expected';
        $pristine           = __DIR__ . '/_fixtures';
        exec('cp -r ' . escapeshellarg($pristine) . '/* ' . escapeshellarg($this->fixtures_dir) . '/');

        $this->cachelangpath_en = $this->fixtures_dir . "/cache.en_US.php";
        $this->cachelangpath_fr = $this->fixtures_dir . "/cache.fr_FR.php";
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
        $converter->run(
            $this->fixtures_dir,
            "plugin_tracker",
            "tuleap-tracker",
            $this->cachelangpath_en,
            $this->cachelangpath_fr,
            $this->fixtures_dir . '/plugins/tracker/site-content',
            'tracker.tab'
        );

        $files_to_compare = [
            'plugins/tracker/include/Foo.php',
            'plugins/tracker/include/Foo.php',
            'plugins/docman/include/index.php',
            'plugins/tracker/site-content/fr_FR/LC_MESSAGES/tuleap-tracker.po',
            'plugins/tracker/site-content/en_US/tracker.tab',
            'plugins/tracker/site-content/fr_FR/tracker.tab'
        ];
        foreach ($files_to_compare as $file) {
            $this->assertFileEquals(
                $this->expected_dir .'/'. $file,
                $this->fixtures_dir .'/'. $file,
                "$file is not well generated"
            );
        }
    }
}
