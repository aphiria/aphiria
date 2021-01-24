<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Constraints;

use Aphiria\Routing\UriTemplates\Constraints\BetweenConstraint;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BetweenConstraintTest extends TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertSame('between', BetweenConstraint::getSlug());
    }

    public function testInclusiveFlagsAreRespected(): void
    {
        $minInclusiveConstraint = new BetweenConstraint(1, 3, true, false);
        $this->assertTrue($minInclusiveConstraint->passes(1));
        $this->assertTrue($minInclusiveConstraint->passes(2));
        $this->assertFalse($minInclusiveConstraint->passes(0));

        $maxInclusiveConstraint = new BetweenConstraint(1, 3, false, true);
        $this->assertTrue($maxInclusiveConstraint->passes(3));
        $this->assertTrue($maxInclusiveConstraint->passes(2));
        $this->assertFalse($maxInclusiveConstraint->passes(4));

        $neitherInclusiveConstraint = new BetweenConstraint(1, 3, false, false);
        $this->assertFalse($neitherInclusiveConstraint->passes(1));
        $this->assertFalse($neitherInclusiveConstraint->passes(3));
        $this->assertTrue($neitherInclusiveConstraint->passes(2));

        $bothInclusiveConstraint = new BetweenConstraint(1, 3, true, true);
        $this->assertTrue($bothInclusiveConstraint->passes(1));
        $this->assertTrue($bothInclusiveConstraint->passes(3));
        $this->assertTrue($bothInclusiveConstraint->passes(2));
    }

    public function testNonNumericValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be numeric');
        $constraint = new BetweenConstraint(0, 2);
        $constraint->passes('foo');
    }
}
