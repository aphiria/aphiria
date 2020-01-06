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

use Aphiria\Validation\ValidationContext;
use Aphiria\Validation\Constraints\InConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the in-array constraint
 */
class InConstraintTest extends TestCase
{
    public function testGettingErrorMessageId(): void
    {
        $constraint = new InConstraint([], 'foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], (new InConstraint(['foo']))->getErrorMessagePlaceholders('val'));
    }

    public function testMatchingValuesPass(): void
    {
        $context = new ValidationContext($this);
        $constraint = new InConstraint(['foo', 'bar'], 'foo');
        $this->assertTrue($constraint->passes('foo', $context));
    }

    /**
     * Tests that non-matching values fail
     */
    public function testNonMatchingValuesFail(): void
    {
        $context = new ValidationContext($this);
        $constraint = new InConstraint(['foo', 'bar'], 'foo');
        $this->assertFalse($constraint->passes('baz', $context));
    }
}
