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

use Aphiria\Security\IPrincipal;
use InvalidArgumentException;

/**
 * Defines the interface for authorization requirement handlers to implement
 *
 * @template TRequirement of object
 * @template TResource of ?object
 */
interface IAuthorizationRequirementHandler
{
    /**
     * Handles an authorization requirement
     *
     * @param IPrincipal $user The user to authorize
     * @param TRequirement $requirement The requirement to handle
     * @param AuthorizationContext<TResource> $authorizationContext The current authorization context
     * @throws InvalidArgumentException Thrown if the requirement was of the incorrect type
     */
    public function handle(IPrincipal $user, object $requirement, AuthorizationContext $authorizationContext): void;
}
