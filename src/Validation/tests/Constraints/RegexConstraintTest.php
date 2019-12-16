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
use Aphiria\Validation\Constraints\RegexConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the regex constraint
 */
class RegexConstraintTest extends TestCase
{
    public function testGettingErrorMessageId(): void
    {
        $constraint = new RegexConstraint('/foo/', 'foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testMatchingValuesPass(): void
    {
        $context = new ValidationContext($this);
        $constraint = new RegexConstraint('/^[a-z]{3}$/', 'foo');
        $this->assertTrue($constraint->passes('foo', $context));
    }

    /**
     * Tests that non-matching values fail
     */
    public function testNonMatchingValuesFail(): void
    {
        $context = new ValidationContext($this);
        $constraint = new RegexConstraint('/^[a-z]{3}$/', 'foo');
        $this->assertFalse($constraint->passes('a', $context));
    }
}
