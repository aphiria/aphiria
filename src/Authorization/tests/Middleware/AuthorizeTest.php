<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\Tests\Middleware;

use Aphiria\Authentication\AuthenticationResult;
use Aphiria\Authentication\IAuthenticator;
use Aphiria\Authentication\IUserAccessor;
use Aphiria\Authorization\AuthorizationPolicy;
use Aphiria\Authorization\AuthorizationPolicyRegistry;
use Aphiria\Authorization\AuthorizationResult;
use Aphiria\Authorization\IAuthority;
use Aphiria\Authorization\Middleware\Authorize;
use Aphiria\Authorization\RequirementHandlers\RolesRequirement;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use Aphiria\Security\ClaimType;
use Aphiria\Security\Identity;
use Aphiria\Security\IIdentity;
use Aphiria\Security\IPrincipal;
use Aphiria\Security\User;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\TestCase;

class AuthorizeTest extends TestCase
{
    private IAuthenticator&MockObject $authenticator;
    private IAuthority&MockObject $authority;
    private Authorize $middleware;
    private AuthorizationPolicyRegistry $policies;
    private IUserAccessor&MockInterface $userAccessor;

    protected function setUp(): void
    {
        $this->authority = $this->createMock(IAuthority::class);
        $this->authenticator = $this->createMock(IAuthenticator::class);
        $this->policies = new AuthorizationPolicyRegistry();
        $this->userAccessor = Mockery::mock(IUserAccessor::class);
        $this->middleware = new Authorize($this->authority, $this->authenticator, $this->policies, $this->userAccessor);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public static function getUnauthenticatedUsers(): array
    {
        $userWithNoIdentity = new class () implements IPrincipal {
            public array $claims = [];
            public array $identities = [];
            public ?IIdentity $primaryIdentity = null;

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
            public array $claims = [];
            public array $identities = [];
            public ?IIdentity $primaryIdentity = null;

            public function __construct()
            {
                $this->primaryIdentity = new class () implements IIdentity {
                    public ?string $authenticationSchemeName = null;
                    public array $claims = [];
                    public bool $isAuthenticated = false;
                    public ?string $name = null;
                    public ?string $nameIdentifier = null;

                    public function filterClaims(ClaimType|string $type): array
                    {
                        return [];
                    }

                    public function hasClaim(ClaimType|string $type, mixed $value): bool
                    {
                        return false;
                    }
                };
            }

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

    public function testHandlingAuthorizedResultForPolicyCallsNextRequestHandler(): void
    {
        $request = $this->createMock(IRequest::class);
        $user = $this->createMockAuthenticatedUser();
        $this->userAccessor->shouldReceive('getUser')
            ->with($request)
            ->andReturn($user);
        $policy = new AuthorizationPolicy('policy', [$this]);
        $this->authority->expects($this->once())
            ->method('authorize')
            ->with($user, $policy)
            ->willReturn(AuthorizationResult::pass($policy->name));
        $this->middleware->parameters = ['policy' => $policy];
        $next = $this->createMock(IRequestHandler::class);
        $response = $this->createMock(IResponse::class);
        $next->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $this->assertSame($response, $this->middleware->handle($request, $next));
    }

    public function testHandlingAuthorizedResultForPolicyNameCallsNextRequestHandler(): void
    {
        $request = $this->createMock(IRequest::class);
        $user = $this->createMockAuthenticatedUser();
        $this->userAccessor->shouldReceive('getUser')
            ->with($request)
            ->andReturn($user);
        $policy = new AuthorizationPolicy('policy', [$this]);
        $this->policies->registerPolicy($policy);
        $this->authority->expects($this->once())
            ->method('authorize')
            ->with($user, $policy)
            ->willReturn(AuthorizationResult::pass($policy->name));
        $this->middleware->parameters = ['policyName' => $policy->name];
        $next = $this->createMock(IRequestHandler::class);
        $response = $this->createMock(IResponse::class);
        $next->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $this->assertSame($response, $this->middleware->handle($request, $next));
    }

    /**
     * @param IPrincipal $user The unauthenticated user
     */
    #[DataProvider('getUnauthenticatedUsers')]
    public function testHandlingUnauthenticatedUserReturnsUnauthorizedAndChallengedResponse(IPrincipal $user): void
    {
        $request = $this->createMock(IRequest::class);
        $this->userAccessor->shouldReceive('getUser')
            ->with($request)
            ->andReturn($user);
        $policy = new AuthorizationPolicy('policy', [$this], 'scheme');
        $this->authenticator->expects($this->once())
            ->method('challenge')
            ->with($request, $this->callback(fn (IResponse $response): bool => $response->statusCode === HttpStatusCode::Unauthorized), ['scheme']);
        $this->middleware->parameters = ['policy' => $policy];
        $response = $this->middleware->handle($request, $this->createMock(IRequestHandler::class));
        $this->assertSame(HttpStatusCode::Unauthorized, $response->statusCode);
    }

    public function testHandlingUnauthenticatedUserSetsUserAfterAuthenticatingAgainstMultipleSchemeNames(): void
    {
        // Need to use Mockery for these tests since they require us to check successive calls with different parameters
        $authenticator = Mockery::mock(IAuthenticator::class);
        $middleware = new Authorize($this->authority, $authenticator, $this->policies, $this->userAccessor);
        $request = $this->createMock(IRequest::class);
        // Must ensure the user has an authenticated identity
        $user = new User([new Identity([], 'authScheme1')]);
        $authenticator->shouldReceive('authenticate')
            ->with($request, ['authScheme1', 'authScheme2'])
            ->andReturn(AuthenticationResult::pass($user, ['authScheme1', 'authScheme2']));
        $this->userAccessor->shouldReceive('getUser')
            ->with($request)
            ->andReturn(null);
        $this->userAccessor->shouldReceive('setUser')
            ->with($user, $request);
        $policy = new AuthorizationPolicy('policy', [$this], ['authScheme1', 'authScheme2']);
        $middleware->parameters = ['policy' => $policy];
        $this->authority->expects($this->once())
            ->method('authorize')
            ->with($user, $policy)
            ->willReturn(AuthorizationResult::pass($policy->name));
        $response = $this->createMock(IResponse::class);
        $next = $this->createMock(IRequestHandler::class);
        $next->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $middleware->handle($request, $next);
    }

    public function testHandlingUnauthorizedResultForPolicyNameReturnsForbiddenResponse(): void
    {
        $request = $this->createMock(IRequest::class);
        $user = $this->createMockAuthenticatedUser();
        $this->userAccessor->shouldReceive('getUser')
            ->with($request)
            ->andReturn($user);
        $policy = new AuthorizationPolicy('policy', [$this], 'scheme');
        $this->policies->registerPolicy($policy);
        $this->authority->expects($this->once())
            ->method('authorize')
            ->with($user, $policy)
            ->willReturn(AuthorizationResult::fail($policy->name, [$this]));
        $this->authenticator->expects($this->once())
            ->method('forbid')
            ->with($request, $this->callback(fn (IResponse $response): bool => $response->statusCode === HttpStatusCode::Forbidden), ['scheme']);
        $this->middleware->parameters = ['policyName' => $policy->name];
        $response = $this->middleware->handle($request, $this->createMock(IRequestHandler::class));
        $this->assertSame(HttpStatusCode::Forbidden, $response->statusCode);
    }

    public function testHandlingUnauthorizedResultForPolicyReturnsForbiddenResponse(): void
    {
        $request = $this->createMock(IRequest::class);
        $user = $this->createMockAuthenticatedUser();
        $this->userAccessor->shouldReceive('getUser')
            ->with($request)
            ->andReturn($user);
        $policy = new AuthorizationPolicy('policy', [$this], 'scheme');
        $this->authority->expects($this->once())
            ->method('authorize')
            ->with($user, $policy)
            ->willReturn(AuthorizationResult::fail($policy->name, [$this]));
        $this->authenticator->expects($this->once())
            ->method('forbid')
            ->with($request, $this->callback(fn (IResponse $response): bool => $response->statusCode === HttpStatusCode::Forbidden), ['scheme']);
        $this->middleware->parameters = ['policy' => $policy];
        $response = $this->middleware->handle($request, $this->createMock(IRequestHandler::class));
        $this->assertSame(HttpStatusCode::Forbidden, $response->statusCode);
    }

    /**
     * @param string|null $policyName The policy name parameter
     * @param AuthorizationPolicy|null $policy The policy parameter
     * @param string $expectedExceptionMessage The expected exception message
     */
    #[TestWith([null, null, 'Either the policy name or the policy must be set'])]
    #[TestWith(['policy', new AuthorizationPolicy('foo', [new RolesRequirement('admin')]), 'Either the policy name or the policy must be set'])]
    public function testInvalidParametersThrowsException(
        ?string $policyName,
        ?AuthorizationPolicy $policy,
        string $expectedExceptionMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->middleware->parameters = ['policyName' => $policyName, 'policy' => $policy];
        $this->middleware->handle($this->createMock(IRequest::class), $this->createMock(IRequestHandler::class));
    }

    /**
     * Creates a mocked authenticated user
     *
     * @return IPrincipal The authenticated mock user
     */
    private function createMockAuthenticatedUser(): IPrincipal
    {
        $identity = $this->createMock(IIdentity::class);
        $identity->method(PropertyHook::get('isAuthenticated'))
            ->willReturn(true);
        $user = $this->createMock(IPrincipal::class);
        $user->method(PropertyHook::get('primaryIdentity'))
            ->willReturn($identity);

        return $user;
    }
}
