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
use Aphiria\Security\IdentityBuilder;
use Aphiria\Security\PrincipalBuilder;
use PHPUnit\Framework\TestCase;

class PrincipalBuilderTest extends TestCase
{
    public function testAddingDefaultClaimsIssuerPassesItToIdentities(): void
    {
        $user = (new PrincipalBuilder('example.com'))
            ->withIdentity(fn (IdentityBuilder $identity) => $identity->withName('Dave'))
            ->build();
        $this->assertSame('example.com', $user->getClaims(ClaimType::Name)[0]->issuer);
    }

    public function testAddingIdentityBuilderAddsBuiltIdentityToPrincipal(): void
    {
        $user = (new PrincipalBuilder())
            ->withIdentity(fn (IdentityBuilder $identity) => $identity->withName('Dave', 'example.com'))
            ->build();
        $this->assertCount(1, $user->getIdentities());
        $this->assertSame('Dave', $user->getIdentities()[0]->getClaims(ClaimType::Name)[0]->value);
        $this->assertSame('example.com', $user->getIdentities()[0]->getClaims(ClaimType::Name)[0]->issuer);
    }

    public function testAddingIdentityBuilderThatCallsBuildAddsBuiltIdentityToPrincipal(): void
    {
        $user = (new PrincipalBuilder())
            ->withIdentity(
                fn (IdentityBuilder $identity) => $identity->withName('Dave', 'example.com')
                ->build()
            )->build();
        $this->assertCount(1, $user->getIdentities());
        $this->assertSame('Dave', $user->getIdentities()[0]->getClaims(ClaimType::Name)[0]->value);
        $this->assertSame('example.com', $user->getIdentities()[0]->getClaims(ClaimType::Name)[0]->issuer);
    }

    public function testAddingIdentityObjectAddsItToPrincipal(): void
    {
        $identity = new Identity([], 'example.com');
        $user = (new PrincipalBuilder())
            ->withIdentity($identity)
            ->build();
        $this->assertSame([$identity], $user->getIdentities());
    }

    public function testAddingMixOfIdentitiesAndIdentityBuildersAddsThemToPrincipal(): void
    {
        $user = (new PrincipalBuilder())
            ->withIdentity(new Identity([new Claim(ClaimType::Name, 'Dave', 'example.com')]))
            ->withIdentity(fn (IdentityBuilder $identity) => $identity->withName('Lindsey', 'example.com'))
            ->build();
        $this->assertCount(2, $user->getIdentities());
        $this->assertSame('Dave', $user->getIdentities()[0]->getClaims(ClaimType::Name)[0]->value);
        $this->assertSame('example.com', $user->getIdentities()[0]->getClaims(ClaimType::Name)[0]->issuer);
        $this->assertSame('Lindsey', $user->getIdentities()[1]->getClaims(ClaimType::Name)[0]->value);
        $this->assertSame('example.com', $user->getIdentities()[1]->getClaims(ClaimType::Name)[0]->issuer);
    }

    public function testAddingMultipleIdentityBuildersAddsThemToPrincipal(): void
    {
        $user = (new PrincipalBuilder())
            ->withIdentity(fn (IdentityBuilder $identity) => $identity->withName('Dave', 'example.com'))
            ->withIdentity(fn (IdentityBuilder $identity) => $identity->withName('Lindsey', 'example.com'))
            ->build();
        $this->assertCount(2, $user->getIdentities());
        $this->assertSame('Dave', $user->getIdentities()[0]->getClaims(ClaimType::Name)[0]->value);
        $this->assertSame('example.com', $user->getIdentities()[0]->getClaims(ClaimType::Name)[0]->issuer);
        $this->assertSame('Lindsey', $user->getIdentities()[1]->getClaims(ClaimType::Name)[0]->value);
        $this->assertSame('example.com', $user->getIdentities()[1]->getClaims(ClaimType::Name)[0]->issuer);
    }

    public function testAddingMultipleIdentityObjectsAddsThemToPrincipal(): void
    {
        $identities = [
            new Identity([], 'example.com1'),
            new Identity([], 'example.com2')
        ];
        $user = (new PrincipalBuilder())
            ->withIdentity($identities[0])
            ->withIdentity($identities[1])
            ->build();
        $this->assertSame($identities, $user->getIdentities());
    }

    public function testAddingPrimaryIdentitySelectorSetsItInPrincipal(): void
    {
        $identities = [
            new Identity([], 'example1.com'),
            new Identity([], 'example2.com')
        ];
        // By default, the primary identity is the first one added, so for testing we'll select the last one added
        $user = (new PrincipalBuilder('foo'))
            ->withIdentity($identities[0])
            ->withIdentity($identities[1])
            ->withPrimaryIdentitySelector(fn (array $identities) => $identities[1])
            ->build();
        $this->assertSame($identities[1], $user->getPrimaryIdentity());
    }
}
