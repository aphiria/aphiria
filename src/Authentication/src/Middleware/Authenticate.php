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
use Aphiria\Authentication\IAuthenticator;
use Aphiria\Authentication\SchemeNotFoundException;
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
     */
    public function __construct(private readonly IAuthenticator $authenticator)
    {
    }

    /**
     * @inheritdoc
     */
    public function handle(IRequest $request, IRequestHandler $next): IResponse
    {
        /** @var string|null $schemeName */
        $schemeName = $this->getParameter('schemeName');
        $authenticationResult = $this->authenticator->authenticate($request, $schemeName);

        if (!$authenticationResult->passed) {
            return $this->handleFailedAuthenticationResult($request, $schemeName, $authenticationResult);
        }

        return $next->handle($request);
    }

    /**
     * Handles a failed authentication result
     * This method can be overridden to, for example, add details about the failed authentication result to the response
     *
     * @param IRequest $request The current request
     * @param string|null $schemeName The name of the authentication scheme that was used, or null if using the default one
     * @param AuthenticationResult $authenticationResult The failed authentication result
     * @return IResponse The response
     * @throws SchemeNotFoundException Thrown if the scheme could not be found
     */
    protected function handleFailedAuthenticationResult(IRequest $request, ?string $schemeName, AuthenticationResult $authenticationResult): IResponse
    {
        $response = new Response(HttpStatusCode::Unauthorized);
        $this->authenticator->challenge($request, $response, $schemeName);

        return $response;
    }
}
