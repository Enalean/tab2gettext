<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tab2Gettext\BrokenGetTextUsage;

use Psr\Log\LoggerInterface;
use Tab2Gettext\FilterPhpFile;

class BrokenGetTextUsageSearcher
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var BrokenGetTextUsageFileParser
     */
    private $parser;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->parser = new BrokenGetTextUsageFileParser($this->logger);
    }

    public function run(string $filepath, ?string $primary_key): void
    {
        foreach (FilterPhpFile::self($filepath, []) as $file) {
            $path = $file->getPathname();
            $this->logger->debug("Processing $path");
            $this->parser->parse($path, $primary_key);
        }
        $this->logger->info("done");
    }
}
