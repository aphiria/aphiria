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

use Aphiria\Routing\UriTemplates\Constraints\IntegerConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the integer constraint
 */
class IntegerConstraintTest extends TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('int', IntegerConstraint::getSlug());
    }

    public function testFailingValue(): void
    {
        $constraint = new IntegerConstraint();
        $this->assertFalse($constraint->passes(false));
        $this->assertFalse($constraint->passes('foo'));
        $this->assertFalse($constraint->passes(1.5));
        $this->assertFalse($constraint->passes('1.5'));
    }

    public function testPassingValue(): void
    {
        $constraint = new IntegerConstraint();
        $this->assertTrue($constraint->passes(0));
        $this->assertTrue($constraint->passes(1));
    }
}
