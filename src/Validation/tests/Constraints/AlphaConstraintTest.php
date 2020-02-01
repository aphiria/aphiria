<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\AlphaConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the alphabetic constraint
 */
class AlphaConstraintTest extends TestCase
{
    public function testFailingValue(): void
    {
        $constraint = new AlphaConstraint('foo');
        $this->assertFalse($constraint->passes(''));
        $this->assertFalse($constraint->passes('1'));
        $this->assertFalse($constraint->passes('a b'));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new AlphaConstraint('foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], (new AlphaConstraint)->getErrorMessagePlaceholders('val'));
    }

    public function testPassingValue(): void
    {
        $constraint = new AlphaConstraint('foo');
        $this->assertTrue($constraint->passes('a'));
        $this->assertTrue($constraint->passes('abc'));
    }
}
