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
        /** @var list<string|null> $schemeNames */
        $schemeNames = $this->getParameter('schemeNames') ?? [null];
        $failedSchemeNamesToAuthenticationResults = [];

        // Default to a null scheme name if none was set
        foreach ($schemeNames as $schemeName) {
            $authenticationResult = $this->authenticator->authenticate($request, $schemeName);

            if (!$authenticationResult->passed) {
                $failedSchemeNamesToAuthenticationResults[] = [$schemeName, $authenticationResult];
            }
        }

        // If all the schemes failed to authenticate, handle it
        if (\count($failedSchemeNamesToAuthenticationResults) === \count($schemeNames)) {
            return $this->handleFailedAuthenticationResult($request, $failedSchemeNamesToAuthenticationResults);
        }

        return $next->handle($request);
    }

    /**
     * Handles a failed authentication result
     * This method can be overridden to, for example, add details about the failed authentication result to the response
     *
     * @param IRequest $request The current request
     * @param list<array{0: ?string, 1: AuthenticationResult}> $failedSchemeNamesAndAuthenticationResults The list of schemes that failed to authenticate and their authentication results
     * @return IResponse The response
     * @throws SchemeNotFoundException Thrown if the scheme could not be found
     */
    protected function handleFailedAuthenticationResult(IRequest $request, array $failedSchemeNamesAndAuthenticationResults): IResponse
    {
        $response = new Response(HttpStatusCode::Unauthorized);

        foreach ($failedSchemeNamesAndAuthenticationResults as $failedSchemeNameAndAuthenticationResult) {
            $this->authenticator->challenge($request, $response, $failedSchemeNameAndAuthenticationResult[0]);
        }

        return $response;
    }
}
