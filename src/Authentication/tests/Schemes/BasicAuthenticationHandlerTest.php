<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Tests\Schemes;

use Aphiria\Authentication\AuthenticationResult;
use Aphiria\Authentication\AuthenticationScheme;
use Aphiria\Authentication\Schemes\BasicAuthenticationHandler;
use Aphiria\Authentication\Schemes\BasicAuthenticationOptions;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Security\IPrincipal;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Issue\UndefinedPropertyAssignment;
use RuntimeException;

class BasicAuthenticationHandlerTest extends TestCase
{
    private BasicAuthenticationHandler $schemeHandler;

    protected function setUp(): void
    {
        $this->schemeHandler = new class () extends BasicAuthenticationHandler {
            public ?string $actualUsername = null;
            public ?string $actualPassword = null;
            public ?IRequest $actualRequest = null;
            public ?AuthenticationScheme $actualScheme = null;
            public ?AuthenticationResult $expectedResult = null;

            protected function createAuthenticationResultFromCredentials(string $username, string $password, IRequest $request, AuthenticationScheme $scheme): AuthenticationResult
            {
                if ($this->expectedResult === null) {
                    throw new RuntimeException('Expected result not set');
                }

                $this->actualUsername = $username;
                $this->actualPassword = $password;
                $this->actualRequest = $request;
                $this->actualScheme = $scheme;

                return $this->expectedResult;
            }
        };
    }

    public static function getChallengeSchemesAndWwwAuthenticateHeaderValues(): array
    {
        $schemeHandler = new class () extends BasicAuthenticationHandler {
            protected function createAuthenticationResultFromCredentials(
                string $username,
                string $password,
                IRequest $request,
                AuthenticationScheme $scheme
            ): AuthenticationResult {
                return AuthenticationResult::fail('foo', $scheme->name);
            }
        };

        return [
            [new AuthenticationScheme('foo', $schemeHandler::class, new BasicAuthenticationOptions()), 'Basic'],
            [new AuthenticationScheme('foo', $schemeHandler::class, new BasicAuthenticationOptions(realm: 'example.com')), 'Basic realm="example.com"']
        ];
    }

    public static function getInvalidBasicAuthorizationValues(): array
    {
        return [
            ['NotBasic ' . \base64_encode('foo:bar'), 'Request did not use basic authentication'],
            ['Basic', 'Authorization header value was invalid'],
            ['Basic ' . \base64_encode('foo:bar') . ' baz', 'Authorization header value was invalid'],
            ['Basic ===', 'Authorization header did not contain valid base64-encoded value'],
            ['Basic foo', 'Authorization header did not contain a base64-encoded username:password value']
        ];
    }

    public static function getValidBasicAuthorizationValues(): array
    {
        return [
            ['BASIC ' . \base64_encode('foo:bar')],
            ['Basic ' . \base64_encode('foo:bar')],
            ['basic ' . \base64_encode('foo:bar')],
            [' basic ' . \base64_encode('foo:bar')],
            ['basic ' . \base64_encode('foo:bar') . ' ']
        ];
    }

    /**
     * @param string $authorizationHeaderValue The invalid authorization header value
     * @param string $expectedFailureMessage The expected failure exception's message
     */
    #[DataProvider('getInvalidBasicAuthorizationValues')]
    public function testAuthenticatingWithInvalidAuthorizationHeaderReturnsFailedResult(string $authorizationHeaderValue, string $expectedFailureMessage): void
    {
        $headers = new Headers();
        $request = $this->createMock(IRequest::class);
        $request->method('getHeaders')
            ->willReturn($headers);
        $headers->add('Authorization', $authorizationHeaderValue);
        $scheme = new AuthenticationScheme('foo', $this->schemeHandler::class, new BasicAuthenticationOptions());
        $result = $this->schemeHandler->authenticate($request, $scheme);
        $this->assertFalse($result->passed);
        $this->assertSame($expectedFailureMessage, $result->failure?->getMessage());
    }

    public function testAuthenticatingWithoutAuthorizationHeaderReturnsFailedResult(): void
    {
        $headers = new Headers();
        $request = $this->createMock(IRequest::class);
        $request->method('getHeaders')
            ->willReturn($headers);
        $scheme = new AuthenticationScheme('foo', $this->schemeHandler::class, new BasicAuthenticationOptions());
        $result = $this->schemeHandler->authenticate($request, $scheme);
        $this->assertFalse($result->passed);
        $this->assertSame('Missing authorization header', $result->failure?->getMessage());
    }

    /**
     * @param string $authorizationHeaderValue The authorization header value to test
     * @psalm-suppress UndefinedPropertyAssignment The properties do actually exist on the anonymous class
     * @psalm-suppress UndefinedPropertyFetch Ditto
     */
    #[DataProvider('getValidBasicAuthorizationValues')]
    public function testAuthenticatingWithValidBase64CredentialsReturnsPassingResult(string $authorizationHeaderValue): void
    {
        $headers = new Headers();
        $request = $this->createMock(IRequest::class);
        $request->method('getHeaders')
            ->willReturn($headers);
        $headers->add('Authorization', $authorizationHeaderValue);
        $this->schemeHandler->expectedResult = AuthenticationResult::pass($this->createMock(IPrincipal::class), 'foo');
        $scheme = new AuthenticationScheme('foo', $this->schemeHandler::class, new BasicAuthenticationOptions());
        $this->assertSame($this->schemeHandler->expectedResult, $this->schemeHandler->authenticate($request, $scheme));
        $this->assertSame('foo', $this->schemeHandler->actualUsername);
        $this->assertSame('bar', $this->schemeHandler->actualPassword);
        $this->assertSame($request, $this->schemeHandler->actualRequest);
        $this->assertSame($scheme, $this->schemeHandler->actualScheme);
    }

    /**
     * @param AuthenticationScheme<BasicAuthenticationOptions> $scheme The scheme we're challenging
     * @param string $expectedWwwAuthenticateHeaderValue The expected Www-Authenticate header value
     */
    #[DataProvider('getChallengeSchemesAndWwwAuthenticateHeaderValues')]
    public function testChallengeSetsWwwAuthenticateResponseHeader(AuthenticationScheme $scheme, string $expectedWwwAuthenticateHeaderValue): void
    {
        $headers = new Headers();
        $response = $this->createMock(IResponse::class);
        $response->method('getHeaders')
            ->willReturn($headers);
        $this->schemeHandler->challenge($this->createMock(IRequest::class), $response, $scheme);
        $this->assertSame($expectedWwwAuthenticateHeaderValue, $headers->getFirst('Www-Authenticate'));
    }

    public function testForbidSetsForbiddenStatusCode(): void
    {
        $response = $this->createMock(IResponse::class);
        $response->expects($this->once())
            ->method('setStatusCode')
            ->with(HttpStatusCode::Forbidden);
        $scheme = new AuthenticationScheme('foo', $this->schemeHandler::class, new BasicAuthenticationOptions());
        $this->schemeHandler->forbid($this->createMock(IRequest::class), $response, $scheme);
    }
}
