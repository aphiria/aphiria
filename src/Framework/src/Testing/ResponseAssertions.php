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

use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\IResponse;

/**
 * Defines assertions that can be made on HTTP responses
 */
class ResponseAssertions
{
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
        // TODO: I think I need something to parse the Set-Cookie header
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
        // TODO: I think I need something to parse the Set-Cookie header
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
     */
    public function assertParsedBodyEquals($expectedValue, IResponse $response): void
    {
        if ($expectedValue instanceof IBody) {
            if ($response->getBody() !== $expectedValue) {
                throw new AssertionFailedException('Failed to assert that the response body equals the expected value');
            }
        } else {
            // TODO: Need to do content negotiation on the response body, and support arrays, objects, and scalars
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
