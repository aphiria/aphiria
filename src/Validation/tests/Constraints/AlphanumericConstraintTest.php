<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\AlphanumericConstraint;
use PHPUnit\Framework\TestCase;

class AlphanumericConstraintTest extends TestCase
{
    public function testFailingValue(): void
    {
        $constraint = new AlphanumericConstraint('foo');
        $this->assertFalse($constraint->passes(''));
        $this->assertFalse($constraint->passes('.'));
        $this->assertFalse($constraint->passes('a1 b'));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new AlphanumericConstraint('foo');
        $this->assertSame('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], (new AlphanumericConstraint())->getErrorMessagePlaceholders('val'));
    }

    public function testPassingValue(): void
    {
        $constraint = new AlphanumericConstraint('foo');
        $this->assertTrue($constraint->passes('1'));
        $this->assertTrue($constraint->passes('a'));
        $this->assertTrue($constraint->passes('a1'));
        $this->assertTrue($constraint->passes('1abc'));
    }
}
