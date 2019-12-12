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

use Aphiria\Routing\UriTemplates\Constraints\NotInConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the not-in-array constraint
 */
class NotInConstraintTest extends TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('notIn', NotInConstraint::getSlug());
    }

    public function testValueInArrayFails(): void
    {
        $constraint = new NotInConstraint(1, 2, 3);
        $this->assertFalse($constraint->passes(1));
        $this->assertFalse($constraint->passes(2));
        $this->assertFalse($constraint->passes(3));
    }

    public function testValueNotInArrayPasses(): void
    {
        $constraint = new NotInConstraint(1, 2, 3);
        $this->assertTrue($constraint->passes(4));
        $this->assertTrue($constraint->passes(5));
        $this->assertTrue($constraint->passes(6));
    }
}
