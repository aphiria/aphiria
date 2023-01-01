<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http;

use Aphiria\Collections\KeyValuePair;
use Aphiria\IO\Streams\ResourceStream;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StreamResponseWriter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StreamResponseWriterTest extends TestCase
{
    private StreamResponseWriter $writer;
    private ResourceStream $outputStream;
    private IResponse&MockObject $response;
    private IBody&MockObject $body;

    protected function setUp(): void
    {
        $this->outputStream = new ResourceStream(\fopen('php://temp', 'r+b'));
        $this->writer = new StreamResponseWriter($this->outputStream);
        $this->body = $this->createMock(IBody::class);

        // Set up the response
        $this->response = $this->createMock(IResponse::class);
        $this->response->method('getHeaders')
            ->willReturn(new Headers([new KeyValuePair('Foo', 'bar')]));
        $this->response->method('getBody')
            ->willReturn($this->body);
        $this->response->method('getProtocolVersion')
            ->willReturn('1.1');
        $this->response->method('getStatusCode')
            ->willReturn(HttpStatusCode::Ok);
        $this->response->method('getReasonPhrase')
            ->willReturn('OK');
    }

    /**
     * Gets a list of headers that should be not be concatenated
     *
     * @return list<list<mixed>> The list of parameters to use
     */
    public function getHeadersThatShouldNotBeConcatenated(): array
    {
        return [
            ['Set-Cookie', ['foo=bar', 'baz=blah']],
            ['Www-Authenticate', ['Basic', 'Basic realm="foo"']],
            ['Proxy-Authenticate', ['Basic', 'Basic realm="foo"']]
        ];
    }

    /**
     * @runInSeparateProcess
     */
    public function testBodyIsWrittenToOutputStream(): void
    {
        $this->body->expects($this->once())
            ->method('writeToStream')
            ->with($this->outputStream);
        $this->writer->writeResponse($this->response);
    }

    /**
     * @param string $headerName The name of the header
     * @param list<mixed> $headerValues The list of header values
     * @dataProvider getHeadersThatShouldNotBeConcatenated
     */
    public function testWritingResponseDoesNotConcatenateSelectHeaders(string $headerName, array $headerValues): void
    {
        $expectedHeaders = ['HTTP/1.1 200 OK'];
        $response = new Response();

        foreach ($headerValues as $headerValue) {
            $response->getHeaders()->add($headerName, $headerValue, true);
            $expectedHeaders[] = "$headerName: $headerValue";
        }

        $responseWriter = new class ($this->outputStream) extends StreamResponseWriter {
            public array $headers = [];

            public function header(string $value, bool $replace = true, int $statusCode = null): void
            {
                $this->headers[] = $value;
            }

            public function headersAreSent(): bool
            {
                return false;
            }
        };
        $responseWriter->writeResponse($response);
        $this->assertSame($expectedHeaders, $responseWriter->headers);
    }

    public function testWritingResponseWhenHeadersAreSentDoesNotDoAnything(): void
    {
        $writer = new class ($this->outputStream) extends StreamResponseWriter {
            public function headersAreSent(): bool
            {
                return true;
            }
        };
        $writer->writeResponse($this->response);
        // Dummy assertion
        $this->assertTrue(true);
    }
}
