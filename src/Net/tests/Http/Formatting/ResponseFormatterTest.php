<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting;

use Aphiria\Net\Http\Formatting\ResponseFormatter;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\Headers\Cookie;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResponseFormatterTest extends TestCase
{
    private ResponseFormatter $formatter;
    private IResponse|MockObject $response;
    private Headers $headers;

    protected function setUp(): void
    {
        $this->formatter = new ResponseFormatter();
        $this->headers = new Headers();
        $this->response = $this->createMock(IResponse::class);
        $this->response->method('getHeaders')
            ->willReturn($this->headers);
    }

    public function testContentTypeHeaderAndBodyAreSetWhenWritingJson(): void
    {
        $this->response->expects($this->once())
            ->method('setBody')
            ->with($this->callback(function (mixed $body) {
                return $body instanceof StringBody && $body->readAsString() === json_encode(['foo' => 'bar']);
            }));
        $this->formatter->writeJson($this->response, ['foo' => 'bar']);
        $this->assertSame('application/json', $this->response->getHeaders()->getFirst('Content-Type'));
    }

    public function testDeletingCookieSetsCookiesToExpire(): void
    {
        $this->formatter->deleteCookie($this->response, 'name', '/path', 'example.com', true, true, 'lax');
        $this->assertSame(
            'name=; Max-Age=0; Path=/path; Domain=example.com; Secure; HttpOnly; SameSite=lax',
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testRedirectingToUriSetsLocationHeaderAndStatusCode(): void
    {
        $this->response->expects($this->once())
            ->method('setStatusCode')
            ->with(301);
        $this->formatter->redirectToUri($this->response, 'http://foo.com', 301);
        $this->assertSame('http://foo.com', $this->headers->getFirst('Location'));
    }

    public function testRedirectingToUriConvertsUriInstanceToStringAndSetsLocationHeaderAndStatusCode(): void
    {
        $this->response->expects($this->once())
            ->method('setStatusCode')
            ->with(301);
        $this->formatter->redirectToUri($this->response, new Uri('http://foo.com'), 301);
        $this->assertSame('http://foo.com', $this->headers->getFirst('Location'));
    }

    public function testSettingCookieSetsCookieInResponseHeader(): void
    {
        $this->formatter->setCookie(
            $this->response,
            new Cookie('name', 'value', 3600, '/path', 'example.com', true, true, 'lax')
        );
        $this->assertSame(
            'name=value; Max-Age=3600; Path=/path; Domain=example.com; Secure; HttpOnly; SameSite=lax',
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testSettingCookiesSetsCookiesInResponseHeader(): void
    {
        $this->formatter->setCookies(
            $this->response,
            [new Cookie('name1', 'value1', 3600), new Cookie('name2', 'value2', 7200)]
        );
        /** @var array<int, string> $cookies */
        $cookies = $this->headers->get('Set-Cookie');
        $this->assertCount(2, $cookies);
        $this->assertSame(
            'name1=value1; Max-Age=3600; HttpOnly; SameSite=lax',
            $cookies[0]
        );
        $this->assertSame(
            'name2=value2; Max-Age=7200; HttpOnly; SameSite=lax',
            $cookies[1]
        );
    }

    public function testWritingInvalidJsonThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->formatter->writeJson($this->response, [9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999]);
    }
}
