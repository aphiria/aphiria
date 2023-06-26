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
use Exception;
use InvalidArgumentException;
use OutOfBoundsException;

/**
 * Defines the default authenticator
 */
class Authenticator implements IAuthenticator
{
    /**
     * @param AuthenticationSchemeRegistry $schemes The registry of authentication schemes
     * @param IAuthenticationSchemeHandlerResolver $handlerResolver The resolver for authentication handlers
     */
    public function __construct(
        private readonly AuthenticationSchemeRegistry $schemes,
        private readonly IAuthenticationSchemeHandlerResolver $handlerResolver
    ) {
    }

    /**
     * @inheritdoc
     */
    public function authenticate(IRequest $request, array|string $schemeNames = null): AuthenticationResult
    {
        // This will contain resolved (ie non-null) scheme names
        $resolvedSchemeNames = [];
        // This will contain all failed authentication results' failures
        $authResultFailures = [];
        $user = $authResult = null;

        foreach (self::normalizeSchemeNames($schemeNames) as $schemeName) {
            $scheme = $this->getScheme($schemeName);
            $resolvedSchemeNames[] = $scheme->name;
            $handler = $this->handlerResolver->resolve($scheme->handlerClassName);
            $authResult = $handler->authenticate($request, $scheme);

            if ($authResult->passed) {
                if ($user instanceof IPrincipal && $authResult->user instanceof IPrincipal) {
                    // We've successfully authenticated with another scheme, so merge identities
                    $user->mergeIdentities($authResult->user);
                } else {
                    // This was the first successful authentication result
                    $user = $authResult->user;
                }
            } elseif ($authResult->failure instanceof Exception) {
                $authResultFailures[] = $authResult->failure;
            }
        }

        // Auth result will never be null, but the check below makes Psalm happy
        if ($authResult instanceof AuthenticationResult && \count($resolvedSchemeNames) === 1) {
            // Just pass back the auth result directly
            return $authResult;
        }

        if ($user === null) {
            // Authentication did not pass, so aggregate the failures
            return AuthenticationResult::fail(new AggregateAuthenticationException('All authentication schemes failed to authenticate', $authResultFailures), $resolvedSchemeNames);
        }

        return AuthenticationResult::pass($user, $resolvedSchemeNames);
    }

    /**
     * @inheritdoc
     */
    public function challenge(IRequest $request, IResponse $response, array|string $schemeNames = null): void
    {
        foreach (self::normalizeSchemeNames($schemeNames) as $schemeName) {
            $scheme = $this->getScheme($schemeName);
            $handler = $this->handlerResolver->resolve($scheme->handlerClassName);
            $handler->challenge($request, $response, $scheme);
        }
    }

    /**
     * @inheritdoc
     */
    public function forbid(IRequest $request, IResponse $response, array|string $schemeNames = null): void
    {
        foreach (self::normalizeSchemeNames($schemeNames) as $schemeName) {
            $scheme = $this->getScheme($schemeName);
            $handler = $this->handlerResolver->resolve($scheme->handlerClassName);
            $handler->forbid($request, $response, $scheme);
        }
    }

    /**
     * @inheritdoc
     */
    public function logIn(IPrincipal $user, IRequest $request, IResponse $response, array|string $schemeNames = null): void
    {
        if (!($user->getPrimaryIdentity()?->isAuthenticated() ?? false)) {
            throw new NotAuthenticatedException('User identity must be set and authenticated to log in');
        }

        foreach (self::normalizeSchemeNames($schemeNames) as $schemeName) {
            $scheme = $this->getScheme($schemeName);
            $handler = $this->handlerResolver->resolve($scheme->handlerClassName);

            if (!$handler instanceof ILoginAuthenticationSchemeHandler) {
                throw new UnsupportedAuthenticationHandlerException($handler::class . ' does not implement ' . ILoginAuthenticationSchemeHandler::class);
            }

            $handler->logIn($user, $request, $response, $scheme);
        }
    }

    /**
     * @inheritdoc
     */
    public function logOut(IRequest $request, IResponse $response, array|string $schemeNames = null): void
    {
        foreach (self::normalizeSchemeNames($schemeNames) as $schemeName) {
            $scheme = $this->getScheme($schemeName);
            $handler = $this->handlerResolver->resolve($scheme->handlerClassName);

            if (!$handler instanceof ILoginAuthenticationSchemeHandler) {
                throw new UnsupportedAuthenticationHandlerException($handler::class . ' does not implement ' . ILoginAuthenticationSchemeHandler::class);
            }

            $handler->logOut($request, $response, $scheme);
        }
    }

    /**
     * Normalizes scheme names into an array of scheme names
     *
     * @param list<string|null>|string|null $schemeNames The scheme name or names to normalize
     * @return list<string|null> The normalized scheme names
     */
    private static function normalizeSchemeNames(array|string|null $schemeNames): array
    {
        $normalizedSchemeNames = \is_array($schemeNames) ? $schemeNames : [$schemeNames];

        if (\count($normalizedSchemeNames) === 0) {
            throw new InvalidArgumentException('You must specify at least one scheme name or pass in null if using the default scheme');
        }

        return $normalizedSchemeNames;
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
