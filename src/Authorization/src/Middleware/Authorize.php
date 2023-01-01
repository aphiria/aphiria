<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\Middleware;

use Aphiria\Authentication\IAuthenticator;
use Aphiria\Authentication\IUserAccessor;
use Aphiria\Authentication\RequestPropertyUserAccessor;
use Aphiria\Authentication\SchemeNotFoundException;
use Aphiria\Authorization\AuthorizationPolicy;
use Aphiria\Authorization\AuthorizationPolicyRegistry;
use Aphiria\Authorization\AuthorizationResult;
use Aphiria\Authorization\IAuthority;
use Aphiria\Middleware\ParameterizedMiddleware;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\Response;
use InvalidArgumentException;

/**
 * Defines the authorization middleware
 */
class Authorize extends ParameterizedMiddleware
{
    /**
     * @param IAuthority $authority The authority to use
     * @param IAuthenticator $authenticator The authenticator to use
     * @param AuthorizationPolicyRegistry $policies The policies to use
     * @param IUserAccessor $userAccessor The user accessor
     */
    public function __construct(
        private readonly IAuthority $authority,
        private readonly IAuthenticator $authenticator,
        private readonly AuthorizationPolicyRegistry $policies,
        private readonly IUserAccessor $userAccessor = new RequestPropertyUserAccessor()
    ) {
    }

    /**
     * @inheritdoc
     * @throws InvalidArgumentException Thrown if the middleware parameters were not correctly set or if the policy could not be found
     */
    public function handle(IRequest $request, IRequestHandler $next): IResponse
    {
        /** @var string|null $policyName */
        $policyName = $this->getParameter('policyName');
        /** @var AuthorizationPolicy|null $policy */
        $policy = $this->getParameter('policy');
        $this->validateParameters($policyName, $policy);

        if ($policyName !== null) {
            $policy = $this->policies->getPolicy($policyName);
        }

        /** @var AuthorizationPolicy $policy */
        $user = $this->userAccessor->getUser($request);

        if ($user === null || $user->getPrimaryIdentity()?->isAuthenticated() !== true) {
            return $this->handleUnauthenticatedUser($request, $policy);
        }

        $authorizationResult = $this->authority->authorize($user, $policy);

        if (!$authorizationResult->passed) {
            return $this->handleFailedAuthorizationResult($request, $policy, $authorizationResult);
        }

        return $next->handle($request);
    }

    /**
     * Handles a failed authorization result
     * This method can be overridden to, for example, add details about the failed authorization result to the response
     *
     * @param IRequest $request The current request
     * @param AuthorizationPolicy $policy The policy that was evaluated against
     * @param AuthorizationResult $authorizationResult The failed authorization result
     * @return IResponse The response
     * @throws SchemeNotFoundException Thrown if the scheme could not be found
     */
    protected function handleFailedAuthorizationResult(IRequest $request, AuthorizationPolicy $policy, AuthorizationResult $authorizationResult): IResponse
    {
        $response = new Response(HttpStatusCode::Forbidden);
        $authenticationSchemeNames = $policy->authenticationSchemeNames === null || \count($policy->authenticationSchemeNames) === 0 ? [null] : $policy->authenticationSchemeNames;

        foreach ($authenticationSchemeNames as $authenticationSchemeName) {
            $this->authenticator->forbid($request, $response, $authenticationSchemeName);
        }

        return $response;
    }

    /**
     * Handles a when a user was not authenticated before checking for authorization
     * This method can be overridden to customize the response
     *
     * @param IRequest $request The current request
     * @param AuthorizationPolicy $policy The policy that was evaluated against
     * @return IResponse The response
     * @throws SchemeNotFoundException Thrown if the scheme could not be found
     */
    protected function handleUnauthenticatedUser(IRequest $request, AuthorizationPolicy $policy): IResponse
    {
        $response = new Response(HttpStatusCode::Unauthorized);
        $authenticationSchemeNames = $policy->authenticationSchemeNames === null || \count($policy->authenticationSchemeNames) === 0 ? [null] : $policy->authenticationSchemeNames;

        foreach ($authenticationSchemeNames as $authenticationSchemeName) {
            $this->authenticator->challenge($request, $response, $authenticationSchemeName);
        }

        return $response;
    }

    /**
     * Validates the parameters on this middleware
     *
     * @param string|null $policyName The name of the policy to use
     * @param AuthorizationPolicy|null $policy The policy to use
     * @throws InvalidArgumentException Thrown if any of the parameters were invalid
     */
    private function validateParameters(?string $policyName, ?AuthorizationPolicy $policy): void
    {
        if (!($policy === null xor $policyName === null)) {
            throw new InvalidArgumentException('Either the policy name or the policy must be set');
        }
    }
}
