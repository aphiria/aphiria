<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Security\Tests;

use Aphiria\Security\Claim;
use Aphiria\Security\ClaimType;
use Aphiria\Security\IdentityBuilder;
use Closure;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class IdentityBuilderTest extends TestCase
{
    /**
     * Provides a list of claims calls, their claim types, and expected claim values
     *
     * @return list<array{Closure(IdentityBuilder): IdentityBuilder, ClaimType, mixed}> The list of claims calls
     */
    public static function provideClaimsCalls(): array
    {
        $dateOfBirth = new DateTimeImmutable();

        return [
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withActor('foo'), ClaimType::Actor, 'foo'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withCountry('foo'), ClaimType::Country, 'foo'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withDateOfBirth($dateOfBirth), ClaimType::DateOfBirth, $dateOfBirth],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withDns('foo'), ClaimType::Dns, 'foo'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withEmail('foo@bar.com'), ClaimType::Email, 'foo@bar.com'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withGender('male'), ClaimType::Gender, 'male'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withGivenName('Dave'), ClaimType::GivenName, 'Dave'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withHomePhone('5555555555'), ClaimType::HomePhone, '5555555555'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withLocality('en'), ClaimType::Locality, 'en'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withMobilePhone('5555555555'), ClaimType::MobilePhone, '5555555555'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withName('Dave'), ClaimType::Name, 'Dave'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withNameIdentifier(123), ClaimType::NameIdentifier, 123],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withOtherPhone('5555555555'), ClaimType::OtherPhone, '5555555555'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withPostalCode(90210), ClaimType::PostalCode, 90210],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withRoles('admin'), ClaimType::Role, 'admin'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withRsa('foo'), ClaimType::Rsa, 'foo'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withSid('foo'), ClaimType::Sid, 'foo'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withStateOrProvince('IL'), ClaimType::StateOrProvince, 'IL'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withStreetAddress('123 Fake St'), ClaimType::StreetAddress, '123 Fake St'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withSurname('Young'), ClaimType::Surname, 'Young'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withThumbprint('foo'), ClaimType::Thumbprint, 'foo'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withUpn('foo'), ClaimType::Upn, 'foo'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withUri('https://example.com'), ClaimType::Uri, 'https://example.com'],
            [fn (IdentityBuilder $identityBuilder): IdentityBuilder => $identityBuilder->withX500DistinguishedName('foo'), ClaimType::X500DistinguishedName, 'foo']
        ];
    }

    #[DataProvider('provideClaimsCalls')]
    public function testAddingClaimsAddsThemToIdentity(
        Closure $claimsCall,
        ClaimType $type,
        mixed $value
    ): void {
        $identityBuilder = new IdentityBuilder('example.com');
        $claimsCall($identityBuilder);
        $identity = $identityBuilder->build();
        $this->assertCount(1, $identity->filterClaims($type));
        $this->assertSame($value, $identity->filterClaims($type)[0]->value);
    }

    public function testAddingMultipleClaimObjectsAtOnceAddsThemToIdentity(): void
    {
        $claims = [
            new Claim(ClaimType::Name, 'Dave', 'example.com'),
            new Claim(ClaimType::Surname, 'Young', 'example.com')
        ];
        $identity = (new IdentityBuilder())
            ->withClaims($claims)
            ->build();
        $this->assertSame($claims, $identity->getClaims());
    }

    public function testAddingMultipleRolesAddsMultipleRoleClaims(): void
    {
        $identity = (new IdentityBuilder())
            ->withRoles(['admin', 'dev'], 'example.com')
            ->build();
        $this->assertCount(2, $identity->filterClaims(ClaimType::Role));
        $this->assertSame('admin', $identity->filterClaims(ClaimType::Role)[0]->value);
        $this->assertSame('example.com', $identity->filterClaims(ClaimType::Role)[0]->issuer);
        $this->assertSame('dev', $identity->filterClaims(ClaimType::Role)[1]->value);
        $this->assertSame('example.com', $identity->filterClaims(ClaimType::Role)[1]->issuer);
    }

    public function testAddingSingleClaimObjectAddsItToIdentity(): void
    {
        $claim = new Claim(ClaimType::Name, 'Dave', 'example.com');
        $identity = (new IdentityBuilder())
            ->withClaims($claim)
            ->build();
        $this->assertSame([$claim], $identity->getClaims());
    }

    public function testNotAddingAuthenticationSchemeNameDoesNotSetOneInIdentity(): void
    {
        $identity = (new IdentityBuilder())
            ->build();
        $this->assertNull($identity->getAuthenticationSchemeName());
    }

    #[DataProvider('provideClaimsCalls')]
    public function testNotSpecifyingDefaultClaimsIssuerOrIssuerForClaimThrowsException(
        Closure $claimsCall,
        ClaimType $type,
        mixed $value
    ): void {
        try {
            $identityBuilder = new IdentityBuilder();
            $claimsCall($identityBuilder);
            $this->fail('Failed to throw exception');
        } catch (InvalidArgumentException) {
            // Dummy assertion
            $this->assertTrue(true);
        }
    }

    public function testWithAuthenticationSchemeNameSetsAuthenticationSchemeNameInIdentity(): void
    {
        $identity = (new IdentityBuilder())
            ->withAuthenticationSchemeName('foo')
            ->build();
        $this->assertSame('foo', $identity->getAuthenticationSchemeName());
    }
}
