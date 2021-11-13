<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions\Tests\Middleware;

use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use Aphiria\Sessions\ISession;
use Aphiria\Sessions\Middleware\Session;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SessionHandlerInterface;

class SessionTest extends TestCase
{
    private ISession&MockObject $session;
    private \SessionHandlerInterface&MockObject $sessionHandler;
    private Headers $requestHeaders;
    private IRequest&MockObject $request;
    private Headers $responseHeaders;
    private IResponse&MockObject $response;
    private IRequestHandler&MockObject $next;

    protected function setUp(): void
    {
        $this->session = $this->createMock(ISession::class);
        $this->sessionHandler = $this->createMock(SessionHandlerInterface::class);
        $this->requestHeaders = new Headers();
        $this->request = $this->createMock(IRequest::class);
        $this->request->method('getHeaders')
            ->willReturn($this->requestHeaders);
        $this->responseHeaders = new Headers();
        $this->response = $this->createMock(IResponse::class);
        $this->response->method('getHeaders')
            ->willReturn($this->responseHeaders);
        $this->next = $this->createMock(IRequestHandler::class);
        $this->next->expects($this->once())
            ->method('handle')
            ->with($this->request)
            ->willReturn($this->response);
    }

    public function testGcIsRunIfWeMeetChance(): void
    {
        $this->session->method('getId')
            ->willReturn('foo');
        $this->sessionHandler->expects($this->once())
            ->method('read')
            ->with('foo')
            ->willReturn('bar');
        $this->sessionHandler->expects($this->once())
            ->method('gc');
        $middleware = new Session(
            $this->session,
            $this->sessionHandler,
            3600,
            'session',
            null,
            null,
            false,
            true,
            1.1 // The GC checks uses '<'.  So, to ensure we always do GC, set it at higher than 100%
        );
        $middleware->handle($this->request, $this->next);
    }

    public function testSessionFlashDataIsAged(): void
    {
        $this->session->method('getId')
            ->willReturn('foo');
        $this->sessionHandler->expects($this->once())
            ->method('read')
            ->with('foo')
            ->willReturn('bar');
        $this->session->expects($this->once())
            ->method('ageFlashData');
        $middleware = new Session(
            $this->session,
            $this->sessionHandler,
            3600,
            'session',
            null,
            null,
            false,
            true,
            0 // Make sure GC doesn't happen
        );
        $middleware->handle($this->request, $this->next);
    }

    public function testSessionDataIsWrittenToResponseCookie(): void
    {
        $this->session->method('getId')
            ->willReturn('foo');
        $this->sessionHandler->expects($this->once())
            ->method('read')
            ->with('foo')
            ->willReturn('bar');
        $this->session->expects($this->once())
            ->method('ageFlashData');
        $middleware = new Session(
            $this->session,
            $this->sessionHandler,
            3600,
            'session',
            '/path',
            'example.com',
            true,
            true,
            0
        );
        $actualResponse = $middleware->handle($this->request, $this->next);
        $this->assertSame(
            'session=foo; Max-Age=3600; Path=/path; Domain=example.com; Secure; HttpOnly; SameSite=lax',
            $actualResponse->getHeaders()->getFirst('Set-Cookie')
        );
    }

    public function testSessionIdIsRegeneratedIfSessionCookieNotPresent(): void
    {
        $this->session->method('getId')
            ->willReturn('foo');
        $this->session->expects($this->once())
            ->method('regenerateId');
        $this->sessionHandler->expects($this->once())
            ->method('read')
            ->with('foo')
            ->willReturn('bar');
        $middleware = new Session(
            $this->session,
            $this->sessionHandler,
            3600,
            'session',
            null,
            null,
            false,
            true,
            0 // Make sure GC doesn't happen
        );
        $middleware->handle($this->request, $this->next);
    }

    public function testSessionIdSetFromCookieNameIfPresent(): void
    {
        $this->request->getHeaders()
            ->add('Cookie', 'session=foo');
        $this->session->expects($this->once())
            ->method('setId')
            ->with('foo');
        $this->session->method('getId')
            ->willReturn('foo');
        $this->session->expects($this->once())
            ->method('setId');
        $this->sessionHandler->expects($this->once())
            ->method('read')
            ->with('foo')
            ->willReturn('bar');
        $middleware = new Session(
            $this->session,
            $this->sessionHandler,
            3600,
            'session',
            null,
            null,
            false,
            true,
            0 // Make sure GC doesn't happen
        );
        $middleware->handle($this->request, $this->next);
    }

    public function testSessionIsOpenedAndVarsAreSet(): void
    {
        $this->session->expects($this->once())
            ->method('regenerateId');
        $this->session->expects($this->once())
            ->method('ageFlashData');
        $this->session->method('getId')
            ->willReturn('foo');
        $this->session->method('setMany')
            ->with(['bar' => 'baz']);
        $this->session->method('getAll')
            ->willReturn(['bar' => 'baz']);
        $this->sessionHandler->method('read')
            ->with('foo')
            ->willReturn(\serialize(['bar' => 'baz']));
        $middleware = new Session(
            $this->session,
            $this->sessionHandler,
            3600,
            'session',
            null,
            null,
            false,
            true,
            0 // Make sure GC doesn't happen
        );
        $middleware->handle($this->request, $this->next);
    }

    public function testSessionIsWritten(): void
    {
        $this->session->method('getId')
            ->willReturn('foo');
        $this->session->method('getAll')
            ->willReturn(['bar' => 'baz']);
        $this->sessionHandler->expects($this->once())
            ->method('read')
            ->with('foo')
            ->willReturn('bar');
        $this->sessionHandler->expects($this->once())
            ->method('write')
            ->with('foo', \serialize(['bar' => 'baz']));
        $middleware = new Session(
            $this->session,
            $this->sessionHandler,
            3600,
            'session',
            null,
            null,
            false,
            true,
            0 // Make sure GC doesn't happen
        );
        $middleware->handle($this->request, $this->next);
    }
}
