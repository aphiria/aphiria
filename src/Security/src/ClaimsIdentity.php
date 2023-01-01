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

/**
 * Defines a claims-based identity
 */
class ClaimsIdentity implements IIdentity
{
    /**
     * @param list<Claim<mixed>> $claims The list of claims for this identity
     * @param string|null $authenticationSchemeName The authentication scheme name used to authenticate this identity, or null if it has not been authenticated
     */
    public function __construct(private readonly array $claims, private readonly ?string $authenticationSchemeName = null)
    {
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
    public function getClaims(ClaimType|string $type = null): array
    {
        if ($type === null) {
            return $this->claims;
        }

        $claims = [];
        $stringClaimType = $type instanceof ClaimType ? $type->value : $type;

        foreach ($this->claims as $claim) {
            if ($claim->type === $stringClaimType) {
                $claims[] = $claim;
            }
        }

        return $claims;
    }

    /**
     * @inheritdoc
     */
    public function getName(): ?string
    {
        $idClaims = $this->getClaims(ClaimType::Name);

        return \count($idClaims) === 0 ? null : (string)$idClaims[0]->value;
    }

    /**
     * @inheritdoc
     */
    public function getNameIdentifier(): ?string
    {
        $idClaims = $this->getClaims(ClaimType::NameIdentifier);

        return \count($idClaims) === 0 ? null : (string)$idClaims[0]->value;
    }

    /**
     * @inheritdoc
     */
    public function hasClaim(ClaimType|string $type, mixed $value): bool
    {
        $claims = $this->getClaims($type);

        foreach ($claims as $claim) {
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
}
