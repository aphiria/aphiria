<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Security;

use DateTimeInterface;
use InvalidArgumentException;

/**
 * Defines a principal identity builder
 */
class IdentityBuilder
{
    /** @var string|null The authentication scheme name used to authenticate this identity, or null if it has not been authenticated */
    private ?string $authenticationSchemeName = null;
    /** @var list<Claim> The list of claims to add to the principal */
    private array $claims = [];

    /**
     * @param string|null $defaultClaimsIssuer The default claims issuer to use if no issuer is specified for a claim
     */
    public function __construct(private readonly ?string $defaultClaimsIssuer = null)
    {
    }

    /**
     * Builds the identity
     *
     * @return IIdentity The built identity
     */
    public function build(): IIdentity
    {
        return new Identity($this->claims, $this->authenticationSchemeName);
    }

    /**
     * Adds an actor claim to the identity
     *
     * @param string $value The actor value
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withActor(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::Actor, $value, $issuer);

        return $this;
    }

    /**
     * Adds an authentication scheme name used to authenticate this identity
     *
     * @param string $authenticationSchemeName The authentication scheme name used to authenticate this identity
     * @return static For chaining
     */
    public function withAuthenticationSchemeName(string $authenticationSchemeName): static
    {
        $this->authenticationSchemeName = $authenticationSchemeName;

        return $this;
    }

    /**
     * Adds claims to the identity
     *
     * @param Claim|list<Claim> $claims The claim or list of claims to add
     * @return static For chaining
     */
    public function withClaims(Claim|array $claims): static
    {
        $claims = \is_array($claims) ? $claims : [$claims];
        $this->claims = [...$this->claims, ...$claims];

        return $this;
    }

    /**
     * Adds a country claim to the identity
     *
     * @param string $value The country
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withCountry(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::Country, $value, $issuer);

        return $this;
    }

    /**
     * Adds a date of birth claim to the identity
     *
     * @param DateTimeInterface $value The date of birth
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withDateOfBirth(DateTimeInterface $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::DateOfBirth, $value, $issuer);

        return $this;
    }

    /**
     * Adds a DNS claim to the identity
     *
     * @param string $value The DNS
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withDns(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::Dns, $value, $issuer);

        return $this;
    }

    /**
     * Adds an email claim to the identity
     *
     * @param string $value The email
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withEmail(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::Email, $value, $issuer);

        return $this;
    }

    /**
     * Adds a gender claim to the identity
     *
     * @param mixed $value The gender
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withGender(mixed $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::Gender, $value, $issuer);

        return $this;
    }

    /**
     * Adds a given name claim to the identity
     *
     * @param string $value The given name
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withGivenName(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::GivenName, $value, $issuer);

        return $this;
    }

    /**
     * Adds a home phone number claim to the identity
     *
     * @param string $value The home phone number
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withHomePhone(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::HomePhone, $value, $issuer);

        return $this;
    }

    /**
     * Adds a locality claim to the identity
     *
     * @param string $value The locality
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withLocality(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::Locality, $value, $issuer);

        return $this;
    }

    /**
     * Adds a mobile phone claim to the identity
     *
     * @param string $value The mobile phone number
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withMobilePhone(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::MobilePhone, $value, $issuer);

        return $this;
    }

    /**
     * Adds a name claim to the identity
     *
     * @param string $value The name
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withName(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::Name, $value, $issuer);

        return $this;
    }

    /**
     * Adds a name identifier claim to the identity
     *
     * @param mixed $value The name identifier
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withNameIdentifier(mixed $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::NameIdentifier, $value, $issuer);

        return $this;
    }

    /**
     * Adds an other phone number claim to the identity
     *
     * @param string $value The other phone number
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withOtherPhone(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::OtherPhone, $value, $issuer);

        return $this;
    }

    /**
     * Adds a postal code claim to the identity
     *
     * @param string|int $value The postal code
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withPostalCode(string|int $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::PostalCode, $value, $issuer);

        return $this;
    }

    /**
     * Adds a role claim to the identity
     *
     * @param string|list<string> $value The role or list of roles
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withRoles(string|array $value, string $issuer = null): static
    {
        foreach ((array)$value as $role) {
            $this->addClaim(ClaimType::Role, $role, $issuer);
        }

        return $this;
    }

    /**
     * Adds an RSA claim to the identity
     *
     * @param string $value The RSA
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withRsa(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::Rsa, $value, $issuer);

        return $this;
    }

    /**
     * Adds a SID claim to the identity
     *
     * @param string $value The SID
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withSid(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::Sid, $value, $issuer);

        return $this;
    }

    /**
     * Adds a state or province claim to the identity
     *
     * @param string $value The state or province
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withStateOrProvince(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::StateOrProvince, $value, $issuer);

        return $this;
    }

    /**
     * Adds a street address claim to the identity
     *
     * @param string $value The street address
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withStreetAddress(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::StreetAddress, $value, $issuer);

        return $this;
    }

    /**
     * Adds a surname claim to the identity
     *
     * @param string $value The surname
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withSurname(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::Surname, $value, $issuer);

        return $this;
    }

    /**
     * Adds a thumbprint claim to the identity
     *
     * @param string $value The thumbprint
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withThumbprint(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::Thumbprint, $value, $issuer);

        return $this;
    }

    /**
     * Adds a UPN claim to the identity
     *
     * @param string $value The UPN
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withUpn(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::Upn, $value, $issuer);

        return $this;
    }

    /**
     * Adds a URI claim to the identity
     *
     * @param string $value The URI
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withUri(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::Uri, $value, $issuer);

        return $this;
    }

    /**
     * Adds a X500 distinguished name claim to the identity
     *
     * @param string $value The X500 distinguished name
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @return static For chaining
     */
    public function withX500DistinguishedName(string $value, string $issuer = null): static
    {
        $this->addClaim(ClaimType::X500DistinguishedName, $value, $issuer);

        return $this;
    }

    /**
     * Adds a claim to the identity
     *
     * @param ClaimType $type The claim type
     * @param mixed $value The claim value
     * @param string|null $issuer The issuer of the claim, which will otherwise default to the one set in the constructor
     * @throws InvalidArgumentException Thrown if there was no claims issuer specified
     */
    protected function addClaim(ClaimType $type, mixed $value, ?string $issuer): void
    {
        if (($issuer = $issuer ?? $this->defaultClaimsIssuer) === null) {
            throw new InvalidArgumentException('No claims issuer was specified for claim type ' . $type->name);
        }

        $this->claims[] = new Claim($type, $value, $issuer);
    }
}
