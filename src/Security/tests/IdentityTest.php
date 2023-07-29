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
use Aphiria\Security\Identity;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class IdentityTest extends TestCase
{
    /**
     * @param string|null $expectedAuthenticationSchemeName The expected auth scheme name
     */
    #[TestWith(['foo'])]
    #[TestWith([null])]
    public function testGettingAuthenticationSchemeNameReturnsOneSetInConstructor(?string $expectedAuthenticationSchemeName): void
    {
        $identity = new Identity([], $expectedAuthenticationSchemeName);
        $this->assertSame($expectedAuthenticationSchemeName, $identity->getAuthenticationSchemeName());
    }

    public function testGettingClaimsWithFilterOnlyReturnsClaimsOfThatType(): void
    {
        $claims = [
            new Claim('foo', 'bar', 'http://example.com'),
            new Claim('foo', 'baz', 'http://example.com'),
            new Claim('baz', 'quz', 'http://example.com')
        ];
        $identity = new Identity($claims);
        $this->assertSame([$claims[0], $claims[1]], $identity->getClaims('foo'));
    }

    public function testGettingClaimsWithoutFilterReturnsAllClaims(): void
    {
        $claims = [
            new Claim('foo', 'bar', 'http://example.com'),
            new Claim('foo', 'baz', 'http://example.com'),
            new Claim('baz', 'quz', 'http://example.com')
        ];
        $identity = new Identity($claims);
        $this->assertSame($claims, $identity->getClaims());
    }

    public function testGettingNameIdentifierReturnsClaimValueIfSet(): void
    {
        $identityWithoutNameIdentifierClaim = new Identity([]);
        $this->assertNull($identityWithoutNameIdentifierClaim->getName());
        $identityWithNameIdentifierClaim = new Identity([new Claim(ClaimType::NameIdentifier, '123', 'http://example.com')]);
        $this->assertSame('123', $identityWithNameIdentifierClaim->getNameIdentifier());
    }

    public function testGettingNameReturnsClaimValueIfSet(): void
    {
        $identityWithoutNameClaim = new Identity([]);
        $this->assertNull($identityWithoutNameClaim->getName());
        $identityWithNameClaim = new Identity([new Claim(ClaimType::Name, 'Dave', 'http://example.com')]);
        $this->assertSame('Dave', $identityWithNameClaim->getName());
    }

    public function testHasClaimReturnsWhetherOrNotIdentityHasClaim(): void
    {
        // Test with string types
        $identity = new Identity([new Claim('foo', 'bar', 'http://example.com')]);
        $this->assertTrue($identity->hasClaim('foo', 'bar'));
        $this->assertFalse($identity->hasClaim('foo', 'baz'));
        $this->assertFalse($identity->hasClaim('doesNotExist', 'bar'));

        // Test with enum types
        $identity = new Identity([new Claim(ClaimType::Actor, 'bar', 'http://example.com')]);
        $this->assertTrue($identity->hasClaim(ClaimType::Actor, 'bar'));
        $this->assertFalse($identity->hasClaim(ClaimType::Actor, 'baz'));
        $this->assertFalse($identity->hasClaim(ClaimType::NameIdentifier, 'bar'));
    }

    public function testIsAuthenticatedOnlyReturnsTrueIfAuthenticationSchemeNameIsSet(): void
    {
        $authenticatedIdentity = new Identity([], 'foo');
        $this->assertTrue($authenticatedIdentity->isAuthenticated());
        $unauthenticatedIdentity = new Identity([]);
        $this->assertFalse($unauthenticatedIdentity->isAuthenticated());
    }

    public function testSettingAuthenticationSchemeName(): void
    {
        $identity = new Identity([]);
        $this->assertNull($identity->getAuthenticationSchemeName());
        $identity->setAuthenticationSchemeName('foo');
        $this->assertSame('foo', $identity->getAuthenticationSchemeName());
    }
}
