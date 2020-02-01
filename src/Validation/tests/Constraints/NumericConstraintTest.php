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

use Aphiria\Validation\Constraints\NumericConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the numeric constraint
 */
class NumericConstraintTest extends TestCase
{
    public function testFailingValue(): void
    {
        $constraint = new NumericConstraint('foo');
        $this->assertFalse($constraint->passes(false));
        $this->assertFalse($constraint->passes('foo'));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new NumericConstraint('foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], (new NumericConstraint)->getErrorMessagePlaceholders('val'));
    }

    public function testPassingValue(): void
    {
        $constraint = new NumericConstraint('foo');
        $this->assertTrue($constraint->passes(0));
        $this->assertTrue($constraint->passes(1));
        $this->assertTrue($constraint->passes(1.0));
        $this->assertTrue($constraint->passes('1.0'));
    }
}
