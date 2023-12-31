<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Security;

use Closure;
use DateTimeInterface;

/**
 * Defines a principal builder
 */
class PrincipalBuilder
{
    /** @var list<IIdentity> The list of identities to add to the principal */
    private array $identities = [];
    /** @var IdentityBuilder|null The identity builder for the primary identity if it was built in this principal builder, otherwise null */
    private ?IdentityBuilder $primaryIdentityBuilder = null;
    /** @var ?Closure(list<IIdentity>): ?IIdentity The primary identity selector */
    private ?Closure $primaryIdentitySelector = null;

    /**
     * @param string|null $defaultClaimsIssuer The default claims issuer to use if no issuer is specified for a claim in any of the identities
     */
    public function __construct(private readonly ?string $defaultClaimsIssuer = null)
    {
    }

    /**
     * Builds the principal
     *
     * @return IPrincipal The built principal
     */
    public function build(): IPrincipal
    {
        if ($this->primaryIdentityBuilder !== null) {
            $this->identities = [$this->primaryIdentityBuilder->build(), ...$this->identities];
        }

        return new User($this->identities, $this->primaryIdentitySelector);
    }

    /**
     * Adds an actor claim to the primary identity
     *
     * @param string $value The actor value
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withActor(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withActor($value, $issuer);

        return $this;
    }

    /**
     * Adds an authentication scheme name used to authenticate the primary identity
     *
     * @param string $authenticationSchemeName The authentication scheme name used to authenticate this identity
     * @return static For chaining
     */
    public function withAuthenticationSchemeName(string $authenticationSchemeName): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withAuthenticationSchemeName($authenticationSchemeName);

        return $this;
    }

    /**
     * Adds claims to the primary identity
     *
     * @param Claim|list<Claim> $claims The claim or list of claims to add
     * @return static For chaining
     */
    public function withClaims(Claim|array $claims): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withClaims($claims);

        return $this;
    }

    /**
     * Adds a country claim to the primary identity
     *
     * @param string $value The country
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withCountry(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withCountry($value, $issuer);

        return $this;
    }

    /**
     * Adds a date of birth claim to the primary identity
     *
     * @param DateTimeInterface $value The date of birth
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withDateOfBirth(DateTimeInterface $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withDateOfBirth($value, $issuer);

        return $this;
    }

    /**
     * Adds a DNS claim to the primary identity
     *
     * @param string $value The DNS
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withDns(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withDns($value, $issuer);

        return $this;
    }

    /**
     * Adds an email claim to the primary identity
     *
     * @param string $value The email
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withEmail(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withEmail($value, $issuer);

        return $this;
    }

    /**
     * Adds a gender claim to the primary identity
     *
     * @param mixed $value The gender
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withGender(mixed $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withGender($value, $issuer);

        return $this;
    }

    /**
     * Adds a given name claim to the primary identity
     *
     * @param string $value The given name
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withGivenName(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withGivenName($value, $issuer);

        return $this;
    }

    /**
     * Adds a home phone number claim to the primary identity
     *
     * @param string $value The home phone number
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withHomePhone(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withHomePhone($value, $issuer);

        return $this;
    }

    /**
     * Adds either an identity or an identity builder to the principal
     *
     * @param IIdentity|Closure(IdentityBuilder): void $identity
     * @return static For chaining
     */
    public function withIdentity(IIdentity|Closure $identity): static
    {
        if ($identity instanceof IIdentity) {
            $this->identities[] = $identity;
        } else {
            $identityBuilder = new IdentityBuilder($this->defaultClaimsIssuer);
            $identity($identityBuilder);
            $this->identities[] = $identityBuilder->build();
        }

        return $this;
    }

    /**
     * Adds a locality claim to the primary identity
     *
     * @param string $value The locality
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withLocality(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withLocality($value, $issuer);

        return $this;
    }

    /**
     * Adds a mobile phone claim to the primary identity
     *
     * @param string $value The mobile phone number
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withMobilePhone(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withMobilePhone($value, $issuer);

        return $this;
    }

    /**
     * Adds a name claim to the primary identity
     *
     * @param string $value The name
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withName(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withName($value, $issuer);

        return $this;
    }

    /**
     * Adds a name identifier claim to the primary identity
     *
     * @param mixed $value The name identifier
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withNameIdentifier(mixed $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withNameIdentifier($value, $issuer);

        return $this;
    }

    /**
     * Adds an other phone number claim to the primary identity
     *
     * @param string $value The other phone number
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withOtherPhone(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withOtherPhone($value, $issuer);

        return $this;
    }

    /**
     * Adds a postal code claim to the primary identity
     *
     * @param string|int $value The postal code
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withPostalCode(string|int $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withPostalCode($value, $issuer);

        return $this;
    }

    /**
     * Adds a primary identity selector to the principal
     *
     * @param Closure(list<IIdentity>): ?IIdentity $primaryIdentitySelector The primary identity selector
     * @return static For chaining
     */
    public function withPrimaryIdentitySelector(Closure $primaryIdentitySelector): static
    {
        $this->primaryIdentitySelector = $primaryIdentitySelector;

        return $this;
    }

    /**
     * Adds a role claim to the primary identity
     *
     * @param string|list<string> $value The role or list of roles
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withRoles(string|array $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withRoles($value, $issuer);

        return $this;
    }

    /**
     * Adds an RSA claim to the primary identity
     *
     * @param string $value The RSA
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withRsa(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withRsa($value, $issuer);

        return $this;
    }

    /**
     * Adds a SID claim to the primary identity
     *
     * @param string $value The SID
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withSid(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withSid($value, $issuer);

        return $this;
    }

    /**
     * Adds a state or province claim to the primary identity
     *
     * @param string $value The state or province
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withStateOrProvince(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withStateOrProvince($value, $issuer);

        return $this;
    }

    /**
     * Adds a street address claim to the primary identity
     *
     * @param string $value The street address
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withStreetAddress(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withStreetAddress($value, $issuer);

        return $this;
    }

    /**
     * Adds a surname claim to the primary identity
     *
     * @param string $value The surname
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withSurname(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withSurname($value, $issuer);

        return $this;
    }

    /**
     * Adds a thumbprint claim to the primary identity
     *
     * @param string $value The thumbprint
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withThumbprint(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withThumbprint($value, $issuer);

        return $this;
    }

    /**
     * Adds a UPN claim to the primary identity
     *
     * @param string $value The UPN
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withUpn(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withUpn($value, $issuer);

        return $this;
    }

    /**
     * Adds a URI claim to the primary identity
     *
     * @param string $value The URI
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withUri(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withUri($value, $issuer);

        return $this;
    }

    /**
     * Adds a X500 distinguished name claim to the primary identity
     *
     * @param string $value The X500 distinguished name
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withX500DistinguishedName(string $value, string $issuer = null): static
    {
        $this->createPrimaryIdentityBuilder()
            ->withX500DistinguishedName($value, $issuer);

        return $this;
    }

    /**
     * Creates the primary identity builder if it does not already exist
     *
     * @return IdentityBuilder The primary identity builder
     */
    private function createPrimaryIdentityBuilder(): IdentityBuilder
    {
        if ($this->primaryIdentityBuilder === null) {
            $this->primaryIdentityBuilder = new IdentityBuilder($this->defaultClaimsIssuer);
        }

        return $this->primaryIdentityBuilder;
    }
}
