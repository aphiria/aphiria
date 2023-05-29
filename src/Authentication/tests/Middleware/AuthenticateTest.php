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
use Aphiria\Authentication\Middleware\Authenticate;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use Aphiria\Security\IPrincipal;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AuthenticateTest extends TestCase
{
    private IAuthenticator&MockInterface $authenticator;
    private Authenticate $middleware;

    protected function setUp(): void
    {
        $this->authenticator = Mockery::mock(IAuthenticator::class);
        $this->middleware = new Authenticate($this->authenticator);
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
    public function testHandlingFailingAuthenticationResultCallsChallengesTheResponse(array $schemeNames): void
    {
        $this->middleware->setParameters(['schemeNames' => $schemeNames]);
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        $next = $this->createMock(IRequestHandler::class);

        foreach ($schemeNames as $schemeName) {
            $this->authenticator->shouldReceive('authenticate')
                ->with($request, $schemeName)
                ->andReturn(AuthenticationResult::fail('foo'));
            $this->authenticator->shouldReceive('challenge')
                ->withArgs(function (IRequest $actualRequest, IResponse $actualResponse, ?string $actualSchemeName) use ($request, $schemeName): bool {
                    return $actualRequest === $request
                        && $actualResponse->getStatusCode() === HttpStatusCode::Unauthorized
                        && $actualSchemeName === $schemeName;
                });
        }

        $next->expects($this->never())
            ->method('handle')
            ->willReturn($response);
        $this->assertSame(HttpStatusCode::Unauthorized, $this->middleware->handle($request, $next)->getStatusCode());
    }

    public function testHandlingFailingThenPassingAuthenticationSchemeStillCallsNextRequestHandler(): void
    {
        $this->middleware->setParameters(['schemeNames' => ['foo', 'bar']]);
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        $next = $this->createMock(IRequestHandler::class);

        $this->authenticator->shouldReceive('authenticate')
            ->with($request, 'foo')
            ->andReturn(AuthenticationResult::fail('foo'));
        $this->authenticator->shouldReceive('authenticate')
            ->with($request, 'bar')
            ->andReturn(AuthenticationResult::pass($this->createMock(IPrincipal::class)));

        $next->expects($this->once())
            ->method('handle')
            ->willReturn($response);
        $this->assertSame($response, $this->middleware->handle($request, $next));
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

        foreach ($schemeNames as $schemeName) {
            $this->authenticator->shouldReceive('authenticate')
                ->with($request, $schemeName)
                ->andReturn(AuthenticationResult::pass($this->createMock(IPrincipal::class)));
        }

        $next->expects($this->once())
            ->method('handle')
            ->willReturn($response);
        $this->assertSame($response, $this->middleware->handle($request, $next));
    }

    public function testHandlingPassingThenFailingAuthenticationSchemeStillCallsNextRequestHandler(): void
    {
        $this->middleware->setParameters(['schemeNames' => ['foo', 'bar']]);
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        $next = $this->createMock(IRequestHandler::class);

        $this->authenticator->shouldReceive('authenticate')
            ->with($request, 'foo')
            ->andReturn(AuthenticationResult::pass($this->createMock(IPrincipal::class)));
        $this->authenticator->shouldReceive('authenticate')
            ->with($request, 'bar')
            ->andReturn(AuthenticationResult::fail('foo'));

        $next->expects($this->once())
            ->method('handle')
            ->willReturn($response);
        $this->assertSame($response, $this->middleware->handle($request, $next));
    }
}
