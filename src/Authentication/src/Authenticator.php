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

use Aphiria\Authentication\Schemes\ILoginAuthenticationSchemeHandler;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Security\IPrincipal;
use OutOfBoundsException;

/**
 * Defines the default authenticator
 */
class Authenticator implements IAuthenticator
{
    /**
     * @param AuthenticationSchemeRegistry $schemes The registry of authentication schemes
     * @param IAuthenticationSchemeHandlerResolver $handlerResolver The resolver for authentication handlers
     * @param IUserAccessor $userAccessor What we'll use to access the current user
     */
    public function __construct(
        private readonly AuthenticationSchemeRegistry $schemes,
        private readonly IAuthenticationSchemeHandlerResolver $handlerResolver,
        private readonly IUserAccessor $userAccessor = new RequestPropertyUserAccessor()
    ) {
    }

    /**
     * @inheritdoc
     */
    public function authenticate(IRequest $request, string $schemeName = null): AuthenticationResult
    {
        $scheme = $this->getScheme($schemeName);
        $handler = $this->handlerResolver->resolve($scheme->handlerClassName);
        $authResult = $handler->authenticate($request, $scheme);

        if ($authResult->passed && $authResult->user !== null) {
            if (($user = $this->userAccessor->getUser($request)) instanceof IPrincipal) {
                // Merge this user with any previously-set user so that all the identities and claims are set for all schemes authenticated against
                // We store this merged identity in a new authentication result, and return that one instead
                $user->mergeIdentities($authResult->user);
                $authResult = AuthenticationResult::pass($user, $scheme->name);
            }

            $this->userAccessor->setUser($authResult->user, $request);
        }

        return $authResult;
    }

    /**
     * @inheritdoc
     */
    public function challenge(IRequest $request, IResponse $response, string $schemeName = null): void
    {
        $scheme = $this->getScheme($schemeName);
        $handler = $this->handlerResolver->resolve($scheme->handlerClassName);
        $handler->challenge($request, $response, $scheme);
    }

    /**
     * @inheritdoc
     */
    public function forbid(IRequest $request, IResponse $response, string $schemeName = null): void
    {
        $scheme = $this->getScheme($schemeName);
        $handler = $this->handlerResolver->resolve($scheme->handlerClassName);
        $handler->forbid($request, $response, $scheme);
    }

    /**
     * @inheritdoc
     */
    public function logIn(IPrincipal $user, IRequest $request, IResponse $response, string $schemeName = null): void
    {
        if (!($user->getPrimaryIdentity()?->isAuthenticated() ?? false)) {
            throw new NotAuthenticatedException('User identity must be set and authenticated to log in');
        }

        $scheme = $this->getScheme($schemeName);
        $handler = $this->handlerResolver->resolve($scheme->handlerClassName);

        if (!$handler instanceof ILoginAuthenticationSchemeHandler) {
            throw new UnsupportedAuthenticationHandlerException($handler::class . ' does not implement ' . ILoginAuthenticationSchemeHandler::class);
        }

        $handler->logIn($user, $request, $response, $scheme);
        $this->userAccessor->setUser($user, $request);
    }

    /**
     * @inheritdoc
     */
    public function logOut(IRequest $request, IResponse $response, string $schemeName = null): void
    {
        $scheme = $this->getScheme($schemeName);
        $handler = $this->handlerResolver->resolve($scheme->handlerClassName);

        if (!$handler instanceof ILoginAuthenticationSchemeHandler) {
            throw new UnsupportedAuthenticationHandlerException($handler::class . ' does not implement ' . ILoginAuthenticationSchemeHandler::class);
        }

        $handler->logOut($request, $response, $scheme);
        $this->userAccessor->setUser(null, $request);
    }

    /**
     * Gets an authentication scheme by name
     *
     * @template T of AuthenticationSchemeOptions
     * @param string|null $schemeName The name of the authentication scheme to get, or null if getting the default one
     * @return AuthenticationScheme<T> The authentication scheme
     * @throws AuthenticationSchemeNotFoundException Thrown if no scheme could be found
     */
    private function getScheme(?string $schemeName): AuthenticationScheme
    {
        if ($schemeName === null) {
            $scheme = $this->schemes->getDefaultScheme();

            if ($scheme === null) {
                throw new AuthenticationSchemeNotFoundException('No default authentication scheme found');
            }

            return $scheme;
        }

        try {
            return $this->schemes->getScheme($schemeName);
        } catch (OutOfBoundsException $ex) {
            throw new AuthenticationSchemeNotFoundException("No authentication scheme with name \"$schemeName\" found", 0, $ex);
        }
    }
}
