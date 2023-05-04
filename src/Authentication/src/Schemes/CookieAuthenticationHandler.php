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
use Aphiria\Authentication\MissingAuthenticationDataException;
use Aphiria\Net\Http\Formatting\RequestParser;
use Aphiria\Net\Http\Formatting\ResponseFormatter;
use Aphiria\Net\Http\Headers\Cookie;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Security\IPrincipal;

/**
 * Defines an authentication scheme handler for reading auth data from cookies
 *
 * @implements ILoginAuthenticationSchemeHandler<CookieAuthenticationOptions>
 * @implements IAuthenticationSchemeHandler<CookieAuthenticationOptions>
 */
abstract class CookieAuthenticationHandler implements IAuthenticationSchemeHandler, ILoginAuthenticationSchemeHandler
{
    /**
     * @param RequestParser $requestParser The request parser to retrieve cookies with
     * @param ResponseFormatter $responseFormatter The response formatter to write cookies with
     */
    public function __construct(
        protected readonly RequestParser $requestParser = new RequestParser(),
        protected readonly ResponseFormatter $responseFormatter = new ResponseFormatter()
    ) {
    }

    /**
     * @inheritdoc
     * @param AuthenticationScheme<CookieAuthenticationOptions> $scheme
     */
    public function authenticate(IRequest $request, AuthenticationScheme $scheme): AuthenticationResult
    {
        if (($cookieValue = $this->getCookieValueFromRequest($request, $scheme)) === null) {
            return AuthenticationResult::fail(new MissingAuthenticationDataException("Cookie {$scheme->options->cookieName} not set"));
        }

        return $this->createAuthenticationResultFromCookie($cookieValue, $request, $scheme);
    }

    /**
     * @inheritdoc
     * @param AuthenticationScheme<CookieAuthenticationOptions> $scheme
     */
    public function challenge(IRequest $request, IResponse $response, AuthenticationScheme $scheme): void
    {
        if ($scheme->options->loginPagePath === null) {
            $response->setStatusCode(HttpStatusCode::Unauthorized);
        } else {
            $this->responseFormatter->redirectToUri($response, $scheme->options->loginPagePath);
        }
    }

    /**
     * @inheritdoc
     * @param AuthenticationScheme<CookieAuthenticationOptions> $scheme
     */
    public function forbid(IRequest $request, IResponse $response, AuthenticationScheme $scheme): void
    {
        if ($scheme->options->forbiddenPagePath === null) {
            $response->setStatusCode(HttpStatusCode::Forbidden);
        } else {
            $this->responseFormatter->redirectToUri($response, $scheme->options->forbiddenPagePath);
        }
    }

    /**
     * @inheritdoc
     * @param AuthenticationScheme<CookieAuthenticationOptions> $scheme
     */
    public function logIn(IPrincipal $user, IRequest $request, IResponse $response, AuthenticationScheme $scheme): void
    {
        $cookieValue = $this->createCookieValueForUser($user, $scheme);
        $this->responseFormatter->setCookie(
            $response,
            new Cookie(
                $scheme->options->cookieName,
                $cookieValue,
                $scheme->options->cookieMaxAge,
                $scheme->options->cookiePath,
                $scheme->options->cookieDomain,
                $scheme->options->cookieIsSecure,
                $scheme->options->cookieIsHttpOnly,
                $scheme->options->cookieSameSite
            )
        );
    }

    /**
     * @inheritdoc
     * @param AuthenticationScheme<CookieAuthenticationOptions> $scheme
     */
    public function logOut(IRequest $request, IResponse $response, AuthenticationScheme $scheme): void
    {
        $this->responseFormatter->deleteCookie(
            $response,
            $scheme->options->cookieName,
            $scheme->options->cookiePath,
            $scheme->options->cookieDomain,
            $scheme->options->cookieIsSecure,
            $scheme->options->cookieIsHttpOnly,
            $scheme->options->cookieSameSite
        );
    }

    /**
     * Creates an authentication result from a cookie
     *
     * @param string $cookieValue The value of the cookie
     * @param IRequest $request The current request
     * @param AuthenticationScheme<CookieAuthenticationOptions> $scheme The scheme
     * @return AuthenticationResult The authentication result
     */
    abstract protected function createAuthenticationResultFromCookie(string $cookieValue, IRequest $request, AuthenticationScheme $scheme): AuthenticationResult;

    /**
     * Creates the authentication cookie value for a user
     *
     * @param IPrincipal $user The current user
     * @param AuthenticationScheme<CookieAuthenticationOptions> $scheme The scheme
     * @return string|int|float The value of the cookie
     */
    abstract protected function createCookieValueForUser(IPrincipal $user, AuthenticationScheme $scheme): string|int|float;

    /**
     * Gets the cookie value from the request
     *
     * @param IRequest $request The request to retrieve the cookie value from
     * @param AuthenticationScheme<CookieAuthenticationOptions> $scheme The scheme
     * @return string|null The value of the cookie if one was found, otherwise null
     */
    protected function getCookieValueFromRequest(IRequest $request, AuthenticationScheme $scheme): ?string
    {
        $cookies = $this->requestParser->parseCookies($request);
        $cookieValue = null;

        if (!$cookies->tryGet($scheme->options->cookieName, $cookieValue)) {
            return null;
        }

        return $cookieValue;
    }
}
