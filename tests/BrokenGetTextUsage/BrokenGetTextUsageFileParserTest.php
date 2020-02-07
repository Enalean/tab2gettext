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

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class BrokenGetTextUsageFileParserTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testParse()
    {
        $file = __DIR__.'/../_fixtures/plugins/tracker/include/BrokenLanguageGettextCall.php';
        $logger = Mockery::mock(LoggerInterface::class);
        $logger
            ->shouldReceive('warning')
            ->with("Bad usage of getText in $file:14")
            ->once();
        $logger
            ->shouldReceive('notice')
            ->with('->getText(\'plugin_tracker\', $value)')
            ->once();

        $parser = new BrokenGetTextUsageFileParser($logger);
        $parser->parse($file, null);
    }
}
