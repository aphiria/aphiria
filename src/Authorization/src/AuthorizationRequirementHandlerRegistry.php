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

use OutOfBoundsException;

/**
 * Defines the authorization requirement handler registry
 */
final class AuthorizationRequirementHandlerRegistry
{
    /**
     * @template T of object
     * @param array<class-string<T>, IAuthorizationRequirementHandler<T>> $requirementTypesToHandlers The requirement types to handlers
     */
    public function __construct(private array $requirementTypesToHandlers = [])
    {
    }

    /**
     * Gets a handler for a requirement
     *
     * @template T of object
     * @param class-string<T> $requirementType
     * @return IAuthorizationRequirementHandler<T>
     * @throws OutOfBoundsException Thrown if no authorization requirement handler could be found
     */
    public function getRequirementHandler(string $requirementType): IAuthorizationRequirementHandler
    {
        return $this->requirementTypesToHandlers[$requirementType] ?? throw new OutOfBoundsException("No handler registered for requirement $requirementType");
    }

    /**
     * Registers a handler for a requirement
     *
     * @template T of object
     * @param class-string<T> $requirementType
     * @param IAuthorizationRequirementHandler<T> $requirementHandler
     */
    public function registerRequirementHandler(string $requirementType, IAuthorizationRequirementHandler $requirementHandler): void
    {
        $this->requirementTypesToHandlers[$requirementType] = $requirementHandler;
    }
}
