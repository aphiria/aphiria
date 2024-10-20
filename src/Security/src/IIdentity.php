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
 * Defines the interface for identities to implement
 */
interface IIdentity
{
    /** @var string|null The authentication scheme used to authenticate this identity */
    public ?string $authenticationSchemeName { get; set; }
    /** @var list<Claim<mixed>> The list of claims for this identity */
    public array $claims { get; }
    /** @var bool Whether or not the identity is authenticated */
    public bool $isAuthenticated { get; }
    /** @var string|null The name if one was found, otherwise null */
    public ?string $name { get; }
    /** @var string|null The name identifier if one was found, otherwise null */
    public ?string $nameIdentifier { get; }

    /**
     * Gets all claims with the input type
     *
     * @param ClaimType|string $type The claim type to filter on
     * @return list<Claim<mixed>> The list of claims for this identity with the input type
     */
    public function filterClaims(ClaimType|string $type): array;

    /**
     * Gets whether or not the identity has a claim
     *
     * @param ClaimType|string $type The claim type to search for
     * @param mixed $value The claim value to search for
     * @return bool True if the identity had the claim, otherwise false
     */
    public function hasClaim(ClaimType|string $type, mixed $value): bool;
}
