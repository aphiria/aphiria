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

use Aphiria\Routing\UriTemplates\Constraints\UuidV4Constraint;
use PHPUnit\Framework\TestCase;

class UuidV4ConstraintTest extends TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertSame('uuidv4', UuidV4Constraint::getSlug());
    }

    public function testMatchingStringsPass(): void
    {
        $constraint = new UuidV4Constraint();
        $string = \random_bytes(16);
        $string[6] = \chr(\ord($string[6]) & 0x0f | 0x40);
        $string[8] = \chr(\ord($string[8]) & 0x3f | 0x80);
        $uuid = \vsprintf('%s%s-%s-%s-%s-%s%s%s', \str_split(\bin2hex($string), 4));
        $this->assertTrue($constraint->passes($uuid));
        $this->assertTrue($constraint->passes('{' . $uuid . '}'));
    }

    public function testNonMatchingStringsFail(): void
    {
        $constraint = new UuidV4Constraint();
        $this->assertFalse($constraint->passes('foo'));
    }
}
