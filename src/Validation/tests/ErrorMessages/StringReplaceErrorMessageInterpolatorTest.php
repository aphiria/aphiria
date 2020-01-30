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

use Aphiria\Validation\ErrorMessages\StringReplaceErrorMessageInterpolator;
use PHPUnit\Framework\TestCase;

/**
 * Tests the string replacement error message interpolator
 */
class StringReplaceErrorMessageInterpolatorTest extends TestCase
{
    private StringReplaceErrorMessageInterpolator $interpolator;

    protected function setUp(): void
    {
        $this->interpolator = new StringReplaceErrorMessageInterpolator();
    }

    public function testErrorMessageIdWithNoPlaceholdersIsReturnedIntact(): void
    {
        $this->assertEquals('foo bar', $this->interpolator->interpolate('foo bar'));
    }

    public function testLeftoverUnusedPlaceholdersAreRemovedFromInterpolatedErrorMessage(): void
    {
        $this->assertEquals('foo ', $this->interpolator->interpolate('foo {bar}'));
    }

    public function testPlaceholdersArePopulated(): void
    {
        $this->assertEquals(
            'foo dave young',
            $this->interpolator->interpolate('foo {bar} {baz}', ['bar' => 'dave', 'baz' => 'young'])
        );
    }
}
