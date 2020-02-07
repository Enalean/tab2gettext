<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

declare(strict_types=1);

namespace Tab2Gettext;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Composer\XdebugHandler\XdebugHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConvertCommand extends Command
{
    protected static $defaultName = 'tab2gettext';

    private const SRC_DIR = 'src-dir';
    private const SRC_TAB = 'src-tab';
    private const PRIMARY_KEY = 'primary-key';
    private const DOMAIN = 'domain';
    private const EN_CACHE = 'en-cache';
    private const FR_CACHE = 'fr-cache';
    private const TARGET_DIR = 'target-dir';

    protected function configure()
    {
        $this
            ->setDescription('convert tab to gettext')
            ->setHelp(<<<EOS
                This will parse a directory to convert some .tab entries to gettext file.
                It will populate fr.po files and will remove legacy getText call in the code.
                EOS
            )->setDefinition(
                new InputDefinition([
                    new InputOption(
                        self::SRC_DIR,
                        null,
                        InputOption::VALUE_REQUIRED,
                        'Directory to parse.' . PHP_EOL . 'Ex: $HOME/tuleap pour toute la codebase' . PHP_EOL
                    ),
                    new InputOption(
                        self::SRC_TAB,
                        null,
                        InputOption::VALUE_REQUIRED,
                        'The src .tab files name that we need to treat.' . PHP_EOL . 'Ex: tracker.tab' . PHP_EOL
                    ),
                    new InputOption(
                        self::PRIMARY_KEY,
                        null,
                        InputOption::VALUE_REQUIRED,
                        'The primary key (prefix) to search.' . PHP_EOL . 'Ex: plugin_tracker' . PHP_EOL
                    ),
                    new InputOption(
                        self::DOMAIN,
                        null,
                        InputOption::VALUE_REQUIRED,
                        'The generated gettext domain.' . PHP_EOL . 'Ex: tuleap-tracker' . PHP_EOL
                    ),
                    new InputOption(
                        self::EN_CACHE,
                        null,
                        InputOption::VALUE_REQUIRED,
                        'The cache file which contains en_US .tab strings (extracted from /var/tmp/tuleap_cache/lang/).' . PHP_EOL . 'Ex: /tmp/cache.lang.en_US.php' . PHP_EOL
                    ),
                    new InputOption(
                        self::FR_CACHE,
                        null,
                        InputOption::VALUE_REQUIRED,
                        'The cache file which contains fr_FR .tab strings (extracted from /var/tmp/tuleap_cache/lang/).' . PHP_EOL . 'Ex: /tmp/cache.lang.fr_FR.php' . PHP_EOL
                    ),
                    new InputOption(
                        self::TARGET_DIR,
                        null,
                        InputOption::VALUE_REQUIRED,
                        'The target site-content directory.' . PHP_EOL . 'Ex: $HOME/tuleap/plugins/tracker/site-content' . PHP_EOL
                    ),
                ])
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $src_dir = $input->getOption(self::SRC_DIR);
        if (! is_string($src_dir) || ! is_dir($src_dir)) {
            throw new InvalidArgumentException(sprintf('%s must be a valid directory', self::SRC_DIR));
        }
        $primary = $input->getOption(self::PRIMARY_KEY);
        if (! is_string($primary) ) {
            throw new InvalidArgumentException(sprintf('%s is missing', self::PRIMARY_KEY));
        }
        $domain = $input->getOption(self::DOMAIN);
        if (! is_string($domain)) {
            throw new InvalidArgumentException(sprintf('%s is missing', self::DOMAIN));
        }
        $en_cache = $input->getOption(self::EN_CACHE);
        if (! is_string($en_cache) || ! is_file($en_cache)) {
            throw new InvalidArgumentException(sprintf('%s must be a valid file', self::EN_CACHE));
        }
        $fr_cache = $input->getOption(self::FR_CACHE);
        if (! is_string($fr_cache) || ! is_file($fr_cache)) {
            throw new InvalidArgumentException(sprintf('%s must be a valid file', self::FR_CACHE));
        }
        $target_dir = $input->getOption(self::TARGET_DIR);
        if (! is_string($target_dir) || ! is_dir($target_dir)) {
            throw new InvalidArgumentException(sprintf('%s must be a valid directory', self::TARGET_DIR));
        }
        $src_tab = $input->getOption(self::SRC_TAB);
        if (! is_string($src_tab) || ! is_file($target_dir .'/en_US/'. $src_tab) || ! is_file($target_dir .'/fr_FR/'. $src_tab)) {
            throw new InvalidArgumentException(sprintf('%s is missing', self::SRC_TAB));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $xdebug = new XdebugHandler('tab2gettext');
        $xdebug->check();
        unset($xdebug);

        $log = new Logger('log');
        $handler = new StreamHandler('php://stdout', $output->isDebug() ? Logger::DEBUG : Logger::INFO);
        $handler->setFormatter(new ColoredLineFormatter(null, "%level_name%: %message%\n"));
        $log->pushHandler($handler);

        try {
            (new Tab2Gettext($log))
                ->run(
                    $input->getOption(self::SRC_DIR),
                    $input->getOption(self::PRIMARY_KEY),
                    $input->getOption(self::DOMAIN),
                    $input->getOption(self::EN_CACHE),
                    $input->getOption(self::FR_CACHE),
                    $input->getOption(self::TARGET_DIR),
                    $input->getOption(self::SRC_TAB)
                );
            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>'. $e->getMessage() .'</error>');
            return 1;
        }
    }
}
