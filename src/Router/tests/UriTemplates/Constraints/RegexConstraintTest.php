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

use Aphiria\Routing\UriTemplates\Constraints\RegexConstraint;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RegexConstraintTest extends TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertSame('regex', RegexConstraint::getSlug());
    }

    public function testEmptyRegexThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Regex cannot be empty');
        new RegexConstraint('');
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
