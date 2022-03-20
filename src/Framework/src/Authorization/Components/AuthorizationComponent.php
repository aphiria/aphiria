<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Authorization\Components;

use Aphiria\Application\IComponent;
use Aphiria\Authorization\AuthorizationPolicy;
use Aphiria\Authorization\AuthorizationPolicyRegistry;
use Aphiria\Authorization\AuthorizationRequirementHandlerRegistry;
use Aphiria\Authorization\IAuthorizationRequirementHandler;
use Aphiria\DependencyInjection\IServiceResolver;

/**
 * Defines the authorization component
 */
class AuthorizationComponent implements IComponent
{
    /** @var list<AuthorizationPolicy> The list of authorization policies */
    private array $policies = [];
    /** @var array<class-string, IAuthorizationRequirementHandler<object, ?object>> The list of requirement types to instances of their handlers */
    private array $requirementHandlerTypesToHandlers = [];

    /**
     * @param IServiceResolver $serviceResolver The service resolver
     */
    public function __construct(private readonly IServiceResolver $serviceResolver)
    {
    }

    /**
     * @inheritdoc
     */
    public function build(): void
    {
        $policies = $this->serviceResolver->resolve(AuthorizationPolicyRegistry::class);

        foreach ($this->policies as $policy) {
            $policies->registerPolicy($policy);
        }

        $requirementHandlers = $this->serviceResolver->resolve(AuthorizationRequirementHandlerRegistry::class);

        foreach ($this->requirementHandlerTypesToHandlers as $requirementType => $requirementHandler) {
            $requirementHandlers->registerRequirementHandler($requirementType, $requirementHandler);
        }
    }

    /**
     * Adds a policy to the authority
     *
     * @param AuthorizationPolicy $policy The policy to add
     * @return static For chaining
     */
    public function withPolicy(AuthorizationPolicy $policy): static
    {
        $this->policies[] = $policy;

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
        $this->requirementHandlerTypesToHandlers[$requirementType] = $requirementHandler;

        return $this;
    }
}
