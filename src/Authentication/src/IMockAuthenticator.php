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

use Aphiria\Security\IPrincipal;
use Closure;

/**
 * Defines the interface for mocked authenticators to implement
 */
interface IMockAuthenticator extends IAuthenticator
{
    /**
     * Mocks the next authentication call to act as the input principal
     *
     * @template T The return type of the closure
     * @param IPrincipal $user The principal to act as for authentication calls
     * @param Closure(): T $callback The callback that will make calls as the acting principal
     * @return T The return value of the callback
     */
    public function actingAs(IPrincipal $user, Closure $callback): mixed;
}
