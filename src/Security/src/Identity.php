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

/**
 * Defines an identity
 */
class Identity implements IIdentity
{
    /** @var array<string, list<Claim<mixed>>> The mapping of claim types to claims */
    private readonly array $claimTypesToClaims;

    /**
     * @param list<Claim<mixed>> $claims The list of claims for this identity
     * @param string|null $authenticationSchemeName The authentication scheme name used to authenticate this identity, or null if it has not been authenticated
     */
    public function __construct(array $claims = [], private ?string $authenticationSchemeName = null)
    {
        $claimTypesToClaims = [];

        foreach ($claims as $claim) {
            if (!isset($claimTypesToClaims[$claim->type])) {
                $claimTypesToClaims[$claim->type] = [];
            }

            $claimTypesToClaims[$claim->type][] = $claim;
        }

        $this->claimTypesToClaims = $claimTypesToClaims;
    }

    /**
     * @inheritdoc
     */
    public function filterClaims(ClaimType|string $type): array
    {
        return $this->claimTypesToClaims[$type instanceof ClaimType ? $type->value : $type] ?? [];
    }

    /**
     * @inheritdoc
     */
    public function getAuthenticationSchemeName(): ?string
    {
        return $this->authenticationSchemeName;
    }

    /**
     * @inheritdoc
     */
    public function getClaims(): array
    {
        $allClaims = [];

        foreach ($this->claimTypesToClaims as $claims) {
            $allClaims = [...$allClaims, ...$claims];
        }

        return $allClaims;
    }

    /**
     * @inheritdoc
     */
    public function getName(): ?string
    {
        $nameClaims = $this->filterClaims(ClaimType::Name);

        return \count($nameClaims) === 0 ? null : (string)$nameClaims[0]->value;
    }

    /**
     * @inheritdoc
     */
    public function getNameIdentifier(): ?string
    {
        $idClaims = $this->filterClaims(ClaimType::NameIdentifier);

        return \count($idClaims) === 0 ? null : (string)$idClaims[0]->value;
    }

    /**
     * @inheritdoc
     */
    public function hasClaim(ClaimType|string $type, mixed $value): bool
    {
        foreach ($this->filterClaims($type) as $claim) {
            if ($claim->value === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function isAuthenticated(): bool
    {
        return $this->authenticationSchemeName !== null;
    }

    /**
     * @inheritdoc
     */
    public function setAuthenticationSchemeName(string $authenticationSchemeName): void
    {
        $this->authenticationSchemeName = $authenticationSchemeName;
    }
}
