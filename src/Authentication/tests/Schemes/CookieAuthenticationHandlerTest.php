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
use Aphiria\Authentication\MissingAuthenticationDataException;
use Aphiria\Authentication\Schemes\CookieAuthenticationHandler;
use Aphiria\Authentication\Schemes\CookieAuthenticationOptions;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\Headers\SameSiteMode;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Security\IPrincipal;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CookieAuthenticationHandlerTest extends TestCase
{
    private CookieAuthenticationHandler $schemeHandler;

    protected function setUp(): void
    {
        $this->schemeHandler = new class () extends CookieAuthenticationHandler {
            public ?AuthenticationResult $expectedAuthenticationResult = null;
            public string|int|float|null $expectedCookieValue = null;

            protected function createAuthenticationResultFromCookie(string $cookieValue, AuthenticationScheme $scheme): AuthenticationResult
            {
                return $this->expectedAuthenticationResult ?? throw new RuntimeException('Expected authentication result not set');
            }

            protected function getCookieValueFromUser(IPrincipal $user, AuthenticationScheme $scheme): string|int|float
            {
                return $this->expectedCookieValue ?? throw new RuntimeException('Expected cookie value not set');
            }
        };
    }

    public function testAuthenticateWithCookieNotSetReturnsFailedResult(): void
    {
        $headers = new Headers();
        $request = $this->createMock(IRequest::class);
        $request->method('getHeaders')
            ->willReturn($headers);
        $scheme = new AuthenticationScheme('foo', $this->schemeHandler::class, new CookieAuthenticationOptions('cookie'));
        $result = $this->schemeHandler->authenticate($request, $scheme);
        $this->assertFalse($result->passed);
        $this->assertInstanceOf(MissingAuthenticationDataException::class, $result->failure);
        $this->assertSame('Cookie cookie not set', $result->failure->getMessage());
    }

    public function testAuthenticateWithCookieSetReturnsExpectedResult(): void
    {
        $headers = new Headers();
        $headers->add('Cookie', 'cookie=abc');
        $request = $this->createMock(IRequest::class);
        $request->method('getHeaders')
            ->willReturn($headers);
        $scheme = new AuthenticationScheme('foo', $this->schemeHandler::class, new CookieAuthenticationOptions('cookie'));
        $this->schemeHandler->expectedAuthenticationResult = AuthenticationResult::pass($this->createMock(IPrincipal::class));
        $this->assertSame(
            $this->schemeHandler->expectedAuthenticationResult,
            $this->schemeHandler->authenticate($request, $scheme)
        );
    }

    public function testChallengeWithLoginPagePathSetRedirectsToPath(): void
    {
        $headers = new Headers();
        $response = $this->createMock(IResponse::class);
        $response->expects($this->once())
            ->method('setStatusCode')
            ->with(HttpStatusCode::Found);
        $response->method('getHeaders')
            ->willReturn($headers);
        $scheme = new AuthenticationScheme('foo', $this->schemeHandler::class, new CookieAuthenticationOptions('cookie', loginPagePath: '/login'));
        $this->schemeHandler->challenge($this->createMock(IRequest::class), $response, $scheme);
        $this->assertSame('/login', $headers->getFirst('Location'));
    }

    public function testChallengeWithNoLoginPagePathSetSetsStatusCodeToUnauthorized(): void
    {
        $headers = new Headers();
        $response = $this->createMock(IResponse::class);
        $response->expects($this->once())
            ->method('setStatusCode')
            ->with(HttpStatusCode::Unauthorized);
        $response->method('getHeaders')
            ->willReturn($headers);
        $scheme = new AuthenticationScheme('foo', $this->schemeHandler::class, new CookieAuthenticationOptions('cookie'));
        $this->schemeHandler->challenge($this->createMock(IRequest::class), $response, $scheme);
    }

    public function testForbidWithForbiddenPagePathSetRedirectsToPath(): void
    {
        $headers = new Headers();
        $response = $this->createMock(IResponse::class);
        $response->expects($this->once())
            ->method('setStatusCode')
            ->with(HttpStatusCode::Found);
        $response->method('getHeaders')
            ->willReturn($headers);
        $scheme = new AuthenticationScheme('foo', $this->schemeHandler::class, new CookieAuthenticationOptions('cookie', forbiddenPagePath: '/forbidden'));
        $this->schemeHandler->forbid($this->createMock(IRequest::class), $response, $scheme);
        $this->assertSame('/forbidden', $headers->getFirst('Location'));
    }

    public function testForbidWithNoForbiddenPagePathSetSetsStatusCodeToUnauthorized(): void
    {
        $headers = new Headers();
        $response = $this->createMock(IResponse::class);
        $response->expects($this->once())
            ->method('setStatusCode')
            ->with(HttpStatusCode::Forbidden);
        $response->method('getHeaders')
            ->willReturn($headers);
        $scheme = new AuthenticationScheme('foo', $this->schemeHandler::class, new CookieAuthenticationOptions('cookie'));
        $this->schemeHandler->forbid($this->createMock(IRequest::class), $response, $scheme);
    }

    public function testLogInSetsResponseCookie(): void
    {
        $headers = new Headers();
        $response = $this->createMock(IResponse::class);
        $response->method('getHeaders')
            ->willReturn($headers);
        $options = new CookieAuthenticationOptions(
            cookieName: 'cookie',
            cookieMaxAge: 360,
            cookiePath: '/path',
            cookieDomain: 'example.com',
            cookieIsSecure: true,
            cookieIsHttpOnly: true,
            cookieSameSite: SameSiteMode::Strict
        );
        $scheme = new AuthenticationScheme('foo', $this->schemeHandler::class, $options);
        $this->schemeHandler->expectedCookieValue = 'abc';
        $this->schemeHandler->logIn($this->createMock(IPrincipal::class), $this->createMock(IRequest::class), $response, $scheme);
        $this->assertSame(
            'cookie=abc; Max-Age=360; Path=/path; Domain=example.com; Secure; HttpOnly; SameSite=strict',
            $headers->getFirst('Set-Cookie')
        );
    }

    public function testLogOutDeletesResponseCookie(): void
    {
        $headers = new Headers();
        $response = $this->createMock(IResponse::class);
        $response->method('getHeaders')
            ->willReturn($headers);
        $options = new CookieAuthenticationOptions(
            cookieName: 'cookie',
            cookieMaxAge: 360,
            cookiePath: '/path',
            cookieDomain: 'example.com',
            cookieIsSecure: true,
            cookieIsHttpOnly: true,
            cookieSameSite: SameSiteMode::Strict
        );
        $scheme = new AuthenticationScheme('foo', $this->schemeHandler::class, $options);
        $this->schemeHandler->expectedCookieValue = 'abc';
        $this->schemeHandler->logOut($this->createMock(IRequest::class), $response, $scheme);
        $this->assertSame(
            'cookie=; Max-Age=0; Path=/path; Domain=example.com; Secure; HttpOnly; SameSite=strict',
            $headers->getFirst('Set-Cookie')
        );
    }
}
