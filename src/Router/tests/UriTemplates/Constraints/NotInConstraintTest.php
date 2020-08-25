<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Constraints;

use Aphiria\Routing\UriTemplates\Constraints\NotInConstraint;
use PHPUnit\Framework\TestCase;

class NotInConstraintTest extends TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertSame('notIn', NotInConstraint::getSlug());
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
