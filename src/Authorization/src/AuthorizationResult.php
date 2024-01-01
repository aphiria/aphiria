<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization;

/**
 * Defines an authorization result
 *
 * @psalm-consistent-constructor
 */
readonly class AuthorizationResult
{
    /**
     * @param bool $passed Whether or not the authorization was successful
     * @param string $policyName The name of the policy used
     * @param list<object> $failedRequirements The list of requirements that failed
     */
    protected function __construct(
        public bool $passed,
        public string $policyName,
        public array $failedRequirements = []
    ) {
    }

    /**
     * Creates a failed authorization result
     *
     * @param string $policyName The name of the policy used
     * @param list<object> $failedRequirements The list of requirements that failed, or nothing if we're explicitly failing
     * @return static A failed authorization result
     */
    public static function fail(string $policyName, array $failedRequirements = []): static
    {
        return new static(false, $policyName, $failedRequirements);
    }

    /**
     * Creates a passing authorization result
     *
     * @param string $policyName The name of the policy used
     * @return static A passing authorization result
     */
    public static function pass(string $policyName): static
    {
        return new static(true, $policyName);
    }
}
