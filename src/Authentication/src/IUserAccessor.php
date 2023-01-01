<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication;

use Aphiria\Net\Http\IRequest;
use Aphiria\Security\IPrincipal;

/**
 * Defines the interface for user accessors to implement
 */
interface IUserAccessor
{
    /**
     * Gets the current user from the request
     *
     * @param IRequest $request The current request
     * @return IPrincipal|null The user if one was found, otherwise null
     */
    public function getUser(IRequest $request): ?IPrincipal;

    /**
     * Sets the current user in the request
     *
     * @param IPrincipal|null $user The current user, or null if there is none
     * @param IRequest $request The current request
     */
    public function setUser(?IPrincipal $user, IRequest $request): void;
}
