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

use Aphiria\Authentication\Schemes\IAuthenticationSchemeHandler;
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

        foreach ($this->getSchemes($schemeNames) as $scheme) {
            $resolvedSchemeNames[] = $scheme->name;
            $handler = $this->handlerResolver->resolve($scheme->handlerClassName);
            $authResult = $this->authenticateWithScheme($request, $scheme, $handler);

            if ($authResult->passed) {
                // If we've successfully authenticated before, merge identities
                $user = $user instanceof IPrincipal && $authResult->user instanceof IPrincipal
                    ? $user->mergeIdentities($authResult->user)
                    : $authResult->user;
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
        foreach ($this->getSchemes($schemeNames) as $scheme) {
            $handler = $this->handlerResolver->resolve($scheme->handlerClassName);
            $handler->challenge($request, $response, $scheme);
        }
    }

    /**
     * @inheritdoc
     */
    public function forbid(IRequest $request, IResponse $response, array|string $schemeNames = null): void
    {
        foreach ($this->getSchemes($schemeNames) as $scheme) {
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

        foreach ($this->getSchemes($schemeNames) as $scheme) {
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
        foreach ($this->getSchemes($schemeNames) as $scheme) {
            $handler = $this->handlerResolver->resolve($scheme->handlerClassName);

            if (!$handler instanceof ILoginAuthenticationSchemeHandler) {
                throw new UnsupportedAuthenticationHandlerException($handler::class . ' does not implement ' . ILoginAuthenticationSchemeHandler::class);
            }

            $handler->logOut($request, $response, $scheme);
        }
    }

    /**
     * Authenticates against a scheme
     * Note: This is protected so that it can be overridden in integration tests
     *
     * @param IRequest $request The current request
     * @param AuthenticationScheme $scheme The scheme being authenticated against
     * @param IAuthenticationSchemeHandler $schemeHandler The scheme handler to authenticate with
     * @return AuthenticationResult The authentication result
     */
    protected function authenticateWithScheme(
        IRequest $request,
        AuthenticationScheme $scheme,
        IAuthenticationSchemeHandler $schemeHandler
    ): AuthenticationResult {
        return $schemeHandler->authenticate($request, $scheme);
    }

    /**
     * Gets authentication schemes by name
     *
     * @template T of AuthenticationSchemeOptions
     * @param list<string|null>|string|null $schemeNames The scheme name or names to retrieve
     * @return list<AuthenticationScheme<T>> The authentication schemes
     * @throws AuthenticationSchemeNotFoundException Thrown if a scheme could not be found
     * @throws InvalidArgumentException Thrown if the list of scheme names was empty
     */
    private function getSchemes(array|string|null $schemeNames): array
    {
        $normalizedSchemeNames = \is_array($schemeNames) ? $schemeNames : [$schemeNames];

        if (\count($normalizedSchemeNames) === 0) {
            throw new InvalidArgumentException('You must specify at least one scheme name or pass in null if using the default scheme');
        }

        $schemes = [];

        foreach ($normalizedSchemeNames as $schemeName) {
            if ($schemeName === null) {
                $scheme = $this->schemes->getDefaultScheme();

                if ($scheme === null) {
                    throw new AuthenticationSchemeNotFoundException('No default authentication scheme found');
                }

                $schemes[] = $scheme;
            } else {
                try {
                    $schemes[] = $this->schemes->getScheme($schemeName);
                } catch (OutOfBoundsException $ex) {
                    throw new AuthenticationSchemeNotFoundException("No authentication scheme with name \"$schemeName\" found", 0, $ex);
                }
            }
        }

        return $schemes;
    }
}
