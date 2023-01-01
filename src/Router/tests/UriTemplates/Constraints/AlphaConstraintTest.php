<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Constraints;

use Aphiria\Routing\UriTemplates\Constraints\AlphaConstraint;
use PHPUnit\Framework\TestCase;

class AlphaConstraintTest extends TestCase
{
    public function testAlphaCharsPass(): void
    {
        $constraint = new AlphaConstraint();
        $this->assertTrue($constraint->passes('a'));
        $this->assertTrue($constraint->passes('ab'));
    }

    public function testCorrectSlugIsReturned(): void
    {
        $this->assertSame('alpha', AlphaConstraint::getSlug());
    }

    public function testNonAlphaCharsFail(): void
    {
        $constraint = new AlphaConstraint();
        $this->assertFalse($constraint->passes(''));
        $this->assertFalse($constraint->passes('1'));
        $this->assertFalse($constraint->passes('a b'));
    }
}
