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

/**
 * Defines the interface for authorities to implement
 */
interface IAuthority
{
    /**
     * Authorizes a user against a policy
     *
     * @template TResource of ?object
     * @param IPrincipal $user The user being authorized
     * @param AuthorizationPolicy|string $policy The policy or name of the policy to authorize against
     * @param TResource $resource The resource whose use is being authorized, or null if not authorizing the use of a resource
     * @return AuthorizationResult The result of authorization
     * @throws PolicyNotFoundException Thrown if the policy could not be found
     * @throws RequirementHandlerNotFoundException Thrown if the requirement handler could not be found
     */
    public function authorize(IPrincipal $user, AuthorizationPolicy|string $policy, object $resource = null): AuthorizationResult;
}
