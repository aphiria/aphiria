<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
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
            public ?AuthenticationScheme $actualScheme = null;
            public ?AuthenticationResult $expectedResult = null;

            protected function createAuthenticationResultFromCredentials(string $username, string $password, AuthenticationScheme $scheme): AuthenticationResult
            {
                if ($this->expectedResult === null) {
                    throw new RuntimeException('Expected result not set');
                }

                $this->actualUsername = $username;
                $this->actualPassword = $password;
                $this->actualScheme = $scheme;

                return $this->expectedResult;
            }
        };
    }

    public function getChallengeSchemesAndWwwAuthenticateHeaderValues(): array
    {
        /** @var BasicAuthenticationHandler $schemeHandler */
        $schemeHandler = $this->createMock(BasicAuthenticationHandler::class);

        /** @psalm-suppress InvalidCast https://github.com/vimeo/psalm/issues/8810 - bug */
        return [
            [new AuthenticationScheme('foo', $schemeHandler::class, new BasicAuthenticationOptions()), 'Basic'],
            [new AuthenticationScheme('foo', $schemeHandler::class, new BasicAuthenticationOptions(realm: 'example.com')), 'Basic realm="example.com"']
        ];
    }

    public function getInvalidBasicAuthorizationValues(): array
    {
        return [
            ['NotBasic ' . \base64_encode('foo:bar'), 'Request did not use basic authentication'],
            ['Basic', 'Authorization header value was invalid'],
            ['Basic ' . \base64_encode('foo:bar') . ' baz', 'Authorization header value was invalid'],
            ['Basic ===', 'Authorization header did not contain valid base64-encoded value'],
            ['Basic foo', 'Authorization header did not contain a base64-encoded username:password value']
        ];
    }

    public function getValidBasicAuthorizationValues(): array
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
     * @dataProvider getValidBasicAuthorizationValues
     *
     * @param string $authorizationHeaderValue The authorization header value to test
     * @psalm-suppress UndefinedPropertyAssignment The properties do actually exist on the anonymous class
     * @psalm-suppress UndefinedPropertyFetch Ditto
     */
    public function testAuthenticatingWithValidBase64CredentialsReturnsPassingResult(string $authorizationHeaderValue): void
    {
        $headers = new Headers();
        $request = $this->createMock(IRequest::class);
        $request->method('getHeaders')
            ->willReturn($headers);
        $headers->add('Authorization', $authorizationHeaderValue);
        $this->schemeHandler->expectedResult = AuthenticationResult::pass($this->createMock(IPrincipal::class));
        /** @psalm-suppress InvalidCast https://github.com/vimeo/psalm/issues/8810 - bug */
        $scheme = new AuthenticationScheme('foo', $this->schemeHandler::class, new BasicAuthenticationOptions());
        $this->assertSame($this->schemeHandler->expectedResult, $this->schemeHandler->authenticate($request, $scheme));
        $this->assertSame('foo', $this->schemeHandler->actualUsername);
        $this->assertSame('bar', $this->schemeHandler->actualPassword);
        $this->assertSame($scheme, $this->schemeHandler->actualScheme);
    }

    /**
     * @dataProvider getInvalidBasicAuthorizationValues
     *
     * @param string $authorizationHeaderValue The invalid authorization header value
     * @param string $expectedFailureMessage The expected failure exception's message
     */
    public function testAuthenticatingWithInvalidAuthorizationHeaderReturnsFailedResult(string $authorizationHeaderValue, string $expectedFailureMessage): void
    {
        $headers = new Headers();
        $request = $this->createMock(IRequest::class);
        $request->method('getHeaders')
            ->willReturn($headers);
        $headers->add('Authorization', $authorizationHeaderValue);
        /** @psalm-suppress InvalidCast https://github.com/vimeo/psalm/issues/8810 - bug */
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
        /** @psalm-suppress InvalidCast https://github.com/vimeo/psalm/issues/8810 - bug */
        $scheme = new AuthenticationScheme('foo', $this->schemeHandler::class, new BasicAuthenticationOptions());
        $result = $this->schemeHandler->authenticate($request, $scheme);
        $this->assertFalse($result->passed);
        $this->assertSame('Missing authorization header', $result->failure?->getMessage());
    }

    /**
     * @dataProvider getChallengeSchemesAndWwwAuthenticateHeaderValues
     *
     * @param AuthenticationScheme<BasicAuthenticationOptions> $scheme The scheme we're challenging
     * @param string $expectedWwwAuthenticateHeaderValue The expected Www-Authenticate header value
     */
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
        /** @psalm-suppress InvalidCast https://github.com/vimeo/psalm/issues/8810 - bug */
        $scheme = new AuthenticationScheme('foo', $this->schemeHandler::class, new BasicAuthenticationOptions());
        $this->schemeHandler->forbid($this->createMock(IRequest::class), $response, $scheme);
    }
}
