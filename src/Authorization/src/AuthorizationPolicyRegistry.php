<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization;

use OutOfBoundsException;

/**
 * Defines the authorization policy registry
 */
final class AuthorizationPolicyRegistry
{
    /** @var array<string, AuthorizationPolicy> $policyNamesToPolicies The mapping of policy names to policies */
    private array $policyNamesToPolicies = [];

    /**
     * Gets the policy with a particular name
     *
     * @param string $policyName The name of the policy to get
     * @return AuthorizationPolicy The policy with the input name
     * @throws OutOfBoundsException Thrown if no authorization policy could be found
     */
    public function getPolicy(string $policyName): AuthorizationPolicy
    {
        return $this->policyNamesToPolicies[$policyName] ?? throw new OutOfBoundsException("No policy with name \"$policyName\" found");
    }

    /**
     * Registers a policy
     *
     * @param AuthorizationPolicy $policy The policy to register
     */
    public function registerPolicy(AuthorizationPolicy $policy): void
    {
        $this->policyNamesToPolicies[$policy->name] = $policy;
    }
}
