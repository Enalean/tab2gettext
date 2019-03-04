<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

declare(strict_types=1);

namespace Tab2Gettext;

use Mockery;
use org\bovigo\vfs\vfsStream;
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
        $this->expected_dir = __DIR__ . '/_expected';
        $this->fixtures_dir = vfsStream::setup('/')->url();
        $pristine           = __DIR__ . '/_fixtures';
        vfsStream::copyFromFileSystem($pristine);
        unlink($this->fixtures_dir . '/cache.en_US.php');
        unlink($this->fixtures_dir . '/cache.fr_FR.php');

        $this->cachelangpath_en = __DIR__ . '/_fixtures/cache.en_US.php';
        $this->cachelangpath_fr = __DIR__ . '/_fixtures/cache.fr_FR.php';
    }

    public function testConversion() : void
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->with(Mockery::any());
        $logger->shouldReceive('debug')->with("Processing $this->fixtures_dir" . 'plugins/tracker/include/BrokenLanguageGettextCall.php')->once();
        $logger->shouldReceive('debug')->with("Processing $this->fixtures_dir" . 'plugins/tracker/include/Foo.php')->once();
        $logger->shouldReceive('debug')->with("Processing $this->fixtures_dir" . 'plugins/docman/include/index.php')->once();
        $logger->shouldReceive('error')->with("Duplicated key Tracker")->once();

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
            'plugins/tracker/include/BrokenLanguageGettextCall.php',
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
