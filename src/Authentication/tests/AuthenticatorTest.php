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
use Aphiria\Authentication\AuthenticationSchemeNotFoundException;
use Aphiria\Authentication\AuthenticationSchemeOptions;
use Aphiria\Authentication\AuthenticationSchemeRegistry;
use Aphiria\Authentication\Authenticator;
use Aphiria\Authentication\IAuthenticationSchemeHandlerResolver;
use Aphiria\Authentication\IUserAccessor;
use Aphiria\Authentication\NotAuthenticatedException;
use Aphiria\Authentication\Schemes\IAuthenticationSchemeHandler;
use Aphiria\Authentication\Schemes\ILoginAuthenticationSchemeHandler;
use Aphiria\Authentication\UnsupportedAuthenticationHandlerException;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Security\ClaimType;
use Aphiria\Security\IIdentity;
use Aphiria\Security\IPrincipal;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthenticatorTest extends TestCase
{
    private IAuthenticationSchemeHandlerResolver&MockObject $authenticationHandlerResolver;
    private Authenticator $authenticator;
    private AuthenticationSchemeRegistry $schemes;
    private IUserAccessor&MockInterface $userAccessor;

    protected function setUp(): void
    {
        $this->schemes = new AuthenticationSchemeRegistry();
        $this->authenticationHandlerResolver = $this->createMock(IAuthenticationSchemeHandlerResolver::class);
        $this->userAccessor = Mockery::mock(IUserAccessor::class);
        $this->authenticator = new Authenticator($this->schemes, $this->authenticationHandlerResolver, $this->userAccessor);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public static function getUsersForLogin(): array
    {
        $userWithNoIdentity = new class () implements IPrincipal {
            public function addIdentity(IIdentity $identity): void
            {
            }

            public function addManyIdentities(array $identities): void
            {
            }

            public function getClaims(ClaimType|string $type = null): array
            {
                return [];
            }

            public function getIdentities(): array
            {
                return [];
            }

            public function getPrimaryIdentity(): ?IIdentity
            {
                return null;
            }

            public function hasClaim(ClaimType|string $type, mixed $value): bool
            {
                return false;
            }

            public function mergeIdentities(IPrincipal $user, bool $includeUnauthenticatedIdentities = false): IPrincipal
            {
                return $this;
            }
        };
        $userWithUnauthenticatedIdentity = new class () implements IPrincipal {
            public function addIdentity(IIdentity $identity): void
            {
            }

            public function addManyIdentities(array $identities): void
            {
            }

            public function getClaims(ClaimType|string $type = null): array
            {
                return [];
            }

            public function getIdentities(): array
            {
                return [];
            }

            public function getPrimaryIdentity(): ?IIdentity
            {
                return new class () implements IIdentity {
                    public function getAuthenticationSchemeName(): ?string
                    {
                        return null;
                    }

                    public function getClaims(ClaimType|string $type = null): array
                    {
                        return [];
                    }

                    public function getName(): ?string
                    {
                        return null;
                    }

                    public function getNameIdentifier(): ?string
                    {
                        return null;
                    }

                    public function hasClaim(ClaimType|string $type, mixed $value): bool
                    {
                        return false;
                    }

                    public function isAuthenticated(): bool
                    {
                        return false;
                    }
                };
            }

            public function hasClaim(ClaimType|string $type, mixed $value): bool
            {
                return false;
            }

            public function mergeIdentities(IPrincipal $user, bool $includeUnauthenticatedIdentities = false): IPrincipal
            {
                return $this;
            }
        };

        return [
            [$userWithNoIdentity],
            [$userWithUnauthenticatedIdentity]
        ];
    }

    public function testAuthenticateDoesNotSetUserOnFailure(): void
    {
        $request = $this->createMock(IRequest::class);
        $expectedResult = AuthenticationResult::fail('whoops', 'scheme');
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $schemeHandler->method('authenticate')
            ->with($request, $scheme)
            ->willReturn($expectedResult);
        $this->userAccessor->shouldNotReceive('setUser');
        $this->assertSame($expectedResult, $this->authenticator->authenticate($request, 'foo'));
    }

    public function testAuthenticateReturnsResultFromSchemeHandler(): void
    {
        $request = $this->createMock(IRequest::class);
        $user = $this->createMock(IPrincipal::class);
        $this->userAccessor->shouldReceive('getUser')
            ->with($request)
            ->andReturn(null);
        $this->userAccessor->shouldReceive('setUser')
            ->with($user, $request);
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $expectedResult = AuthenticationResult::pass($user, 'scheme');
        $schemeHandler->method('authenticate')
            ->with($request, $scheme)
            ->willReturn($expectedResult);
        $this->assertSame($expectedResult, $this->authenticator->authenticate($request, 'foo'));
    }

    public function testAuthenticateSetsUserOnSuccess(): void
    {
        $request = $this->createMock(IRequest::class);
        $user = $this->createMock(IPrincipal::class);
        $this->userAccessor->shouldReceive('getUser')
            ->with($request)
            ->andReturn(null);
        $this->userAccessor->shouldReceive('setUser')
            ->with($user, $request);
        $expectedResult = AuthenticationResult::pass($user, 'scheme');
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $schemeHandler->expects($this->once())
            ->method('authenticate')
            ->with($request, $scheme)
            ->willReturn($expectedResult);
        $this->userAccessor->shouldReceive('setUser')
            ->with($user, $request);
        $this->assertSame($expectedResult, $this->authenticator->authenticate($request, 'foo'));
    }

    public function testAuthenticateWithDefaultAuthenticationSchemeUsesDefaultScheme(): void
    {
        $request = $this->createMock(IRequest::class);
        $user = $this->createMock(IPrincipal::class);
        $this->userAccessor->shouldReceive('getUser')
            ->with($request)
            ->andReturn(null);
        $this->userAccessor->shouldReceive('setUser')
            ->with($user, $request);
        $expectedResult = AuthenticationResult::pass($user, 'scheme');
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
        $this->expectException(AuthenticationSchemeNotFoundException::class);
        $this->expectExceptionMessage('No default authentication scheme found');
        $this->authenticator->authenticate($this->createMock(IRequest::class));
    }

    public function testAuthenticateWithNonExistentSchemeThrowsException(): void
    {
        $this->expectException(AuthenticationSchemeNotFoundException::class);
        $this->expectExceptionMessage('No authentication scheme with name "foo" found');
        $this->authenticator->authenticate($this->createMock(IRequest::class), 'foo');
    }

    public function testAuthenticatingUserWhenOneWasPreviouslySetMergesAuthenticatedIdentitiesAndSetsTheUser(): void
    {
        $request = $this->createMock(IRequest::class);
        $user1 = $this->createMock(IPrincipal::class);
        $user2 = $this->createMock(IPrincipal::class);
        $user1->method('mergeIdentities')
            ->with($user2);
        $this->userAccessor->shouldReceive('getUser')
            ->with($request)
            ->andReturn($user1);
        // The second user's identities should be merged into the first one's
        $this->userAccessor->shouldReceive('setUser')
            ->with($user1, $request);
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $expectedResult = AuthenticationResult::pass($user2, 'scheme');
        $schemeHandler->method('authenticate')
            ->with($request, $scheme)
            ->willReturn($expectedResult);
        // Note: The authenticator will essentially clone the expected result set above, but with user 1 merged with user 2's identities
        $actualResult = $this->authenticator->authenticate($request, 'foo');
        $this->assertSame($user1, $actualResult->user);
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
        $this->expectException(AuthenticationSchemeNotFoundException::class);
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
        $this->expectException(AuthenticationSchemeNotFoundException::class);
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
        $this->userAccessor->shouldReceive('setUser')
            ->with($user, $request);
        $schemeHandler->expects($this->once())
            ->method('logIn')
            ->with($user, $request, $response, $scheme);
        $this->authenticator->logIn($user, $request, $response, 'foo');
    }

    public function testLogInWithNonExistentSchemeThrowsException(): void
    {
        $this->expectException(AuthenticationSchemeNotFoundException::class);
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

    /**     *
     * @param IPrincipal $user The user to log in in tests
     */
    #[DataProvider('getUsersForLogin')]
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
        $this->userAccessor->shouldReceive('setUser')
            ->with(null, $request);
        [$scheme, $schemeHandler] = $this->createLoginSchemeAndSetUpResolver('foo');
        $schemeHandler->expects($this->once())
            ->method('logOut')
            ->with($request, $response, $scheme);
        $this->authenticator->logOut($request, $response, 'foo');
    }

    public function testLogOutWithNonExistentSchemeThrowsException(): void
    {
        $this->expectException(AuthenticationSchemeNotFoundException::class);
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
        $scheme = new AuthenticationScheme($schemeName, $schemeHandler::class);
        $this->authenticationHandlerResolver->method('resolve')
            ->with($schemeHandler::class)
            ->willReturn($schemeHandler);
        $this->schemes->registerScheme($scheme);

        return [$scheme, $schemeHandler];
    }
}
