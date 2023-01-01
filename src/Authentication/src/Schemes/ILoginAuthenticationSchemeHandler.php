<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Schemes;

use Aphiria\Authentication\AuthenticationScheme;
use Aphiria\Authentication\AuthenticationSchemeOptions;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Security\IPrincipal;

/**
 * Defines the interface to implement for authentication scheme handlers that can also log in users
 *
 * @template T of AuthenticationSchemeOptions
 * @extends IAuthenticationSchemeHandler<T>
 */
interface ILoginAuthenticationSchemeHandler extends IAuthenticationSchemeHandler
{
    /**
     * Logs in a user
     *
     * @param IPrincipal $user The user to log in
     * @param IRequest $request The current request
     * @param IResponse $response The current response
     * @param AuthenticationScheme<T> $scheme The authentication scheme used
     */
    public function logIn(IPrincipal $user, IRequest $request, IResponse $response, AuthenticationScheme $scheme): void;

    /**
     * Logs out a user
     *
     * @param IRequest $request The current request
     * @param IResponse $response The current response
     * @param AuthenticationScheme<T> $scheme The authentication scheme used
     */
    public function logOut(IRequest $request, IResponse $response, AuthenticationScheme $scheme): void;
}
