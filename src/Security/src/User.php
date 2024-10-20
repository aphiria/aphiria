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

/**
 * Defines a user principal
 */
class User implements IPrincipal
{
    /** @inheritdoc */
    public array $claims {
        get {
            $claims = [];

            foreach ($this->identities as $identity) {
                $claims = [...$claims, ...$identity->claims];
            }

            return $claims;
        }
    }
    /** @inheritdoc */
    public private(set) array $identities;
    /** @inheritdoc */
    public private(set) ?IIdentity $primaryIdentity = null;
    /** @var Closure(list<IIdentity>): ?IIdentity The primary identity selector */
    private readonly Closure $primaryIdentitySelector;

    /**
     * @param list<IIdentity>|IIdentity $identities The identity or list of identities this principal has
     * @param ?Closure(list<IIdentity>): ?IIdentity $primaryIdentitySelector The primary identity selector
     */
    public function __construct(
        array|IIdentity $identities,
        ?Closure $primaryIdentitySelector = null
    ) {
        if (\is_array($identities)) {
            $this->identities = $identities;
        } else {
            $this->identities = [$identities];
        }

        /**
         * @psalm-suppress MixedInferredReturnType The closure will always return an identity
         * @psalm-suppress MixedReturnStatement Ditto
         */
        $this->primaryIdentitySelector = $primaryIdentitySelector ?? static fn (array $identities): ?IIdentity => \count($identities) === 0 ? null : $identities[0];
        $this->setPrimaryIdentity();
    }

    /**
     * @inheritdoc
     */
    public function addIdentity(IIdentity $identity): void
    {
        $this->identities[] = $identity;
        $this->setPrimaryIdentity();
    }

    /**
     * @inheritdoc
     */
    public function addManyIdentities(array $identities): void
    {
        foreach ($identities as $identity) {
            $this->identities[] = $identity;
        }

        $this->setPrimaryIdentity();
    }

    /**
     * @inheritdoc
     */
    public function filterClaims(ClaimType|string $type): array
    {
        $claims = [];

        foreach ($this->identities as $identity) {
            $claims = [...$claims, ...$identity->filterClaims($type)];
        }

        return $claims;
    }

    /**
     * @inheritdoc
     */
    public function hasClaim(ClaimType|string $type, mixed $value): bool
    {
        foreach ($this->identities as $identity) {
            if ($identity->hasClaim($type, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function mergeIdentities(IPrincipal $user, bool $includeUnauthenticatedIdentities = false): IPrincipal
    {
        foreach ($user->identities as $identity) {
            if ($identity->isAuthenticated || $includeUnauthenticatedIdentities) {
                $this->addIdentity($identity);
            }
        }

        return $this;
    }

    /**
     * Sets the primary identity
     */
    private function setPrimaryIdentity(): void
    {
        $this->primaryIdentity = ($this->primaryIdentitySelector)($this->identities);
    }
}
