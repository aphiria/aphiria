<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions\Middleware;

use Aphiria\Middleware\IMiddleware;
use Aphiria\Net\Http\Formatting\RequestParser;
use Aphiria\Net\Http\Formatting\ResponseFormatter;
use Aphiria\Net\Http\Headers\Cookie;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use Aphiria\Sessions\ISession;
use SessionHandlerInterface;

/**
 * Defines the middleware that handles sessions
 */
final class Session implements IMiddleware
{
    /** @var ISession The session to store data in */
    private ISession $session;
    /** @var SessionHandlerInterface The session handler */
    private SessionHandlerInterface $sessionHandler;
    /** @var int The TTL for sessions (cookies + server-side storage) in seconds */
    private int $sessionTtl;
    /** @var string The name of the session cookie to use */
    private string $sessionCookieName;
    /** @var string|null The path the cookie is valid on */
    private ?string $sessionCookiePath;
    /** @var string|null The domain the cookie is valid on */
    private ?string $sessionCookieDomain;
    /** @var bool Whether or not the cookie is only sent over HTTPS */
    private bool $sessionCookieIsSecure;
    /** @var bool Whether or not the cookie is HTTP-only (eg not readable by JavaScript) */
    private bool $sessionCookieIsHttpOnly;
    /** @var float The chance (0-1) of GC happening on this request */
    private float $gcChance;
    /** @var RequestParser The request parser for reading cookies with */
    private RequestParser $requestParser;
    /** @var ResponseFormatter The response formatter for writing cookies with */
    private ResponseFormatter $responseFormatter;

    /**
     * @param ISession $session The session to store data in
     * @param SessionHandlerInterface $sessionHandler The session handler
     * @param int $sessionTtl The TTL for sessions (cookies + server-side storage) in seconds
     * @param string $sessionCookieName The name of the session cookie to use
     * @param string|null $sessionCookiePath The path the cookie is valid on
     * @param string|null $sessionCookieDomain The domain the cookie is valid on
     * @param bool $sessionCookieIsSecure Whether or not the cookie is only sent over HTTPS
     * @param bool $sessionCookieIsHttpOnly Whether or not the cookie is HTTP-only (eg not readable by JavaScript)
     * @param float $gcChance The chance (0-1) of GC happening on this request
     * @param RequestParser|null $requestParser The request parser to use, or null if using the default
     * @param ResponseFormatter|null $responseFormatter The response formatter to use, or null if using the default
     */
    public function __construct(
        ISession $session,
        SessionHandlerInterface $sessionHandler,
        int $sessionTtl,
        string $sessionCookieName,
        string $sessionCookiePath = null,
        string $sessionCookieDomain = null,
        bool $sessionCookieIsSecure = false,
        bool $sessionCookieIsHttpOnly = true,
        float $gcChance = 0.01,
        RequestParser $requestParser = null,
        ResponseFormatter $responseFormatter = null
    ) {
        $this->session = $session;
        $this->sessionHandler = $sessionHandler;
        $this->sessionTtl = $sessionTtl;
        $this->sessionCookieName = $sessionCookieName;
        $this->sessionCookiePath = $sessionCookiePath;
        $this->sessionCookieDomain = $sessionCookieDomain;
        $this->sessionCookieIsSecure = $sessionCookieIsSecure;
        $this->sessionCookieIsHttpOnly = $sessionCookieIsHttpOnly;
        $this->gcChance = $gcChance;
        $this->requestParser = $requestParser ?? new RequestParser();
        $this->responseFormatter = $responseFormatter ?? new ResponseFormatter();
    }

    /**
     * @inheritdoc
     */
    public function handle(IRequest $request, IRequestHandler $next): IResponse
    {
        if (\random_int(0, 100) / 100 < $this->gcChance) {
            $this->sessionHandler->gc($this->sessionTtl);
        }

        $requestCookies = $this->requestParser->parseCookies($request);

        if ($requestCookies->containsKey($this->sessionCookieName)) {
            $this->session->setId($requestCookies->get($this->sessionCookieName));
        } else {
            $this->session->regenerateId();
        }

        $this->sessionHandler->open(null, $this->sessionCookieName);
        $sessionVars = @\unserialize($this->sessionHandler->read($this->session->getId()));
        $this->session->setMany($sessionVars === false ? [] : $sessionVars);

        $response = $next->handle($request);

        $this->session->ageFlashData();
        $this->sessionHandler->write($this->session->getId(), \serialize($this->session->getAll()));
        $this->writeSessionToResponse($response);

        return $response;
    }

    /**
     * Writes the current session to the response
     *
     * @param IResponse $response The response to write to
     */
    protected function writeSessionToResponse(IResponse $response): void
    {
        $this->responseFormatter->setCookie(
            $response,
            new Cookie(
                $this->sessionCookieName,
                $this->session->getId(),
                $this->sessionTtl,
                $this->sessionCookiePath,
                $this->sessionCookieDomain,
                $this->sessionCookieIsSecure,
                $this->sessionCookieIsHttpOnly
            )
        );
    }
}
