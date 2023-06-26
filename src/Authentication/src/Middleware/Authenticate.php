<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Middleware;

use Aphiria\Authentication\AuthenticationResult;
use Aphiria\Authentication\AuthenticationSchemeNotFoundException;
use Aphiria\Authentication\IAuthenticator;
use Aphiria\Authentication\IUserAccessor;
use Aphiria\Authentication\RequestPropertyUserAccessor;
use Aphiria\Middleware\ParameterizedMiddleware;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\Response;

/**
 * Defines the authentication middleware
 */
class Authenticate extends ParameterizedMiddleware
{
    /**
     * @param IAuthenticator $authenticator The authenticator to use
     * @param IUserAccessor $userAccessor The user accessor we'll store the authenticated user in
     */
    public function __construct(
        private readonly IAuthenticator $authenticator,
        private readonly IUserAccessor $userAccessor = new RequestPropertyUserAccessor()
    ) {
    }

    /**
     * @inheritdoc
     */
    public function handle(IRequest $request, IRequestHandler $next): IResponse
    {
        // Default to a null scheme name if none was set
        /** @var list<string|null> $schemeNames */
        $schemeNames = $this->getParameter('schemeNames') ?? [null];
        $authenticationResult = $this->authenticator->authenticate($request, $schemeNames);

        if (!$authenticationResult->passed) {
            return $this->handleFailedAuthenticationResult($request, $authenticationResult);
        }

        // Persist the user so that it can be retrieved in controllers
        $this->userAccessor->setUser($authenticationResult->user, $request);

        return $next->handle($request);
    }

    /**
     * Handles a failed authentication result
     * This method can be overridden to, for example, add details about the failed authentication result to the response
     *
     * @param IRequest $request The current request
     * @param AuthenticationResult $failedAuthenticationResult The failed authentication result
     * @return IResponse The response
     * @throws AuthenticationSchemeNotFoundException Thrown if the scheme could not be found
     */
    protected function handleFailedAuthenticationResult(IRequest $request, AuthenticationResult $failedAuthenticationResult): IResponse
    {
        $response = new Response(HttpStatusCode::Unauthorized);
        $this->authenticator->challenge($request, $response, $failedAuthenticationResult->schemeNames);

        return $response;
    }
}
