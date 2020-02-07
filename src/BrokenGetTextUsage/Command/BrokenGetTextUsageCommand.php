<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

declare(strict_types=1);

namespace Tab2Gettext\BrokenGetTextUsage\Command;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Composer\XdebugHandler\XdebugHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tab2Gettext\BrokenGetTextUsage\BrokenGetTextUsageSearcher;
use Tab2Gettext\Tab2Gettext;

class BrokenGetTextUsageCommand extends Command
{
    protected static $defaultName = 'broken-gettext-usage';

    private const SRC_DIR = 'src-dir';
    private const PRIMARY_KEY = 'primary-key';

    protected function configure()
    {
        $this
            ->setDescription('Search concatenation, expression, variables in legacy getText')
            ->setHelp(<<<EOS
                This will parse source of tuleap and will identify wrong getText usage (with concatenated code for example).
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
                        self::PRIMARY_KEY,
                        null,
                        InputOption::VALUE_REQUIRED,
                        'The primary key (prefix) to search (optional). If omitted, will search for any primary key.' . PHP_EOL . 'Ex: plugin_tracker' . PHP_EOL
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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $xdebug = new XdebugHandler(self::$defaultName);
        $xdebug->check();
        unset($xdebug);

        $log = new Logger('log');
        $handler = new StreamHandler('php://stdout', $output->isDebug() ? Logger::DEBUG : Logger::INFO);
        $handler->setFormatter(new ColoredLineFormatter(null, "%level_name%: %message%\n"));
        $log->pushHandler($handler);

        try {
            (new BrokenGetTextUsageSearcher($log))
                ->run(
                    $input->getOption(self::SRC_DIR),
                    $input->getOption(self::PRIMARY_KEY)
                );
            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>'. $e->getMessage() .'</error>');
            return 1;
        }
    }
}
