<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Constraints;

use Aphiria\Routing\UriTemplates\Constraints\RegexConstraint;
use PHPUnit\Framework\TestCase;

class RegexConstraintTest extends TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('regex', RegexConstraint::getSlug());
    }

    public function testMatchingStringsPass(): void
    {
        $constraint = new RegexConstraint('/^[a-z]{3}$/');
        $this->assertTrue($constraint->passes('foo'));
    }

    public function testNonMatchingStringsFail(): void
    {
        $constraint = new RegexConstraint('/^[a-z]{3}$/');
        $this->assertFalse($constraint->passes('foobar'));
    }
}
