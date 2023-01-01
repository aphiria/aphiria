<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
     * @param RequestParser $requestParser The request parser to use
     * @param ResponseFormatter $responseFormatter The response formatter to use
     */
    public function __construct(
        private readonly ISession $session,
        private readonly SessionHandlerInterface $sessionHandler,
        private readonly int $sessionTtl,
        private readonly string $sessionCookieName,
        private readonly ?string $sessionCookiePath = null,
        private readonly ?string $sessionCookieDomain = null,
        private readonly bool $sessionCookieIsSecure = false,
        private readonly bool $sessionCookieIsHttpOnly = true,
        private readonly float $gcChance = 0.01,
        private readonly RequestParser $requestParser = new RequestParser(),
        private readonly ResponseFormatter $responseFormatter = new ResponseFormatter()
    ) {
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
            $this->session->setId((string)$requestCookies->get($this->sessionCookieName));
        } else {
            $this->session->regenerateId();
        }

        $this->sessionHandler->open('', $this->sessionCookieName);
        /** @var array<string, mixed>|false $sessionVars */
        $sessionVars = @\unserialize($this->sessionHandler->read((string)$this->session->getId()));
        $this->session->setMany($sessionVars === false ? [] : $sessionVars);

        $response = $next->handle($request);

        $this->session->ageFlashData();
        $this->sessionHandler->write((string)$this->session->getId(), \serialize($this->session->getAll()));
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
