<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Security\Tests;

use Aphiria\Security\Claim;
use Aphiria\Security\ClaimType;
use PHPUnit\Framework\TestCase;

class ClaimTest extends TestCase
{
    public function testClaimTypeAcceptsString(): void
    {
        $claim = new Claim('foo', 'bar', 'http://example.com');
        $this->assertSame('foo', $claim->type);
    }

    public function testClaimTypeEnumGetsConvertedToString(): void
    {
        $claim = new Claim(ClaimType::Actor, 'foo', 'http://example.com');
        $this->assertSame(ClaimType::Actor->value, $claim->type);
    }

    public function testClaimValueGetsSet(): void
    {
        $claim = new Claim('foo', 'bar', 'http://example.com');
        $this->assertSame('bar', $claim->value);
    }
}
