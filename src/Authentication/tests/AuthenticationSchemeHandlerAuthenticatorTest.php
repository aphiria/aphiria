<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Tests;

use Aphiria\Authentication\AuthenticationResult;
use Aphiria\Authentication\AuthenticationScheme;
use Aphiria\Authentication\AuthenticationSchemeOptions;
use Aphiria\Authentication\AuthenticationSchemeRegistry;
use Aphiria\Authentication\AuthenticationSchemeHandlerAuthenticator;
use Aphiria\Authentication\IAuthenticationSchemeHandlerResolver;
use Aphiria\Authentication\IUserAccessor;
use Aphiria\Authentication\NotAuthenticatedException;
use Aphiria\Authentication\SchemeNotFoundException;
use Aphiria\Authentication\Schemes\IAuthenticationSchemeHandler;
use Aphiria\Authentication\Schemes\ILoginAuthenticationSchemeHandler;
use Aphiria\Authentication\UnsupportedAuthenticationHandlerException;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Security\IIdentity;
use Aphiria\Security\IPrincipal;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthenticationSchemeHandlerAuthenticatorTest extends TestCase
{
    private AuthenticationSchemeHandlerAuthenticator $authenticator;
    private AuthenticationSchemeRegistry $schemes;
    private IAuthenticationSchemeHandlerResolver&MockObject $authenticationHandlerResolver;
    private IUserAccessor&MockObject $userAccessor;

    protected function setUp(): void
    {
        $this->schemes = new AuthenticationSchemeRegistry();
        $this->authenticationHandlerResolver = $this->createMock(IAuthenticationSchemeHandlerResolver::class);
        $this->userAccessor = $this->createMock(IUserAccessor::class);
        $this->authenticator = new AuthenticationSchemeHandlerAuthenticator($this->schemes, $this->authenticationHandlerResolver, $this->userAccessor);
    }

    public function getUsersForLogin(): array
    {
        $userWithNoIdentity = $this->createMock(IPrincipal::class);
        $userWithNoIdentity->method('getPrimaryIdentity')
            ->willReturn(null);
        $userWithUnauthenticatedIdentity = $this->createMock(IPrincipal::class);
        $unauthenticatedIdentity = $this->createMock(IIdentity::class);
        $unauthenticatedIdentity->method('isAuthenticated')
            ->willReturn(false);
        $userWithUnauthenticatedIdentity->method('getPrimaryIdentity')
            ->willReturn($unauthenticatedIdentity);

        return [
            [$userWithNoIdentity],
            [$userWithUnauthenticatedIdentity]
        ];
    }

    public function testAuthenticateDoesNotSetUserOnFailure(): void
    {
        $request = $this->createMock(IRequest::class);
        $expectedResult = AuthenticationResult::fail('whoops');
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $schemeHandler->method('authenticate')
            ->with($request, $scheme)
            ->willReturn($expectedResult);
        $this->userAccessor->expects($this->never())
            ->method('setUser');
        $this->assertSame($expectedResult, $this->authenticator->authenticate($request, 'foo'));
    }

    public function testAuthenticateReturnsResultFromSchemeHandler(): void
    {
        $request = $this->createMock(IRequest::class);
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $expectedResult = AuthenticationResult::pass($this->createMock(IPrincipal::class));
        $schemeHandler->method('authenticate')
            ->with($request, $scheme)
            ->willReturn($expectedResult);
        $this->assertSame($expectedResult, $this->authenticator->authenticate($request, 'foo'));
    }

    public function testAuthenticateSetsUserOnSuccess(): void
    {
        $request = $this->createMock(IRequest::class);
        $expectedUser = $this->createMock(IPrincipal::class);
        $expectedResult = AuthenticationResult::pass($expectedUser);
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $schemeHandler->expects($this->once())
            ->method('authenticate')
            ->with($request, $scheme)
            ->willReturn($expectedResult);
        $this->userAccessor->method('setUser')
            ->with($expectedUser, $request);
        $this->assertSame($expectedResult, $this->authenticator->authenticate($request, 'foo'));
    }

    public function testAuthenticateWithDefaultAuthenticationSchemeUsesDefaultScheme(): void
    {
        $request = $this->createMock(IRequest::class);
        $expectedUser = $this->createMock(IPrincipal::class);
        $expectedResult = AuthenticationResult::pass($expectedUser);
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $schemeHandler->expects($this->once())
            ->method('authenticate')
            ->with($request, $scheme)
            ->willReturn($expectedResult);
        $this->schemes->registerScheme($scheme, true);
        $this->assertSame($expectedResult, $this->authenticator->authenticate($request));
    }

    public function testAuthenticateWithNoDefaultAuthenticationSchemeThrowsException(): void
    {
        $this->expectException(SchemeNotFoundException::class);
        $this->expectExceptionMessage('No default authentication scheme found');
        $this->authenticator->authenticate($this->createMock(IRequest::class));
    }

    public function testAuthenticateWithNonExistentSchemeThrowsException(): void
    {
        $this->expectException(SchemeNotFoundException::class);
        $this->expectExceptionMessage('No authentication scheme with name "foo" found');
        $this->authenticator->authenticate($this->createMock(IRequest::class), 'foo');
    }

    public function testChallengeCallsSchemeHandler(): void
    {
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $schemeHandler->expects($this->once())
            ->method('challenge')
            ->with($request, $response, $scheme);
        $this->authenticator->challenge($request, $response, 'foo');
    }

    public function testChallengeWithNonExistentSchemeThrowsException(): void
    {
        $this->expectException(SchemeNotFoundException::class);
        $this->expectExceptionMessage('No authentication scheme with name "foo" found');
        $this->authenticator->challenge($this->createMock(IRequest::class), $this->createMock(IResponse::class), 'foo');
    }

    public function testForbidCallsSchemeHandler(): void
    {
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $schemeHandler->expects($this->once())
            ->method('forbid')
            ->with($request, $response, $scheme);
        $this->authenticator->forbid($request, $response, 'foo');
    }

    public function testForbidWithNonExistentSchemeThrowsException(): void
    {
        $this->expectException(SchemeNotFoundException::class);
        $this->expectExceptionMessage('No authentication scheme with name "foo" found');
        $this->authenticator->forbid($this->createMock(IRequest::class), $this->createMock(IResponse::class), 'foo');
    }

    public function testLogInCallsSchemeHandler(): void
    {
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        [$scheme, $schemeHandler] = $this->createLoginSchemeAndSetUpResolver('foo');
        $identity = $this->createMock(IIdentity::class);
        $identity->method('isAuthenticated')
            ->willReturn(true);
        $user = $this->createMock(IPrincipal::class);
        $user->method('getPrimaryIdentity')
            ->willReturn($identity);
        $schemeHandler->expects($this->once())
            ->method('logIn')
            ->with($user, $request, $response, $scheme);
        $this->authenticator->logIn($user, $request, $response, 'foo');
    }

    public function testLogInWithNonExistentSchemeThrowsException(): void
    {
        $this->expectException(SchemeNotFoundException::class);
        $this->expectExceptionMessage('No authentication scheme with name "foo" found');
        $identity = $this->createMock(IIdentity::class);
        $identity->method('isAuthenticated')
            ->willReturn(true);
        $user = $this->createMock(IPrincipal::class);
        $user->method('getPrimaryIdentity')
            ->willReturn($identity);
        $this->authenticator->logIn($user, $this->createMock(IRequest::class), $this->createMock(IResponse::class), 'foo');
    }

    public function testLogInWithSchemeHandlerThatDoesNotSupportLogInThrowsException(): void
    {
        $this->expectException(UnsupportedAuthenticationHandlerException::class);
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        [, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $this->expectExceptionMessage($schemeHandler::class . ' does not implement ' . ILoginAuthenticationSchemeHandler::class);
        $identity = $this->createMock(IIdentity::class);
        $identity->method('isAuthenticated')
            ->willReturn(true);
        $user = $this->createMock(IPrincipal::class);
        $user->method('getPrimaryIdentity')
            ->willReturn($identity);
        $this->authenticator->logIn($user, $request, $response, 'foo');
    }

    /**
     * @dataProvider getUsersForLogin
     *
     * @param IPrincipal $user The user to log in in tests
     */
    public function testLogInWithUnauthenticatedUserThrowsException(IPrincipal $user): void
    {
        $this->expectException(NotAuthenticatedException::class);
        $this->expectExceptionMessage('User identity must be set and authenticated to log in');
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        $this->createLoginSchemeAndSetUpResolver('foo');
        $this->authenticator->logIn($user, $request, $response, 'foo');
    }

    public function testLogOutCallsSchemeHandler(): void
    {
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        [$scheme, $schemeHandler] = $this->createLoginSchemeAndSetUpResolver('foo');
        $schemeHandler->expects($this->once())
            ->method('logOut')
            ->with($request, $response, $scheme);
        $this->authenticator->logOut($request, $response, 'foo');
    }

    public function testLogOutWithNonExistentSchemeThrowsException(): void
    {
        $this->expectException(SchemeNotFoundException::class);
        $this->expectExceptionMessage('No authentication scheme with name "foo" found');
        $this->authenticator->logOut($this->createMock(IRequest::class), $this->createMock(IResponse::class), 'foo');
    }

    public function testLogOutWithSchemeHandlerThatDoesNotSupportLogOutThrowsException(): void
    {
        $this->expectException(UnsupportedAuthenticationHandlerException::class);
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        [, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $this->expectExceptionMessage($schemeHandler::class . ' does not implement ' . ILoginAuthenticationSchemeHandler::class);
        $this->authenticator->logOut($request, $response, 'foo');
    }

    /**
     * A helper method to create a login authentication scheme and set up the resolver to resolve its handler
     *
     * @param string $schemeName The name of the scheme to create
     * @return array{0: AuthenticationScheme, 1: ILoginAuthenticationSchemeHandler<AuthenticationSchemeOptions>&MockObject} The created authentication scheme and handler
     */
    private function createLoginSchemeAndSetUpResolver(string $schemeName): array
    {
        /** @var ILoginAuthenticationSchemeHandler<AuthenticationSchemeOptions>&MockObject $schemeHandler */
        $schemeHandler = $this->createMock(ILoginAuthenticationSchemeHandler::class);
        /** @psalm-suppress InvalidCast https://github.com/vimeo/psalm/issues/8810 - bug */
        $scheme = new AuthenticationScheme($schemeName, $schemeHandler::class);
        $this->authenticationHandlerResolver->method('resolve')
            ->with($schemeHandler::class)
            ->willReturn($schemeHandler);
        $this->schemes->registerScheme($scheme);

        return [$scheme, $schemeHandler];
    }

    /**
     * A helper method to create an authentication scheme and set up the resolver to resolve its handler
     *
     * @param string $schemeName The name of the scheme to create
     * @return array{0: AuthenticationScheme, 1: IAuthenticationSchemeHandler<AuthenticationSchemeOptions>&MockObject} The created authentication scheme and handler
     */
    private function createSchemeAndSetUpResolver(string $schemeName): array
    {
        /** @var IAuthenticationSchemeHandler<AuthenticationSchemeOptions>&MockObject $schemeHandler */
        $schemeHandler = $this->createMock(IAuthenticationSchemeHandler::class);
        /** @psalm-suppress InvalidCast https://github.com/vimeo/psalm/issues/8810 - bug */
        $scheme = new AuthenticationScheme($schemeName, $schemeHandler::class);
        $this->authenticationHandlerResolver->method('resolve')
            ->with($schemeHandler::class)
            ->willReturn($schemeHandler);
        $this->schemes->registerScheme($scheme);

        return [$scheme, $schemeHandler];
    }
}
