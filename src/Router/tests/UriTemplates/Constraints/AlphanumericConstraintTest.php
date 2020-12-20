<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Constraints;

use Aphiria\Routing\UriTemplates\Constraints\AlphanumericConstraint;
use PHPUnit\Framework\TestCase;

class AlphanumericConstraintTest extends TestCase
{
    public function testAlphanumericCharsPass(): void
    {
        $constraint = new AlphanumericConstraint();
        $this->assertTrue($constraint->passes('1'));
        $this->assertTrue($constraint->passes('a'));
        $this->assertTrue($constraint->passes('a1'));
        $this->assertTrue($constraint->passes('1abc'));
    }

    public function testCorrectSlugIsReturned(): void
    {
        $this->assertSame('alphanumeric', AlphanumericConstraint::getSlug());
    }

    public function testNonAlphanumericCharsFail(): void
    {
        $constraint = new AlphanumericConstraint();
        $this->assertFalse($constraint->passes(''));
        $this->assertFalse($constraint->passes('.'));
        $this->assertFalse($constraint->passes('a1 b'));
    }
}
