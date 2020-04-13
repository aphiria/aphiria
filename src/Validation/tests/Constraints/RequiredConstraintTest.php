<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\RequiredConstraint;
use Countable;
use PHPUnit\Framework\TestCase;

/**
 * Tests the required constraint
 */
class RequiredConstraintTest extends TestCase
{
    public function testEmptyArrayFails(): void
    {
        $constraint = new RequiredConstraint('foo');
        $this->assertFalse($constraint->passes([]));
        $countable = $this->createMock(Countable::class);
        $countable->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->assertFalse($constraint->passes($countable));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new RequiredConstraint('foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], (new RequiredConstraint())->getErrorMessagePlaceholders('val'));
    }

    public function testSetValuePasses(): void
    {
        $constraint = new RequiredConstraint('foo');
        $this->assertTrue($constraint->passes(0));
        $this->assertTrue($constraint->passes(true));
        $this->assertTrue($constraint->passes(false));
        $this->assertTrue($constraint->passes('foo'));
    }

    public function testUnsetValueFails(): void
    {
        $constraint = new RequiredConstraint('foo');
        $this->assertFalse($constraint->passes(null));
        $this->assertFalse($constraint->passes(''));
    }
}
