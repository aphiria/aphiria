<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization;

/**
 * Defines an authorization result
 *
 * @psalm-consistent-constructor
 */
class AuthorizationResult
{
    /**
     * @param bool $passed Whether or not the authorization was successful
     * @param list<object> $failedRequirements The list of requirements that failed
     */
    public function __construct(
        public readonly bool $passed,
        public readonly array $failedRequirements = []
    ) {
    }

    /**
     * Creates a failed authorization result
     *
     * @param list<object> $failedRequirements The list of requirements that failed, or nothing if we're explicitly failing
     * @return static A failed authorization result
     */
    public static function fail(array $failedRequirements = []): static
    {
        return new static(false, $failedRequirements);
    }

    /**
     * Creates a passing authorization result
     *
     * @return static A passing authorization result
     */
    public static function pass(): static
    {
        return new static(true);
    }
}
