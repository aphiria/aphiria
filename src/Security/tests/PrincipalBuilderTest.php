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
use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PrincipalBuilderTest extends TestCase
{
    /**
     * Provides a list of claims calls, their claim types, and expected claim values
     *
     * @return list<array{Closure(PrincipalBuilder): PrincipalBuilder, ClaimType, mixed}> The list of claims calls
     */
    public static function provideClaimsCalls(): array
    {
        $dateOfBirth = new DateTimeImmutable();

        return [
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withActor('foo'), ClaimType::Actor, 'foo'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withCountry('foo'), ClaimType::Country, 'foo'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withDateOfBirth($dateOfBirth), ClaimType::DateOfBirth, $dateOfBirth],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withDns('foo'), ClaimType::Dns, 'foo'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withEmail('foo@bar.com'), ClaimType::Email, 'foo@bar.com'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withGender('male'), ClaimType::Gender, 'male'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withGivenName('Dave'), ClaimType::GivenName, 'Dave'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withHomePhone('5555555555'), ClaimType::HomePhone, '5555555555'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withLocality('en'), ClaimType::Locality, 'en'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withMobilePhone('5555555555'), ClaimType::MobilePhone, '5555555555'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withName('Dave'), ClaimType::Name, 'Dave'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withNameIdentifier(123), ClaimType::NameIdentifier, 123],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withOtherPhone('5555555555'), ClaimType::OtherPhone, '5555555555'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withPostalCode(90210), ClaimType::PostalCode, 90210],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withRoles('admin'), ClaimType::Role, 'admin'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withRsa('foo'), ClaimType::Rsa, 'foo'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withSid('foo'), ClaimType::Sid, 'foo'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withStateOrProvince('IL'), ClaimType::StateOrProvince, 'IL'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withStreetAddress('123 Fake St'), ClaimType::StreetAddress, '123 Fake St'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withSurname('Young'), ClaimType::Surname, 'Young'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withThumbprint('foo'), ClaimType::Thumbprint, 'foo'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withUpn('foo'), ClaimType::Upn, 'foo'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withUri('https://example.com'), ClaimType::Uri, 'https://example.com'],
            [fn (PrincipalBuilder $principalBuilder): PrincipalBuilder => $principalBuilder->withX500DistinguishedName('foo'), ClaimType::X500DistinguishedName, 'foo']
        ];
    }

    #[DataProvider('provideClaimsCalls')]
    public function testAddingClaimsAddsThemToPrimaryIdentity(
        Closure $claimsCall,
        ClaimType $type,
        mixed $value
    ): void {
        $principalBuilder = new PrincipalBuilder('example.com');
        $claimsCall($principalBuilder);
        $user = $principalBuilder->build();
        $this->assertCount(1, $user->filterClaims($type));
        $this->assertSame($value, $user->filterClaims($type)[0]->value);
    }

    public function testAddingDefaultClaimsIssuerPassesItToIdentities(): void
    {
        $user = (new PrincipalBuilder('example.com'))
            ->withIdentity(fn (IdentityBuilder $identity) => $identity->withName('Dave'))
            ->build();
        $this->assertSame('example.com', $user->filterClaims(ClaimType::Name)[0]->issuer);
    }

    public function testAddingIdentityBuilderAddsBuiltIdentityToPrincipal(): void
    {
        $user = (new PrincipalBuilder())
            ->withIdentity(fn (IdentityBuilder $identity) => $identity->withName('Dave', 'example.com'))
            ->build();
        $this->assertCount(1, $user->getIdentities());
        $this->assertSame('Dave', $user->getIdentities()[0]->filterClaims(ClaimType::Name)[0]->value);
        $this->assertSame('example.com', $user->getIdentities()[0]->filterClaims(ClaimType::Name)[0]->issuer);
    }

    public function testAddingIdentityBuilderThatCallsBuildAddsBuiltIdentityToPrincipal(): void
    {
        $user = (new PrincipalBuilder())
            ->withIdentity(
                fn (IdentityBuilder $identity) => $identity->withName('Dave', 'example.com')
                ->build()
            )->build();
        $this->assertCount(1, $user->getIdentities());
        $this->assertSame('Dave', $user->getIdentities()[0]->filterClaims(ClaimType::Name)[0]->value);
        $this->assertSame('example.com', $user->getIdentities()[0]->filterClaims(ClaimType::Name)[0]->issuer);
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
        $this->assertSame('Dave', $user->getIdentities()[0]->filterClaims(ClaimType::Name)[0]->value);
        $this->assertSame('example.com', $user->getIdentities()[0]->filterClaims(ClaimType::Name)[0]->issuer);
        $this->assertSame('Lindsey', $user->getIdentities()[1]->filterClaims(ClaimType::Name)[0]->value);
        $this->assertSame('example.com', $user->getIdentities()[1]->filterClaims(ClaimType::Name)[0]->issuer);
    }

    public function testAddingMultipleIdentityBuildersAddsThemToPrincipal(): void
    {
        $user = (new PrincipalBuilder())
            ->withIdentity(fn (IdentityBuilder $identity) => $identity->withName('Dave', 'example.com'))
            ->withIdentity(fn (IdentityBuilder $identity) => $identity->withName('Lindsey', 'example.com'))
            ->build();
        $this->assertCount(2, $user->getIdentities());
        $this->assertSame('Dave', $user->getIdentities()[0]->filterClaims(ClaimType::Name)[0]->value);
        $this->assertSame('example.com', $user->getIdentities()[0]->filterClaims(ClaimType::Name)[0]->issuer);
        $this->assertSame('Lindsey', $user->getIdentities()[1]->filterClaims(ClaimType::Name)[0]->value);
        $this->assertSame('example.com', $user->getIdentities()[1]->filterClaims(ClaimType::Name)[0]->issuer);
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

    public function testBuildingPrimaryIdentityAddsThatBuiltIdentityBeforeAllOtherIdentities(): void
    {
        $principalBuilder = new PrincipalBuilder('example.com');
        $principalBuilder->withIdentity(new Identity());
        $principalBuilder->withNameIdentifier('foo', 'example.com');
        $user = $principalBuilder->build();
        $this->assertCount(2, $user->getIdentities());
        $this->assertSame('foo', $user->getIdentities()[0]->filterClaims(ClaimType::NameIdentifier)[0]->value);
        $this->assertSame('foo', $user->getPrimaryIdentity()?->filterClaims(ClaimType::NameIdentifier)[0]?->value);
    }

    public function testWithAuthenticationSchemeNameAddsItToPrimaryIdentity(): void
    {
        $user = (new PrincipalBuilder('example.com'))->withAuthenticationSchemeName('foo')
            ->build();
        $this->assertSame('foo', $user->getPrimaryIdentity()?->getAuthenticationSchemeName());
    }

    public function testWithClaimsForMultipleClaimsAddsThemToPrimaryIdentity(): void
    {
        $expectedClaims = [
            new Claim(ClaimType::Name, 'Dave', 'example.com'),
            new Claim(ClaimType::Email, 'foo@bar.com', 'example.com')
        ];
        $user = (new PrincipalBuilder('example.com'))->withClaims($expectedClaims)
            ->build();
        $this->assertSame($expectedClaims, $user->getPrimaryIdentity()?->getClaims());
    }

    public function testWithClaimsForSingleClaimsAddsItToPrimaryIdentity(): void
    {
        $expectedClaims = [
            new Claim(ClaimType::Name, 'Dave', 'example.com')
        ];
        $user = (new PrincipalBuilder('example.com'))->withClaims($expectedClaims[0])
            ->build();
        $this->assertSame($expectedClaims, $user->getPrimaryIdentity()?->getClaims());
    }
}
