<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\IntegerConstraint;
use PHPUnit\Framework\TestCase;

class IntegerConstraintTest extends TestCase
{
    public function testFailingValue(): void
    {
        $constraint = new IntegerConstraint('foo');
        $this->assertFalse($constraint->passes(false));
        $this->assertFalse($constraint->passes('foo'));
        $this->assertFalse($constraint->passes(1.5));
        $this->assertFalse($constraint->passes('1.5'));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new IntegerConstraint('foo');
        $this->assertSame('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], (new IntegerConstraint())->getErrorMessagePlaceholders('val'));
    }

    public function testPassingValue(): void
    {
        $constraint = new IntegerConstraint('foo');
        $this->assertTrue($constraint->passes(0));
        $this->assertTrue($constraint->passes(1));
    }
}
