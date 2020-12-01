<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

declare(strict_types=1);

namespace Tab2Gettext;

use Mockery;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
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
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $fixtures;
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup('/');

        $this->expected_dir = __DIR__ . '/_expected';

        $this->fixtures = vfsStream::newDirectory('fixtures');
        $this->fixtures_dir = $this->fixtures->url();
        vfsStream::copyFromFileSystem(__DIR__ . '/_fixtures', $this->fixtures);
        $this->root->addChild($this->fixtures);

        unlink($this->fixtures_dir . '/cache.en_US.php');
        unlink($this->fixtures_dir . '/cache.fr_FR.php');

        $this->cachelangpath_en = __DIR__ . '/_fixtures/cache.en_US.php';
        $this->cachelangpath_fr = __DIR__ . '/_fixtures/cache.fr_FR.php';
    }

    public function testConversionInCore() : void
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->with(Mockery::any());
        $logger->shouldReceive('debug')->with("Processing $this->fixtures_dir" . '/core/www/cvs.php')->once();

        $converter = new Tab2Gettext($logger);
        $this->expectOutputRegex('/\.+/m');
        $converter->run(
            $this->fixtures_dir . '/core',
            "cvs",
            "tuleap-core",
            $this->cachelangpath_en,
            $this->cachelangpath_fr,
            $this->fixtures_dir . '/core/site-content',
            'cvs/cvs.tab'
        );

        $files_to_compare = [
            'core/www/cvs.php',
            'core/site-content/fr_FR/LC_MESSAGES/tuleap-core.po',
            'core/site-content/en_US/cvs/cvs.tab',
            'core/site-content/fr_FR/cvs/cvs.tab'
        ];
        $this->assertFilesAreTheSame($files_to_compare, $this->expected_dir);
    }

    public function testConversion() : void
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->with(Mockery::any());
        $logger->shouldReceive('debug')->with("Processing $this->fixtures_dir" . '/plugins/tracker/include/BrokenLanguageGettextCall.php')->once();
        $logger->shouldReceive('debug')->with("Processing $this->fixtures_dir" . '/plugins/tracker/include/Foo.php')->once();
        $logger->shouldReceive('debug')->with("Processing $this->fixtures_dir" . '/plugins/docman/include/index.php')->once();
        $logger->shouldReceive('debug')->with("Processing $this->fixtures_dir" . '/core/www/cvs.php')->once();
        $logger->shouldReceive('error')->with("Duplicated key Tracker")->once();

        $converter = new Tab2Gettext($logger);
        $this->expectOutputRegex('/\.+/m');
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
        $this->assertFilesAreTheSame($files_to_compare, $this->expected_dir);
    }

    /**
     * @dataProvider codeInErrorProvider
     */
    public function testConversionIsAbortedIfThereIsAnError(string $code, string $error_message): void
    {
        $file = 'plugins/tracker/include/File.php';
        file_put_contents(
            $this->fixtures_dir . '/' . $file,
            $code
        );

        $expected = vfsStream::newDirectory('expected');
        $this->root->addChild($expected);

        $expected_dir = $expected->url();
        vfsStream::create(
            vfsStream::inspect(new vfsStreamStructureVisitor(), $this->fixtures)->getStructure()['fixtures'],
            $expected
        );

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info');
        $logger->shouldReceive('debug');
        $logger->shouldReceive('error');
        $logger->shouldReceive('critical')->with($error_message);

        $converter = new Tab2Gettext($logger);
        $this->expectOutputRegex('/\.+/m');
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
            $file,
            'plugins/tracker/include/BrokenLanguageGettextCall.php',
            'plugins/tracker/include/Foo.php',
            'plugins/docman/include/index.php',
            'plugins/tracker/site-content/fr_FR/LC_MESSAGES/tuleap-tracker.po',
            'plugins/tracker/site-content/en_US/tracker.tab',
            'plugins/tracker/site-content/fr_FR/tracker.tab'
        ];
        $this->assertFilesAreTheSame($files_to_compare, $expected_dir);
    }

    public function codeInErrorProvider(): array
    {
        return [
            [
                '<?php $Language->getText("plugin_tracker", "plugin_allowed_project_title");',
                'Mismatch substitution count!'
            ],
            [
                '<?php $Language->getText("plugin_tracker", "plugin_allowed_project_title", [$a, $b]);',
                'Mismatch substitution count!'
            ],
            [
                '<?php $Language->getText("plugin_tracker", "Secondary key that cannot be found in dictionary");',
                'Sentence not found!'
            ],
        ];
    }

    /**
     * @param string[] $files_to_compare
     * @param string $expected_dir
     */
    private function assertFilesAreTheSame(array $files_to_compare, string $expected_dir): void
    {
        foreach ($files_to_compare as $file) {
            $this->assertFileEquals(
                $expected_dir . '/' . $file,
                $this->fixtures_dir . '/' . $file,
                "$file is not well generated"
            );
        }
    }
}
