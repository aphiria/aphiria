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

/**
 * Defines the authority builder
 */
class AuthorityBuilder
{
    /** @var bool Whether or not we want to continue on failure */
    private bool $continueOnFailure = true;

    /**
     * @param AuthorizationPolicyRegistry $policies The policies
     * @param AuthorizationRequirementHandlerRegistry $requirementHandlers The requirement handlers
     */
    public function __construct(
        private readonly AuthorizationPolicyRegistry $policies = new AuthorizationPolicyRegistry(),
        private readonly AuthorizationRequirementHandlerRegistry $requirementHandlers = new AuthorizationRequirementHandlerRegistry()
    ) {
    }

    /**
     * Builds the authority
     *
     * @return IAuthority The built authority
     */
    public function build(): IAuthority
    {
        return new Authority($this->policies, $this->requirementHandlers, $this->continueOnFailure);
    }

    /**
     * Sets whether or not the authority should continue after the first authorization failure
     *
     * @param bool $continueOnFailure Whether or not we want to continue after the first authorization failure
     * @return static For chaining
     */
    public function withContinueOnFailure(bool $continueOnFailure = true): static
    {
        $this->continueOnFailure = $continueOnFailure;

        return $this;
    }

    /**
     * Adds a policy to the authority
     *
     * @param AuthorizationPolicy $policy The policy to add
     * @return static For chaining
     */
    public function withPolicy(AuthorizationPolicy $policy): static
    {
        $this->policies->registerPolicy($policy);

        return $this;
    }

    /**
     * Adds a requirement handler to the authority
     *
     * @template TRequirement of object
     * @template TResource of ?object
     * @param class-string<TRequirement> $requirementType
     * @param IAuthorizationRequirementHandler<TRequirement, TResource> $requirementHandler
     * @return static For chaining
     */
    public function withRequirementHandler(string $requirementType, IAuthorizationRequirementHandler $requirementHandler): static
    {
        $this->requirementHandlers->registerRequirementHandler($requirementType, $requirementHandler);

        return $this;
    }
}
