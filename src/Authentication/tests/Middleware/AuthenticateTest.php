<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Tests\Middleware;

use Aphiria\Authentication\AuthenticationResult;
use Aphiria\Authentication\IAuthenticator;
use Aphiria\Authentication\IUserAccessor;
use Aphiria\Authentication\Middleware\Authenticate;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use Aphiria\Security\IPrincipal;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthenticateTest extends TestCase
{
    private IAuthenticator&MockInterface $authenticator;
    private Authenticate $middleware;
    private IUserAccessor&MockObject $userAccessor;

    protected function setUp(): void
    {
        $this->authenticator = Mockery::mock(IAuthenticator::class);
        $this->userAccessor = $this->createMock(IUserAccessor::class);
        $this->middleware = new Authenticate($this->authenticator, $this->userAccessor);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public static function getSchemeNames(): array
    {
        return [
            [[null]],
            [['foo']],
            [['foo', 'bar']]
        ];
    }

    /**
     * @param list<string|null> $schemeNames The list of scheme names to test
     */
    #[DataProvider('getSchemeNames')]
    public function testHandlingFailingAuthenticationResultChallengesTheResponse(array $schemeNames): void
    {
        $this->middleware->setParameters(['schemeNames' => $schemeNames]);
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        $next = $this->createMock(IRequestHandler::class);
        // Authenticator will resolve null scheme names to the default scheme.  So, we'll create a dummy list of resolved scheme names that do not contain null.
        $resolvedSchemeNames = \array_fill(0, \count($schemeNames), 'scheme');
        $this->authenticator->shouldReceive('authenticate')
            ->with($request, $schemeNames)
            ->andReturn(AuthenticationResult::fail('foo', $resolvedSchemeNames));
        $this->authenticator->shouldReceive('challenge')
            ->withArgs(function (IRequest $actualRequest, IResponse $actualResponse, array|string $actualSchemeNames) use ($request, $resolvedSchemeNames): bool {
                // Similar to the above note, the real auth result will contain a non-null scheme name
                return $actualRequest === $request
                    && $actualResponse->getStatusCode() === HttpStatusCode::Unauthorized
                    && $actualSchemeNames === $resolvedSchemeNames;
            });
        $next->expects($this->never())
            ->method('handle')
            ->willReturn($response);
        $this->assertSame(HttpStatusCode::Unauthorized, $this->middleware->handle($request, $next)->getStatusCode());
    }

    /**
     * @param list<string|null> $schemeNames The list of scheme names to test
     */
    #[DataProvider('getSchemeNames')]
    public function testHandlingPassingAuthenticationResultCallsNextRequestHandler(array $schemeNames): void
    {
        $this->middleware->setParameters(['schemeNames' => $schemeNames]);
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        $next = $this->createMock(IRequestHandler::class);
        // Authenticator will resolve null scheme names to the default scheme.  So, we'll create a dummy list of resolved scheme names that do not contain null.
        $resolvedSchemeNames = \array_fill(0, \count($schemeNames), 'scheme');
        $this->authenticator->shouldReceive('authenticate')
            ->with($request, $schemeNames)
            ->andReturn(AuthenticationResult::pass($this->createMock(IPrincipal::class), $resolvedSchemeNames));
        $next->expects($this->once())
            ->method('handle')
            ->willReturn($response);
        $this->assertSame($response, $this->middleware->handle($request, $next));
    }
}
