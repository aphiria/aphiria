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

use Aphiria\Routing\UriTemplates\Constraints\InConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the in-array constraint
 */
class InConstraintTest extends TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('in', InConstraint::getSlug());
    }

    public function testValueInArrayPasses(): void
    {
        $constraint = new InConstraint(1, 2, 3);
        $this->assertTrue($constraint->passes(1));
        $this->assertTrue($constraint->passes(2));
        $this->assertTrue($constraint->passes(3));
    }

    public function testValueNotInArrayFails(): void
    {
        $constraint = new InConstraint(1, 2, 3);
        $this->assertFalse($constraint->passes(4));
        $this->assertFalse($constraint->passes(5));
        $this->assertFalse($constraint->passes(6));
    }
}
