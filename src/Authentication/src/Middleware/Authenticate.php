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
        /** @var list<string|null> $schemeNames */
        $schemeNames = $this->getParameter('schemeNames') ?? [null];
        $failedAuthenticationResults = [];

        // Default to a null scheme name if none was set
        foreach ($schemeNames as $schemeName) {
            $authenticationResult = $this->authenticator->authenticate($request, $schemeName);

            if (!$authenticationResult->passed) {
                $failedAuthenticationResults[] = $authenticationResult;
            }
        }

        // If all the schemes failed to authenticate, handle it
        if (\count($failedAuthenticationResults) === \count($schemeNames)) {
            return $this->handleFailedAuthenticationResults($request, $failedAuthenticationResults);
        }

        return $next->handle($request);
    }

    /**
     * Handles failed authentication results
     * This method can be overridden to, for example, add details about the failed authentication result to the response
     *
     * @param IRequest $request The current request
     * @param list<AuthenticationResult> $failedAuthenticationResults The list of failed authentication results
     * @return IResponse The response
     * @throws AuthenticationSchemeNotFoundException Thrown if the scheme could not be found
     */
    protected function handleFailedAuthenticationResults(IRequest $request, array $failedAuthenticationResults): IResponse
    {
        $response = new Response(HttpStatusCode::Unauthorized);

        foreach ($failedAuthenticationResults as $failedAuthenticationResult) {
            $this->authenticator->challenge($request, $response, $failedAuthenticationResult->schemeName);
        }

        return $response;
    }
}
