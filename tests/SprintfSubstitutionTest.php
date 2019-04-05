<?php
/**
 * Copyright (c) Enalean, 2019 - Present. All Rights Reserved.
 */

declare(strict_types=1);

namespace Tab2Gettext;

use PHPUnit\Framework\TestCase;

class SprintfSubstitutionTest extends TestCase
{
    /**
     * @dataProvider provideSubstitutions
     */
    public function testSubstitutions(int $count, string $sentence): void
    {
        $this->assertEquals($count, SprintfSubstitution::countSubstitutions($sentence));
    }

    public function provideSubstitutions(): array
    {
        return [
            [0, ''],
            [0, 'Simple sentence'],
            [1, 'With $1 substitution'],
            [2, 'With $1 substitution $2'],
            [2, 'With $2 substitution $1'],
            [2, 'With substitution $2'],
        ];
    }
}
