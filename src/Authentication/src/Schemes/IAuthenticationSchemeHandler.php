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

use Aphiria\Authentication\AuthenticationResult;
use Aphiria\Authentication\AuthenticationScheme;
use Aphiria\Authentication\AuthenticationSchemeOptions;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;

/**
 * Defines the interface for authentication scheme handlers to implement
 *
 * @template T of AuthenticationSchemeOptions
 */
interface IAuthenticationSchemeHandler
{
    /**
     * Attempts to authenticate a request
     *
     * @param IRequest $request The current request
     * @param AuthenticationScheme<T> $scheme The scheme that was used
     * @return AuthenticationResult The result of authentication
     */
    public function authenticate(IRequest $request, AuthenticationScheme $scheme): AuthenticationResult;

    /**
     * Challenges an unauthenticated request
     *
     * @param IRequest $request The current request
     * @param IResponse $response The current response
     * @param AuthenticationScheme<T> $scheme The scheme that was used
     */
    public function challenge(IRequest $request, IResponse $response, AuthenticationScheme $scheme): void;

    /**
     * Forbids a request from accessing a resource
     *
     * @param IRequest $request The current request
     * @param IResponse $response The current response
     * @param AuthenticationScheme<T> $scheme The scheme that was used
     */
    public function forbid(IRequest $request, IResponse $response, AuthenticationScheme $scheme): void;
}
