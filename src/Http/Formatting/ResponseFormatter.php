<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Formatting;

use Aphiria\Net\Http\Cookie;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the HTTP response message formatter
 */
class ResponseFormatter
{
    /** @var ResponseHeaderFormatter The response header formatter to use */
    private ?ResponseHeaderFormatter $headerFormatter;

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
     * @param IHttpResponseMessage $response The response to format
     * @param string $name The name of the cookie to delete
     * @param string|null $path The path to the cookie to delete if set, otherwise null
     * @param string|null $domain The domain of the cookie to delete if set, otherwise null
     * @param bool $isSecure Whether or not the cookie to be deleted was HTTPS
     * @param bool $isHttpOnly Whether or not the cookie to be deleted was HTTP-only
     * @param string|null $sameSite The same-site setting to use if set, otherwise null
     */
    public function deleteCookie(
        IHttpResponseMessage $response,
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
     * @param IHttpResponseMessage $response The response to set the cookie in
     * @param Cookie $cookie The cookie to set
     */
    public function setCookie(IHttpResponseMessage $response, Cookie $cookie): void
    {
        $this->headerFormatter->setCookie($response->getHeaders(), $cookie);
    }

    /**
     * Sets cookies in the headers
     *
     * @param IHttpResponseMessage $response The response to set the cookies in
     * @param Cookie[] $cookies The cookies to set
     */
    public function setCookies(IHttpResponseMessage $response, array $cookies): void
    {
        $this->headerFormatter->setCookies($response->getHeaders(), $cookies);
    }

    /**
     * Sets up the response to redirect to a particular URI
     *
     * @param IHttpResponseMessage $response The response to format
     * @param Uri|string $uri The URI to redirect to
     * @param int $statusCode The status code
     * @throws InvalidArgumentException Thrown if the URI is not an instance of URI or a string
     * @throws RuntimeException Thrown if the location header's hash key could not be calculated
     */
    public function redirectToUri(IHttpResponseMessage $response, $uri, int $statusCode = 302): void
    {
        if (is_string($uri)) {
            $uriString = $uri;
        } elseif ($uri instanceof Uri) {
            $uriString = (string)$uri;
        } else {
            throw new InvalidArgumentException('Uri must be instance of ' . Uri::class . ' or string');
        }

        $response->setStatusCode($statusCode);
        $response->getHeaders()->add('Location', $uriString);
    }

    /**
     * Writes JSON to the response
     *
     * @param IHttpResponseMessage $response The response to write to
     * @param array $content The JSON to write
     * @throws InvalidArgumentException Thrown if the input JSON is incorrectly formatted
     * @throws RuntimeException Thrown if the content type header's hash key could not be calculated
     */
    public function writeJson(IHttpResponseMessage $response, array $content): void
    {
        $json = json_encode($content);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Failed to JSON encode content: ' . json_last_error_msg());
        }

        $response->getHeaders()->add('Content-Type', 'application/json');
        $response->setBody(new StringBody($json));
    }
}
