<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
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
    /** @var Closure(list<IIdentity>): ?IIdentity The primary identity selector */
    private readonly Closure $primaryIdentitySelector;
    /** @var IIdentity|null The primary identity if this principal has one, otherwise null */
    private ?IIdentity $primaryIdentity = null;
    /** @var list<IIdentity> The list of identities this principal has */
    private array $identities;

    /**
     * @param list<IIdentity>|IIdentity $identities The identity or list of identities this principal has
     * @param ?Closure(list<IIdentity>): ?IIdentity $primaryIdentitySelector The primary identity selector
     */
    public function __construct(
        array|IIdentity $identities,
        Closure $primaryIdentitySelector = null
    ) {
        if (\is_array($identities)) {
            $this->identities = $identities;
        } else {
            $this->identities = [$identities];
        }

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
    public function getClaims(ClaimType|string $type = null): array
    {
        $claims = [];

        foreach ($this->identities as $identity) {
            $claims = [...$claims, ...$identity->getClaims($type)];
        }

        return $claims;
    }

    /**
     * @inheritdoc
     */
    public function getIdentities(): array
    {
        return $this->identities;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryIdentity(): ?IIdentity
    {
        return $this->primaryIdentity;
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
     * Sets the primary identity
     */
    private function setPrimaryIdentity(): void
    {
        $this->primaryIdentity = ($this->primaryIdentitySelector)($this->identities);
    }
}
