<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\ErrorMessages;

use Aphiria\Validation\ErrorMessages\StringReplaceErrorMessageInterpolater;
use PHPUnit\Framework\TestCase;

/**
 * Tests the string replacement error message interpolater
 */
class StringReplaceErrorMessageInterpolaterTest extends TestCase
{
    private StringReplaceErrorMessageInterpolater $interpolater;

    protected function setUp(): void
    {
        $this->interpolater = new StringReplaceErrorMessageInterpolater();
    }

    public function testErrorMessageIdWithNoPlaceholdersIsReturnedIntact(): void
    {
        $this->assertEquals('foo bar', $this->interpolater->interpolate('foo bar'));
    }

    public function testLeftoverUnusedPlaceholdersAreRemovedFromInterpolatedErrorMessage(): void
    {
        $this->assertEquals('foo ', $this->interpolater->interpolate('foo {bar}'));
    }

    public function testPlaceholdersArePopulated(): void
    {
        $this->assertEquals(
            'foo dave young',
            $this->interpolater->interpolate('foo {bar} {baz}', ['bar' => 'dave', 'baz' => 'young'])
        );
    }
}
