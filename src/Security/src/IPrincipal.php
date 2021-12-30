<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Security;

/**
 * Defines the interface for principals to implement
 */
interface IPrincipal
{
    /**
     * Adds an identity
     *
     * @param IIdentity $identity The identity to add
     */
    public function addIdentity(IIdentity $identity): void;

    /**
     * Adds a list of identities
     *
     * @param list<IIdentity> $identities The list of identities to add
     */
    public function addManyIdentities(array $identities): void;

    /**
     * Gets all the claims associated with this principal
     *
     * @param ClaimType|string|null $type The claim type to filter on, or null if returning all claims
     * @return list<Claim<mixed>> The list of claims for this principal
     */
    public function getClaims(ClaimType|string $type = null): array;

    /**
     * Gets the list of identities a principal has
     *
     * @return list<IIdentity> The list of identities
     */
    public function getIdentities(): array;

    /**
     * Gets the primary identity
     *
     * @return IIdentity|null The primary identity of the principal if there is one, otherwise null
     */
    public function getPrimaryIdentity(): ?IIdentity;

    /**
     * Gets whether or not the principal has a claim
     *
     * @param ClaimType|string $type The claim type to search for
     * @param mixed $value The claim value to search for
     * @return bool True if the principal had the claim, otherwise false
     */
    public function hasClaim(ClaimType|string $type, mixed $value): bool;
}
