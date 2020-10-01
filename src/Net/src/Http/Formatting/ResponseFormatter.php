<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Formatting;

use Aphiria\Net\Http\Headers\Cookie;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

/**
 * Defines the HTTP response message formatter
 */
class ResponseFormatter
{
    /** @var ResponseHeaderFormatter The response header formatter to use */
    private ResponseHeaderFormatter $headerFormatter;

    /**
     * @param ResponseHeaderFormatter|null $headerFormatter The response header formatter to use, or null if using the default one
     */
    public function __construct(ResponseHeaderFormatter $headerFormatter = null)
    {
        $this->headerFormatter = $headerFormatter ?? new ResponseHeaderFormatter();
    }

    /**
     * Deletes a cookie from headers
     *
     * @param IResponse $response The response to format
     * @param string $name The name of the cookie to delete
     * @param string|null $path The path to the cookie to delete if set, otherwise null
     * @param string|null $domain The domain of the cookie to delete if set, otherwise null
     * @param bool $isSecure Whether or not the cookie to be deleted was HTTPS
     * @param bool $isHttpOnly Whether or not the cookie to be deleted was HTTP-only
     * @param string|null $sameSite The same-site setting to use if set, otherwise null
     */
    public function deleteCookie(
        IResponse $response,
        string $name,
        ?string $path = null,
        ?string $domain = null,
        bool $isSecure = false,
        bool $isHttpOnly = true,
        ?string $sameSite = null
    ): void {
        $this->headerFormatter->deleteCookie(
            $response->getHeaders(),
            $name,
            $path,
            $domain,
            $isSecure,
            $isHttpOnly,
            $sameSite
        );
    }

    /**
     * Sets a cookie in the headers
     *
     * @param IResponse $response The response to set the cookie in
     * @param Cookie $cookie The cookie to set
     */
    public function setCookie(IResponse $response, Cookie $cookie): void
    {
        $this->headerFormatter->setCookie($response->getHeaders(), $cookie);
    }

    /**
     * Sets cookies in the headers
     *
     * @param IResponse $response The response to set the cookies in
     * @param Cookie[] $cookies The cookies to set
     */
    public function setCookies(IResponse $response, array $cookies): void
    {
        $this->headerFormatter->setCookies($response->getHeaders(), $cookies);
    }

    /**
     * Sets up the response to redirect to a particular URI
     *
     * @param IResponse $response The response to format
     * @param string|Uri $uri The URI to redirect to
     * @param int $statusCode The status code
     * @throws RuntimeException Thrown if the location header's hash key could not be calculated
     */
    public function redirectToUri(IResponse $response, string|Uri $uri, int $statusCode = 302): void
    {
        if (\is_string($uri)) {
            $uriString = $uri;
        } else {
            $uriString = (string)$uri;
        }

        $response->setStatusCode($statusCode);
        $response->getHeaders()->add('Location', $uriString);
    }

    /**
     * Writes JSON to the response
     *
     * @param IResponse $response The response to write to
     * @param array $content The JSON to write
     * @throws InvalidArgumentException Thrown if the input JSON is incorrectly formatted
     * @throws RuntimeException Thrown if the content type header's hash key could not be calculated
     */
    public function writeJson(IResponse $response, array $content): void
    {
        try {
            $json = json_encode($content, JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            throw new InvalidArgumentException('Failed to JSON encode content', 0, $ex);
        }

        $response->getHeaders()->add('Content-Type', 'application/json');
        $response->setBody(new StringBody($json));
    }
}
