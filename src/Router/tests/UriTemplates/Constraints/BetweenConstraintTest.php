<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Constraints;

use Aphiria\Routing\UriTemplates\Constraints\BetweenConstraint;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the between constraint
 */
class BetweenConstraintTest extends TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('between', BetweenConstraint::getSlug());
    }

    public function testFailingValueWithExclusiveRange(): void
    {
        $constraint = new BetweenConstraint(0, 2, false);
        $this->assertFalse($constraint->passes(3));
    }

    public function testFailingValueWithInclusiveRange(): void
    {
        $constraint = new BetweenConstraint(0, 2, true);
        $this->assertFalse($constraint->passes(3));
    }

    public function testPassingValueWithExclusiveRange(): void
    {
        $constraint = new BetweenConstraint(0, 2, false);
        $this->assertTrue($constraint->passes(1));
    }

    public function testPassingValueWithInclusiveRange(): void
    {
        $constraint = new BetweenConstraint(0, 2, true);
        $this->assertTrue($constraint->passes(2));
    }

    public function testInvalidMaxValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max value must be numeric');
        new BetweenConstraint(1, false);
    }

    public function testInvalidMinValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Min value must be numeric');
        new BetweenConstraint(false, 1);
    }
}
