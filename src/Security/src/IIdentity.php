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
 * Defines the interface for identities to implement
 */
interface IIdentity
{
    /**
     * Gets the authentication scheme used to authenticate this identity
     *
     * @return string|null The authentication scheme, eg Bearer, Cookie, etc, or null if the identity has not been authenticated
     */
    public function getAuthenticationSchemeName(): ?string;

    /**
     * Gets all the claims associated with this identity
     *
     * @param ClaimType|string|null $type The claim type to filter on, or null if returning all claims
     * @return list<Claim<mixed>> The list of claims for this identity
     */
    public function getClaims(ClaimType|string $type = null): array;

    /**
     * A helper method around getting the name claim value
     *
     * @return string|null The name if one was found, otherwise null
     */
    public function getName(): ?string;

    /**
     * A helper method around getting the name identifier claim value
     *
     * @return string|null The name identifier if one was found, otherwise null
     */
    public function getNameIdentifier(): ?string;

    /**
     * Gets whether or not the identity has a claim
     *
     * @param ClaimType|string $type The claim type to search for
     * @param mixed $value The claim value to search for
     * @return bool True if the identity had the claim, otherwise false
     */
    public function hasClaim(ClaimType|string $type, mixed $value): bool;

    /**
     * Gets whether or not the identity has been authenticated
     *
     * @return bool True if the identity has been authenticated, otherwise false
     */
    public function isAuthenticated(): bool;
}
