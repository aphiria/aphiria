<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Testing;

use Aphiria\ContentNegotiation\FailedContentNegotiationException;
use Aphiria\ContentNegotiation\IBodyDeserializer;
use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\ContentNegotiation\NegotiatedBodyDeserializer;
use Aphiria\Net\Http\Formatting\ResponseHeaderParser;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Reflection\TypeResolver;
use Closure;
use InvalidArgumentException;

/**
 * Defines assertions that can be made on HTTP responses
 */
class ResponseAssertions
{
    /**
     * @param IBodyDeserializer $bodyDeserializer The body deserializer
     * @param ResponseHeaderParser $responseHeaderParser The response header parser
     */
    public function __construct(
        private readonly IBodyDeserializer $bodyDeserializer = new NegotiatedBodyDeserializer(),
        private readonly ResponseHeaderParser $responseHeaderParser = new ResponseHeaderParser()
    ) {
    }

    /**
     * Asserts that a cookie value matches an expected value
     *
     * @param mixed $expectedValue The expected cookie value
     * @param IResponse $response The response to inspect
     * @param string $cookieName The name of the cookie to inspect
     * @throws AssertionFailedException Thrown if the assertion failed
     */
    public function assertCookieEquals(mixed $expectedValue, IResponse $response, string $cookieName): void
    {
        foreach ($this->responseHeaderParser->parseCookies($response->getHeaders()) as $cookie) {
            if ($cookie->getName() === $cookieName && $cookie->value === $expectedValue) {
                return;
            }
        }

        throw new AssertionFailedException("Failed to assert that cookie $cookieName has expected value");
    }

    /**
     * Asserts that a response unsets a cookie
     *
     * @param IResponse $response The response to inspect
     * @param string $cookieName The name of the cookie to look for
     * @throws AssertionFailedException Thrown if the assertion failed
     */
    public function assertCookieIsUnset(IResponse $response, string $cookieName): void
    {
        foreach ($this->responseHeaderParser->parseCookies($response->getHeaders()) as $cookie) {
            if (
                $cookie->getName() === $cookieName
                && ($cookie->value === '' || $cookie->value === null)
                && ($cookie->maxAge === 0 || $cookie->maxAge === null)
            ) {
                return;
            }
        }

        throw new AssertionFailedException("Failed to assert that cookie $cookieName is unset");
    }

    /**
     * Asserts that a response has a cookie
     *
     * @param IResponse $response The response to inspect
     * @param string $cookieName The name of the cookie to look for
     * @throws AssertionFailedException Thrown if the assertion failed
     */
    public function assertHasCookie(IResponse $response, string $cookieName): void
    {
        foreach ($this->responseHeaderParser->parseCookies($response->getHeaders()) as $cookie) {
            if ($cookie->getName() === $cookieName) {
                return;
            }
        }

        throw new AssertionFailedException("Failed to assert that cookie $cookieName is set");
    }

    /**
     * Asserts that a response has a header
     *
     * @param IResponse $response The response to inspect
     * @param string $headerName The name of the header to look for
     * @throws AssertionFailedException Thrown if the assertion failed
     */
    public function assertHasHeader(IResponse $response, string $headerName): void
    {
        if (!$response->getHeaders()->containsKey($headerName)) {
            throw new AssertionFailedException("Failed to assert that header $headerName is set");
        }
    }

    /**
     * Asserts that a response header equals a value
     *
     * @param mixed $expectedValue The expected header value
     * @param IResponse $response The response to inspect
     * @param string $headerName The name of the header to inspect
     * @throws AssertionFailedException Thrown if the assertion failed
     */
    public function assertHeaderEquals(mixed $expectedValue, IResponse $response, string $headerName): void
    {
        if (!$response->getHeaders()->containsKey($headerName)) {
            throw new AssertionFailedException("No header value for $headerName is set");
        }

        // If the expected value is an array, then get all the header values, otherwise grab just the first
        $actualHeaderValue = \is_array($expectedValue)
            ? $response->getHeaders()->get($headerName)
            : $response->getHeaders()->getFirst($headerName);

        if ($expectedValue !== $actualHeaderValue) {
            throw new AssertionFailedException('Expected header value ' . \print_r($expectedValue, true) . ', got ' . \print_r($actualHeaderValue, true));
        }
    }

    /**
     * Asserts that a header value matches a regex
     *
     * @param string $regex The regex to apply
     * @param IResponse $response The response to inspect
     * @param string $headerName The name of the header to inspect
     * @throws AssertionFailedException Thrown if the assertion failed
     * @throws InvalidArgumentException Thrown if the regex was empty
     */
    public function assertHeaderMatchesRegex(string $regex, IResponse $response, string $headerName): void
    {
        if (empty($regex)) {
            throw new InvalidArgumentException('Regex cannot be empty');
        }

        $actualHeaderValue = null;

        if (!$response->getHeaders()->tryGetFirst($headerName, $actualHeaderValue)) {
            throw new AssertionFailedException("No header value for $headerName is set");
        }

        if (\preg_match($regex, (string)$actualHeaderValue) !== 1) {
            throw new AssertionFailedException("$actualHeaderValue does not match regex $regex");
        }
    }

    /**
     * Asserts that the parsed response body matches the expected value
     *
     * @param mixed $expectedValue The expected value
     * @param IRequest $request The request that generated the response (used for content negotiation)
     * @param IResponse $response The response to inspect
     * @throws AssertionFailedException Thrown if the assertion failed
     */
    public function assertParsedBodyEquals(mixed $expectedValue, IRequest $request, IResponse $response): void
    {
        try {
            // Purposely not checking references here
            if ($expectedValue instanceof IBody) {
                if ($response->getBody() != $expectedValue) {
                    throw new AssertionFailedException('Failed to assert that the response body equals the expected value');
                }
            } elseif ($this->bodyDeserializer->readResponseBodyAs(TypeResolver::resolveType($expectedValue), $request, $response, ) != $expectedValue) {
                throw new AssertionFailedException('Failed to assert that the response body matches the expected value');
            }
        } catch (SerializationException | InvalidArgumentException | FailedContentNegotiationException $ex) {
            throw new AssertionFailedException('Failed to parse the response body', 0, $ex);
        }
    }

    /**
     * Asserts that the parsed response body passes a callback
     *
     * @param IRequest $request The request that generated the response (used for content negotiation)
     * @param IResponse $response The response to inspect
     * @param string $type The type to parse the response body as
     * @param Closure(mixed): bool $callback The callback that takes in the parsed body (mixed type) and returns true if it passes, otherwise false
     * @throws AssertionFailedException Thrown if the assertion failed
     */
    public function assertParsedBodyPassesCallback(IRequest $request, IResponse $response, string $type, Closure $callback): void
    {
        try {
            if (!$callback($this->bodyDeserializer->readResponseBodyAs($type, $request, $response))) {
                throw new AssertionFailedException('Failed to assert that the response body passes the callback');
            }
        } catch (SerializationException | InvalidArgumentException | FailedContentNegotiationException $ex) {
            throw new AssertionFailedException('Failed to parse the response body', 0, $ex);
        }
    }

    /**
     * Asserts that the response status code matches the expected value
     *
     * @param HttpStatusCode|int $expectedStatusCode The expected value
     * @param IResponse $response The response to inspect
     * @throws AssertionFailedException Thrown if the assertion failed
     */
    public function assertStatusCodeEquals(HttpStatusCode|int $expectedStatusCode, IResponse $response): void
    {
        $actualStatusCodeAsInt = $response->getStatusCode()->value;
        $expectedStatusCodeAsInt = \is_int($expectedStatusCode) ? $expectedStatusCode : $expectedStatusCode->value;

        if ($actualStatusCodeAsInt !== $expectedStatusCodeAsInt) {
            throw new AssertionFailedException("Expected status code $expectedStatusCodeAsInt, got $actualStatusCodeAsInt");
        }
    }
}
