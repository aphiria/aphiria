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
     * Gets all the claims with the input type associated with this principal
     *
     * @param ClaimType|string $type The claim type to filter on
     * @return list<Claim<mixed>> The list of claims for this principal with the input type
     */
    public function filterClaims(ClaimType|string $type): array;

    /**
     * Gets all the claims associated with this principal
     *
     * @return list<Claim<mixed>> The list of claims for this principal
     */
    public function getClaims(): array;

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

    /**
     * Merges another principal's identities into this one's
     *
     * @param IPrincipal $user The principal to merge
     * @param bool $includeUnauthenticatedIdentities Whether to include unauthenticated identities in the principal we're merging with
     * @return IPrincipal The merged principal
     */
    public function mergeIdentities(IPrincipal $user, bool $includeUnauthenticatedIdentities = false): IPrincipal;
}
