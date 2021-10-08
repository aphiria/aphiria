<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Testing\PhpUnit;

use Aphiria\Framework\Api\Testing\AssertionFailedException;
use Aphiria\Framework\Api\Testing\IntegrationTest;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IResponse;
use Closure;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Defines a base integration test case
 */
abstract class IntegrationTestCase extends TestCase
{
    use IntegrationTest;

    protected function setUp(): void
    {
        $this->beforeEachTest();
    }

    /**
     * Asserts that a cookie value matches an expected value
     *
     * @param mixed $expectedValue The expected cookie value
     * @param IResponse $response The response to inspect
     * @param string $cookieName The name of the cookie to inspect
     */
    public function assertCookieEquals(mixed $expectedValue, IResponse $response, string $cookieName): void
    {
        try {
            $this->responseAssertions->assertCookieEquals($expectedValue, $response, $cookieName);
            // Dummy assertion
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Asserts that a response has a cookie
     *
     * @param IResponse $response The response to inspect
     * @param string $cookieName The name of the cookie to look for
     */
    public function assertHasCookie(IResponse $response, string $cookieName): void
    {
        try {
            $this->responseAssertions->assertHasCookie($response, $cookieName);
            // Dummy assertion
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Asserts that a response has a header
     *
     * @param IResponse $response The response to inspect
     * @param string $headerName The name of the header to look for
     */
    public function assertHasHeader(IResponse $response, string $headerName): void
    {
        try {
            $this->responseAssertions->assertHasHeader($response, $headerName);
            // Dummy assertion
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Asserts that a response header equals a value
     *
     * @param mixed $expectedValue The expected header value
     * @param IResponse $response The response to inspect
     * @param string $headerName The name of the header to inspect
     */
    public function assertHeaderEquals(mixed $expectedValue, IResponse $response, string $headerName): void
    {
        try {
            $this->responseAssertions->assertHeaderEquals($expectedValue, $response, $headerName);
            // Dummy assertion
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Asserts that a header value matches a regex
     *
     * @param string $regex The regex to apply
     * @param IResponse $response The response to inspect
     * @param string $headerName The name of the header to inspect
     */
    public function assertHeaderMatchesRegex(string $regex, IResponse $response, string $headerName): void
    {
        try {
            $this->responseAssertions->assertHeaderMatchesRegex($regex, $response, $headerName);
            // Dummy assertion
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Asserts that the parsed response body matches the expected value
     *
     * @param mixed $expectedValue The expected value
     * @param IResponse $response The response to inspect
     * @throws RuntimeException Thrown if the last request is not set
     */
    public function assertParsedBodyEquals(mixed $expectedValue, IResponse $response): void
    {
        if ($this->lastRequest === null) {
            throw new RuntimeException('A request must be sent before calling ' . __METHOD__);
        }

        try {
            $this->responseAssertions->assertParsedBodyEquals($expectedValue, $this->lastRequest, $response);
            // Dummy assertion
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Asserts that the parsed response body passes a callback
     *
     * @param IResponse $response The response to inspect
     * @param string $type The type to parse the response body as
     * @param Closure(mixed): bool $callback The callback that takes in the parsed body (mixed type) and returns true if it passes, otherwise false
     * @throws RuntimeException Thrown if the last request is not set
     */
    public function assertParsedBodyPassesCallback(IResponse $response, string $type, Closure $callback): void
    {
        if ($this->lastRequest === null) {
            throw new RuntimeException('A request must be sent before calling ' . __METHOD__);
        }

        try {
            $this->responseAssertions->assertParsedBodyPassesCallback($this->lastRequest, $response, $type, $callback);
            // Dummy assertion
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Asserts that the response status code matches the expected value
     *
     * @param HttpStatusCode|int $expectedStatusCode The expected value
     * @param IResponse $response The response to inspect
     */
    public function assertStatusCodeEquals(HttpStatusCode|int $expectedStatusCode, IResponse $response): void
    {
        try {
            $this->responseAssertions->assertStatusCodeEquals($expectedStatusCode, $response);
            // Dummy assertion
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }
}
