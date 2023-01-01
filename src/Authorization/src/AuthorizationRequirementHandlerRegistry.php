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
 * Defines the authorization requirement handler registry
 */
final class AuthorizationRequirementHandlerRegistry
{
    /**
     * @template TRequirement of object
     * @template TResource of ?object
     * @param array<class-string<TRequirement>, IAuthorizationRequirementHandler<TRequirement, TResource>> $requirementTypesToHandlers The requirement types to handlers
     */
    public function __construct(private array $requirementTypesToHandlers = [])
    {
    }

    /**
     * Gets a handler for a requirement
     *
     * @template TRequirement of object
     * @template TResource of ?object
     * @param class-string<TRequirement> $requirementType
     * @return IAuthorizationRequirementHandler<TRequirement, TResource>
     * @throws OutOfBoundsException Thrown if no authorization requirement handler could be found
     */
    public function getRequirementHandler(string $requirementType): IAuthorizationRequirementHandler
    {
        return $this->requirementTypesToHandlers[$requirementType] ?? throw new OutOfBoundsException("No handler registered for requirement $requirementType");
    }

    /**
     * Registers a handler for a requirement
     *
     * @template TRequirement of object
     * @template TResource of ?object
     * @param class-string<TRequirement> $requirementType
     * @param IAuthorizationRequirementHandler<TRequirement, TResource> $requirementHandler
     */
    public function registerRequirementHandler(string $requirementType, IAuthorizationRequirementHandler $requirementHandler): void
    {
        $this->requirementTypesToHandlers[$requirementType] = $requirementHandler;
    }
}
