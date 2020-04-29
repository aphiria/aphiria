<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Testing;

use Aphiria\Net\Http\ContentNegotiation\IMediaTypeFormatterMatcher;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatterMatcher;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\FormUrlEncodedMediaTypeFormatter;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\HtmlMediaTypeFormatter;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\PlainTextMediaTypeFormatter;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\Net\Http\Formatting\ResponseHeaderParser;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\IResponse;
use Aphiria\Serialization\TypeResolver;
use InvalidArgumentException;

/**
 * Defines assertions that can be made on HTTP responses
 */
class ResponseAssertions
{
    /** @var IMediaTypeFormatterMatcher The media type formatter matcher */
    private IMediaTypeFormatterMatcher $mediaTypeFormatterMatcher;
    /** @var ResponseHeaderParser The response header parser */
    private ResponseHeaderParser $responseHeaderParser;

    /**
     * @param IMediaTypeFormatterMatcher|null $mediaTypeFormatterMatcher The media type formatter matcher
     * @param ResponseHeaderParser|null $responseHeaderParser The response header parser
     */
    public function __construct(
        IMediaTypeFormatterMatcher $mediaTypeFormatterMatcher = null,
        ResponseHeaderParser $responseHeaderParser = null
    ) {
        $this->mediaTypeFormatterMatcher = $mediaTypeFormatterMatcher ?? new MediaTypeFormatterMatcher([
            new JsonMediaTypeFormatter(),
            new FormUrlEncodedMediaTypeFormatter(),
            new HtmlMediaTypeFormatter(),
            new PlainTextMediaTypeFormatter()
        ]);
        $this->responseHeaderParser = $responseHeaderParser ?? new ResponseHeaderParser();
    }

    /**
     * Asserts that a cookie value matches an expected value
     *
     * @param mixed $expectedValue The expected cookie value
     * @param IResponse $response The response to inspect
     * @param string $cookieName The name of the cookie to inspect
     * @throws AssertionFailedException Thrown if the assertion failed
     */
    public function assertCookieEquals($expectedValue, IResponse $response, string $cookieName): void
    {
        foreach ($this->responseHeaderParser->parseCookies($response->getHeaders()) as $cookie) {
            if ($cookie->getName() === $cookieName && $cookie->getValue() === $expectedValue) {
                return;
            }
        }

        throw new AssertionFailedException("Failed to assert that cookie $cookieName has expected value");
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
            throw new AssertionFailedException("Failed asserting that response has header $headerName");
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
    public function assertHeaderEquals($expectedValue, IResponse $response, string $headerName): void
    {
        $actualHeaderValue = null;

        if (!$response->getHeaders()->tryGet($headerName, $actualHeaderValue)) {
            throw new AssertionFailedException("No header value for $headerName is set");
        }

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
     */
    public function assertHeaderMatchesRegex(string $regex, IResponse $response, string $headerName): void
    {
        $actualHeaderValue = null;

        if (!$response->getHeaders()->tryGetFirst($headerName, $actualHeaderValue)) {
            throw new AssertionFailedException("No header value for $headerName is set");
        }

        if (\preg_match($regex, $actualHeaderValue) !== 1) {
            throw new AssertionFailedException("$actualHeaderValue does not match regex $regex");
        }
    }

    /**
     * Asserts that the parsed response body matches the expected value
     *
     * @param mixed $expectedValue The expected value
     * @param IResponse $response The response to inspect
     * @throws AssertionFailedException Thrown if the assertion failed
     * @throws SerializationException Thrown if there was an error deserializing the response body
     */
    public function assertParsedBodyEquals($expectedValue, IResponse $response): void
    {
        if ($expectedValue instanceof IBody) {
            if ($response->getBody() !== $expectedValue) {
                throw new AssertionFailedException('Failed to assert that the response body equals the expected value');
            }
        } else {
            $type = TypeResolver::resolveType($expectedValue);
            // TODO: We don't currently support doing this for responses.  How do I separate negotiating the response media type formatter to use based on the request's Accept type and based on whatever the response content type actually is?
            // TODO: Do I just create a dummy request whose Accept header is the same as the Content-Type response header?  Are there cases this wouldn't work for?
            $mediaTypeFormatterMatch = $this->mediaTypeFormatterMatcher->getBestResponseMediaTypeFormatterMatch(
                $type,
                $response
            );

            if ($mediaTypeFormatterMatch === null) {
                throw new InvalidArgumentException("No media type formatter available for $type");
            }

            $actualValue = $mediaTypeFormatterMatch->getFormatter()->readFromStream($response->getBody()->readAsStream(), $type);

            if ($actualValue !== $expectedValue) {
                throw new AssertionFailedException('Failed to assert that the response body matches the expected value');
            }
        }
    }

    /**
     * Asserts that the response status code matches the expected value
     *
     * @param int $expectedStatusCode The expected value
     * @param IResponse $response The response to inspect
     * @throws AssertionFailedException Thrown if the assertion failed
     */
    public function assertStatusCodeEquals(int $expectedStatusCode, IResponse $response): void
    {
        $actualStatusCode = $response->getStatusCode();

        if ($actualStatusCode !== $expectedStatusCode) {
            throw new AssertionFailedException("Expected status code $expectedStatusCode, got $actualStatusCode");
        }
    }
}
