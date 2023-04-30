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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthenticateTest extends TestCase
{
    private IAuthenticator&MockObject $authenticator;
    private Authenticate $middleware;

    protected function setUp(): void
    {
        $this->authenticator = $this->createMock(IAuthenticator::class);
        $this->middleware = new Authenticate($this->authenticator);
    }

    public static function getSchemeNames(): array
    {
        return [
            [null],
            ['foo']
        ];
    }

    /**
     * @param string|null $schemeName The scheme name or null
     */
    #[DataProvider('getSchemeNames')]
    public function testHandlingFailingAuthenticationResultCallsChallengesTheResponse(?string $schemeName): void
    {
        $this->middleware->setParameters(['schemeName' => $schemeName]);
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        $next = $this->createMock(IRequestHandler::class);
        $this->authenticator->expects($this->once())
            ->method('authenticate')
            ->with($request, $schemeName)
            ->willReturn(AuthenticationResult::fail('foo'));
        $this->authenticator->expects($this->once())
            ->method('challenge')
            ->with($request, $this->callback(fn (IResponse $response): bool => $response->getStatusCode() === HttpStatusCode::Unauthorized), $schemeName);
        $next->expects($this->never())
            ->method('handle')
            ->willReturn($response);
        $this->assertSame(HttpStatusCode::Unauthorized, $this->middleware->handle($request, $next)->getStatusCode());
    }

    /**
     * @param string|null $schemeName The scheme name or null
     */
    #[DataProvider('getSchemeNames')]
    public function testHandlingPassingAuthenticationResultCallsNextRequestHandler(?string $schemeName): void
    {
        $this->middleware->setParameters(['schemeName' => $schemeName]);
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        $next = $this->createMock(IRequestHandler::class);
        $this->authenticator->expects($this->once())
            ->method('authenticate')
            ->with($request, $schemeName)
            ->willReturn(AuthenticationResult::pass($this->createMock(IPrincipal::class)));
        $next->expects($this->once())
            ->method('handle')
            ->willReturn($response);
        $this->assertSame($response, $this->middleware->handle($request, $next));
    }
}
