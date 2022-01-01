<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication;

use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Security\IPrincipal;

/**
 * Defines the interface for authenticators to implement
 */
interface IAuthenticator
{
    /**
     * Attempts to authenticate a request
     *
     * @param IRequest $request The current request
     * @param string|null $schemeName The name of the authentication scheme to use, or null if using the default one
     * @return AuthenticationResult The result of authentication
     * @throws SchemeNotFoundException Thrown if no scheme could be found
     */
    public function authenticate(IRequest $request, string $schemeName = null): AuthenticationResult;

    /**
     * Challenges an unauthenticated request
     *
     * @param IRequest $request The current request
     * @param IResponse $response The current response
     * @param string|null $schemeName The name of the authentication scheme to use, or null if using the default one
     * @throws SchemeNotFoundException Thrown if no scheme could be found
     */
    public function challenge(IRequest $request, IResponse $response, string $schemeName = null): void;

    /**
     * Forbids a request from accessing a resource
     *
     * @param IRequest $request The current request
     * @param IResponse $response The current response
     * @param string|null $schemeName The name of the authentication scheme to use, or null if using the default one
     * @throws SchemeNotFoundException Thrown if no scheme could be found
     */
    public function forbid(IRequest $request, IResponse $response, string $schemeName = null): void;

    /**
     * Logs in a user
     *
     * @param IPrincipal $user The user to log in
     * @param IRequest $request The current request
     * @param IResponse $response The current response
     * @param string|null $schemeName The name of the authentication scheme used, or null if using the default one
     * @throws SchemeNotFoundException Thrown if no scheme could be found
     * @throws NotAuthenticatedException Thrown if the user's primary identity was not authenticated or set
     * @throws UnsupportedAuthenticationHandlerException Thrown if the scheme's handler does not support login
     */
    public function logIn(IPrincipal $user, IRequest $request, IResponse $response, string $schemeName = null): void;

    /**
     * Logs out a user
     *
     * @param IRequest $request The current request
     * @param IResponse $response The current response
     * @param string|null $schemeName The name of the authentication scheme used, or null if using the default one
     * @throws SchemeNotFoundException Thrown if no scheme could be found
     * @throws UnsupportedAuthenticationHandlerException Thrown if the scheme's handler does not support login
     */
    public function logOut(IRequest $request, IResponse $response, string $schemeName = null): void;
}
