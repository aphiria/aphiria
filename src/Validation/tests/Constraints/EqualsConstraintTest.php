<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\EqualsConstraint;
use PHPUnit\Framework\TestCase;

class EqualsConstraintTest extends TestCase
{
    public function testEqualValuesPass(): void
    {
        $constraint = new EqualsConstraint('foo', 'bar');
        $this->assertTrue($constraint->passes('foo'));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new EqualsConstraint('foo', 'bar');
        $this->assertSame('bar', $constraint->errorMessageId);
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], new EqualsConstraint('foo')->getErrorMessagePlaceholders('val'));
    }

    public function testUnequalValuesFail(): void
    {
        $constraint = new EqualsConstraint('foo', 'bar');
        $this->assertFalse($constraint->passes('baz'));
    }
}
