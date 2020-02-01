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

use Aphiria\Validation\Constraints\NotInConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the not-in-array constraint
 */
class NotInConstraintTest extends TestCase
{
    public function testGettingErrorMessageId(): void
    {
        $constraint = new NotInConstraint([], 'foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], (new NotInConstraint(['foo']))->getErrorMessagePlaceholders('val'));
    }

    public function testMatchingValuesPass(): void
    {
        $constraint = new NotInConstraint(['foo', 'bar'], 'foo');
        $this->assertTrue($constraint->passes('baz'));
    }

    /**
     * Tests that non-matching values fail
     */
    public function testNonMatchingValuesFail(): void
    {
        $constraint = new NotInConstraint(['foo', 'bar'], 'foo');
        $this->assertFalse($constraint->passes('foo'));
    }
}
