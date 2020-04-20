<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
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
    /** @var IResponse|MockObject The message to use in tests */
    private IResponse $response;
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
            ->with($this->callback(function ($body) {
                return $body instanceof StringBody && $body->readAsString() === json_encode(['foo' => 'bar']);
            }));
        $this->formatter->writeJson($this->response, ['foo' => 'bar']);
        $this->assertEquals('application/json', $this->response->getHeaders()->getFirst('Content-Type'));
    }

    public function testDeletingCookieSetsCookiesToExpire(): void
    {
        $this->formatter->deleteCookie($this->response, 'name', '/path', 'example.com', true, true, 'lax');
        $this->assertEquals(
            'name=; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Max-Age=0; Path=%2Fpath; Domain=example.com; Secure; HttpOnly; SameSite=lax',
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testRedirectingToUriSetsLocationHeaderAndStatusCode(): void
    {
        $this->response->expects($this->once())
            ->method('setStatusCode')
            ->with(301);
        $this->formatter->redirectToUri($this->response, 'http://foo.com', 301);
        $this->assertEquals('http://foo.com', $this->headers->getFirst('Location'));
    }

    public function testRedirectingToUriConvertsUriInstanceToStringAndSetsLocationHeaderAndStatusCode(): void
    {
        $this->response->expects($this->once())
            ->method('setStatusCode')
            ->with(301);
        $this->formatter->redirectToUri($this->response, new Uri('http://foo.com'), 301);
        $this->assertEquals('http://foo.com', $this->headers->getFirst('Location'));
    }

    public function testRedirectingToUriThatIsNotUriNorStringThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Uri must be instance of %s or string', Uri::class));
        $this->formatter->redirectToUri($this->response, [], 301);
    }

    public function testSettingCookieSetsCookieInResponseHeader(): void
    {
        $this->formatter->setCookie(
            $this->response,
            new Cookie('name', 'value', time() + 3600, '/path', 'example.com', true, true, 'lax')
        );
        $this->assertMatchesRegularExpression(
            '/^name=value; Expires=[^;]+; Max\-Age=3600; Path=%2Fpath; Domain=example\.com; Secure; HttpOnly; SameSite=lax$/',
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testSettingCookiesSetsCookiesInResponseHeader(): void
    {
        $this->formatter->setCookies(
            $this->response,
            [new Cookie('name1', 'value1', time() + 3600), new Cookie('name2', 'value2', time() + 3600)]
        );
        $cookies = $this->headers->get('Set-Cookie');
        $this->assertCount(2, $cookies);
        $this->assertMatchesRegularExpression(
            '/^name1=value1; Expires=[^;]+; Max\-Age=3600; HttpOnly; SameSite=lax$/',
            $cookies[0]
        );
        $this->assertMatchesRegularExpression(
            '/^name2=value2; Expires=[^;]+; Max\-Age=3600; HttpOnly; SameSite=lax$/',
            $cookies[1]
        );
    }

    public function testWritingInvalidJsonThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->formatter->writeJson($this->response, [9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999]);
    }
}
