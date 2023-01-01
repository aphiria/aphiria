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

use Aphiria\Validation\Constraints\InConstraint;
use PHPUnit\Framework\TestCase;

class InConstraintTest extends TestCase
{
    public function testGettingErrorMessageId(): void
    {
        $constraint = new InConstraint([], 'foo');
        $this->assertSame('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], (new InConstraint(['foo']))->getErrorMessagePlaceholders('val'));
    }

    public function testMatchingValuesPass(): void
    {
        $constraint = new InConstraint(['foo', 'bar'], 'foo');
        $this->assertTrue($constraint->passes('foo'));
    }

    /**
     * Tests that non-matching values fail
     */
    public function testNonMatchingValuesFail(): void
    {
        $constraint = new InConstraint(['foo', 'bar'], 'foo');
        $this->assertFalse($constraint->passes('baz'));
    }
}
