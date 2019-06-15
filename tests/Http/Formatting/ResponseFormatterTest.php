<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting;

use Aphiria\Net\Http\Formatting\ResponseFormatter;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the HTTP response message formatter
 */
class ResponseFormatterTest extends TestCase
{
    private ResponseFormatter $formatter;
    /** @var IHttpResponseMessage|MockObject The message to use in tests */
    private IHttpResponseMessage $response;
    private HttpHeaders $headers;

    protected function setUp(): void
    {
        $this->formatter = new ResponseFormatter();
        $this->headers = new HttpHeaders();
        $this->response = $this->createMock(IHttpResponseMessage::class);
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
}
