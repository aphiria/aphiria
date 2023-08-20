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

use Aphiria\Authentication\AggregateAuthenticationException;
use Aphiria\Authentication\AuthenticationResult;
use Aphiria\Authentication\AuthenticationScheme;
use Aphiria\Authentication\AuthenticationSchemeNotFoundException;
use Aphiria\Authentication\AuthenticationSchemeOptions;
use Aphiria\Authentication\AuthenticationSchemeRegistry;
use Aphiria\Authentication\Authenticator;
use Aphiria\Authentication\IAuthenticationSchemeHandlerResolver;
use Aphiria\Authentication\NotAuthenticatedException;
use Aphiria\Authentication\Schemes\IAuthenticationSchemeHandler;
use Aphiria\Authentication\Schemes\ILoginAuthenticationSchemeHandler;
use Aphiria\Authentication\UnsupportedAuthenticationHandlerException;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Security\ClaimType;
use Aphiria\Security\IIdentity;
use Aphiria\Security\IPrincipal;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AuthenticatorTest extends TestCase
{
    private IAuthenticationSchemeHandlerResolver&MockInterface $authenticationHandlerResolver;
    private Authenticator $authenticator;
    private AuthenticationSchemeRegistry $schemes;

    protected function setUp(): void
    {
        $this->schemes = new AuthenticationSchemeRegistry();
        $this->authenticationHandlerResolver = Mockery::mock(IAuthenticationSchemeHandlerResolver::class);
        $this->authenticator = new Authenticator($this->schemes, $this->authenticationHandlerResolver);
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

            public function filterClaims(ClaimType|string $type): array
            {
                return [];
            }

            public function getClaims(): array
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

            public function filterClaims(ClaimType|string $type): array
            {
                return [];
            }

            public function getClaims(): array
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
                    public function filterClaims(ClaimType|string $type): array
                    {
                        return [];
                    }

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

                    public function setAuthenticationSchemeName(string $authenticationSchemeName): void
                    {
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

    public function testAuthenticateWithDefaultAuthenticationSchemeUsesDefaultScheme(): void
    {
        $request = $this->createMock(IRequest::class);
        $user = $this->createMock(IPrincipal::class);
        $expectedResult = AuthenticationResult::pass($user, 'scheme');
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $schemeHandler->shouldReceive('authenticate')
            ->with($request, $scheme)
            ->andReturn($expectedResult);
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

    public function testAuthenticatingMultipleSchemesThatAllFailReturnsFailingResultWithAggregateFailure(): void
    {
        $request = $this->createMock(IRequest::class);
        $user1 = $this->createMock(IPrincipal::class);
        $user2 = $this->createMock(IPrincipal::class);
        $user1->method('mergeIdentities')
            ->with($user2);
        [$scheme1, $scheme1Handler] = $this->createSchemeAndSetUpResolver('foo');
        [$scheme2, $scheme2Handler] = $this->createSchemeAndSetUpResolver('bar');
        $scheme1Handler->shouldReceive('authenticate')
            ->with($request, $scheme1)
            ->andReturn(AuthenticationResult::fail('failure 1', 'foo'));
        $scheme2Handler->shouldReceive('authenticate')
            ->with($request, $scheme2)
            ->andReturn(AuthenticationResult::fail('failure 2', 'bar'));
        $actualResult = $this->authenticator->authenticate($request, ['foo', 'bar']);
        $this->assertFalse($actualResult->passed);
        $this->assertSame('All authentication schemes failed to authenticate', $actualResult->failure?->getMessage());
        // I had to combine this check into one instance to make Psalm happy
        $this->assertTrue(
            $actualResult->failure instanceof AggregateAuthenticationException
            && \count($actualResult->failure->innerExceptions) === 2
        );
    }

    public function testAuthenticatingMultipleSchemesThatMultiplePassReturnsPassingResultWithMergedUserIdentity(): void
    {
        $request = $this->createMock(IRequest::class);
        $user1 = $this->createMock(IPrincipal::class);
        $user2 = $this->createMock(IPrincipal::class);
        $user1->method('mergeIdentities')
            ->with($user2)
            ->willReturn($user1);
        [$scheme1, $scheme1Handler] = $this->createSchemeAndSetUpResolver('foo');
        [$scheme2, $scheme2Handler] = $this->createSchemeAndSetUpResolver('bar');
        $scheme1Handler->shouldReceive('authenticate')
            ->with($request, $scheme1)
            ->andReturn(AuthenticationResult::pass($user1, 'foo'));
        $scheme2Handler->shouldReceive('authenticate')
            ->with($request, $scheme2)
            ->andReturn(AuthenticationResult::pass($user2, 'bar'));
        // Note: The authenticator will essentially clone the expected result set above, but with user 1 merged with user 2's identities
        $actualResult = $this->authenticator->authenticate($request, ['foo', 'bar']);
        $this->assertTrue($actualResult->passed);
        $this->assertSame($user1, $actualResult->user);
    }

    public function testAuthenticatingMultipleSchemesThatOnePassesReturnsPassingResult(): void
    {
        $request = $this->createMock(IRequest::class);
        $user = $this->createMock(IPrincipal::class);
        [$scheme1, $scheme1Handler] = $this->createSchemeAndSetUpResolver('foo');
        [$scheme2, $scheme2Handler] = $this->createSchemeAndSetUpResolver('bar');
        $scheme1Handler->shouldReceive('authenticate')
            ->with($request, $scheme1)
            ->andReturn(AuthenticationResult::fail('fail', 'foo'));
        $scheme2Handler->shouldReceive('authenticate')
            ->with($request, $scheme2)
            ->andReturn(AuthenticationResult::pass($user, 'bar'));
        $actualResult = $this->authenticator->authenticate($request, ['foo', 'bar']);
        $this->assertTrue($actualResult->passed);
        $this->assertSame($user, $actualResult->user);
    }

    public function testAuthenticatingSingleSchemeThatFailsReturnsFailingResult(): void
    {
        $request = $this->createMock(IRequest::class);
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        // We're using a custom exception type to make sure that that's what is set in the auth result's failure
        $expectedFailure = new RuntimeException('fail');
        $schemeHandler->shouldReceive('authenticate')
            ->with($request, $scheme)
            ->andReturn(AuthenticationResult::fail($expectedFailure, 'foo'));
        $actualResult = $this->authenticator->authenticate($request, 'foo');
        $this->assertFalse($actualResult->passed);
        $this->assertSame($expectedFailure, $actualResult->failure);
    }

    public function testAuthenticatingSingleSchemeThatPassesReturnsPassingResult(): void
    {
        $request = $this->createMock(IRequest::class);
        $user = $this->createMock(IPrincipal::class);
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $schemeHandler->shouldReceive('authenticate')
            ->with($request, $scheme)
            ->andReturn(AuthenticationResult::pass($user, 'foo'));
        $actualResult = $this->authenticator->authenticate($request, 'foo');
        $this->assertTrue($actualResult->passed);
        $this->assertSame($user, $actualResult->user);
    }

    public function testAuthenticatingWithEmptyListOfSchemeNamesThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must specify at least one scheme name or pass in null if using the default scheme');
        $this->authenticator->authenticate($this->createMock(IRequest::class), []);
    }

    public function testChallengeForMultipleSchemesCallsAllSchemeHandlers(): void
    {
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        [$scheme1, $scheme1Handler] = $this->createSchemeAndSetUpResolver('foo');
        [$scheme2, $scheme2Handler] = $this->createSchemeAndSetUpResolver('bar');
        $scheme1Handler->shouldReceive('challenge')
            ->with($request, $response, $scheme1);
        $scheme2Handler->shouldReceive('challenge')
            ->with($request, $response, $scheme2);
        $this->authenticator->challenge($request, $response, ['foo', 'bar']);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testChallengeForSingleSchemeCallsSchemeHandler(): void
    {
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $schemeHandler->shouldReceive('challenge')
            ->with($request, $response, $scheme);
        $this->authenticator->challenge($request, $response, 'foo');
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testChallengeWithNonExistentSchemeThrowsException(): void
    {
        $this->expectException(AuthenticationSchemeNotFoundException::class);
        $this->expectExceptionMessage('No authentication scheme with name "foo" found');
        $this->authenticator->challenge($this->createMock(IRequest::class), $this->createMock(IResponse::class), 'foo');
    }

    public function testForbidForMultipleSchemesCallsAllSchemeHandlers(): void
    {
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        [$scheme1, $scheme1Handler] = $this->createSchemeAndSetUpResolver('foo');
        [$scheme2, $scheme2Handler] = $this->createSchemeAndSetUpResolver('bar');
        $scheme1Handler->shouldReceive('forbid')
            ->with($request, $response, $scheme1);
        $scheme2Handler->shouldReceive('forbid')
            ->with($request, $response, $scheme2);
        $this->authenticator->forbid($request, $response, ['foo', 'bar']);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testForbidForSingleSchemeCallsSchemeHandler(): void
    {
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        [$scheme, $schemeHandler] = $this->createSchemeAndSetUpResolver('foo');
        $schemeHandler->shouldReceive('forbid')
            ->with($request, $response, $scheme);
        $this->authenticator->forbid($request, $response, 'foo');
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testForbidWithNonExistentSchemeThrowsException(): void
    {
        $this->expectException(AuthenticationSchemeNotFoundException::class);
        $this->expectExceptionMessage('No authentication scheme with name "foo" found');
        $this->authenticator->forbid($this->createMock(IRequest::class), $this->createMock(IResponse::class), 'foo');
    }

    public function testLogInForMultipleSchemesCallsAllSchemeHandlers(): void
    {
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        [$scheme1, $scheme1Handler] = $this->createLoginSchemeAndSetUpResolver('foo');
        [$scheme2, $scheme2Handler] = $this->createLoginSchemeAndSetUpResolver('bar');
        $identity = $this->createMock(IIdentity::class);
        $identity->method('isAuthenticated')
            ->willReturn(true);
        $user = $this->createMock(IPrincipal::class);
        $user->method('getPrimaryIdentity')
            ->willReturn($identity);
        $scheme1Handler->shouldReceive('logIn')
            ->with($user, $request, $response, $scheme1);
        $scheme2Handler->shouldReceive('logIn')
            ->with($user, $request, $response, $scheme2);
        $this->authenticator->logIn($user, $request, $response, ['foo', 'bar']);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testLogInForSingleSchemeCallsSchemeHandler(): void
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
        $schemeHandler->shouldReceive('logIn')
            ->with($user, $request, $response, $scheme);
        $this->authenticator->logIn($user, $request, $response, 'foo');
        // Dummy assertion
        $this->assertTrue(true);
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

    public function testLogOutForMultipleSchemesCallsAllSchemeHandlers(): void
    {
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        [$scheme1, $scheme1Handler] = $this->createLoginSchemeAndSetUpResolver('foo');
        [$scheme2, $scheme2Handler] = $this->createLoginSchemeAndSetUpResolver('bar');
        $scheme1Handler->shouldReceive('logOut')
            ->with($request, $response, $scheme1);
        $scheme2Handler->shouldReceive('logOut')
            ->with($request, $response, $scheme2);
        $this->authenticator->logOut($request, $response, ['foo', 'bar']);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testLogOutForSingleSchemeCallsSchemeHandler(): void
    {
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        [$scheme, $schemeHandler] = $this->createLoginSchemeAndSetUpResolver('foo');
        $schemeHandler->shouldReceive('logOut')
            ->with($request, $response, $scheme);
        $this->authenticator->logOut($request, $response, 'foo');
        // Dummy assertion
        $this->assertTrue(true);
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
     * @return array{0: AuthenticationScheme, 1: ILoginAuthenticationSchemeHandler<AuthenticationSchemeOptions>&MockInterface} The created authentication scheme and handler
     */
    private function createLoginSchemeAndSetUpResolver(string $schemeName): array
    {
        // PHPUnit will not assign unique mock class names if you create multiple
        // So, we'll use a mock builder to ensure that the generated class names are unique
        $schemeHandlerClassName = "{$schemeName}_LoginSchemeHandler";
        /** @var ILoginAuthenticationSchemeHandler<AuthenticationSchemeOptions>&MockInterface $schemeHandler */
        $schemeHandler = Mockery::namedMock($schemeHandlerClassName, ILoginAuthenticationSchemeHandler::class);
        $scheme = new AuthenticationScheme($schemeName, $schemeHandler::class);
        $this->authenticationHandlerResolver->shouldReceive('resolve')
            ->with($schemeHandlerClassName)
            ->andReturn($schemeHandler);
        $this->schemes->registerScheme($scheme);

        return [$scheme, $schemeHandler];
    }

    /**
     * A helper method to create an authentication scheme and set up the resolver to resolve its handler
     *
     * @param string $schemeName The name of the scheme to create
     * @return array{0: AuthenticationScheme, 1: IAuthenticationSchemeHandler<AuthenticationSchemeOptions>&MockInterface} The created authentication scheme and handler
     */
    private function createSchemeAndSetUpResolver(string $schemeName): array
    {
        // PHPUnit will not assign unique mock class names if you create multiple
        // So, we'll use a mock builder to ensure that the generated class names are unique
        $schemeHandlerClassName = "{$schemeName}_SchemeHandler";
        /** @var IAuthenticationSchemeHandler<AuthenticationSchemeOptions>&MockInterface $schemeHandler */
        $schemeHandler = Mockery::namedMock($schemeHandlerClassName, IAuthenticationSchemeHandler::class);
        $scheme = new AuthenticationScheme($schemeName, $schemeHandler::class);
        $this->authenticationHandlerResolver->shouldReceive('resolve')
            ->with($schemeHandlerClassName)
            ->andReturn($schemeHandler);
        $this->schemes->registerScheme($scheme);

        return [$scheme, $schemeHandler];
    }
}
