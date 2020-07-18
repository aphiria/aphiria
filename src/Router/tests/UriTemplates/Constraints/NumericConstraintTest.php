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

use Aphiria\Routing\UriTemplates\Constraints\NumericConstraint;
use PHPUnit\Framework\TestCase;

class NumericConstraintTest extends TestCase
{
    public function testAlphaCharsPass(): void
    {
        $constraint = new NumericConstraint();
        $this->assertTrue($constraint->passes(0));
        $this->assertTrue($constraint->passes(1));
        $this->assertTrue($constraint->passes(1.0));
        $this->assertTrue($constraint->passes('1.0'));
    }

    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('numeric', NumericConstraint::getSlug());
    }

    public function testNonAlphaCharsFail(): void
    {
        $constraint = new NumericConstraint();
        $this->assertFalse($constraint->passes(false));
        $this->assertFalse($constraint->passes('foo'));
    }
}
