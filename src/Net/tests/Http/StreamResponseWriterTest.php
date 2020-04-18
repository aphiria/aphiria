<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http;

use Aphiria\Collections\KeyValuePair;
use Aphiria\IO\Streams\Stream;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\IHttpBody;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\StreamResponseWriter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StreamResponseWriterTest extends TestCase
{
    private StreamResponseWriter $writer;
    private Stream $outputStream;
    /** @var IHttpResponseMessage|MockObject The response to use in tests */
    private IHttpResponseMessage $response;
    private HttpHeaders $headers;
    /** @var IHttpBody|MockObject the response body to use in tests */
    private IHttpBody $body;

    protected function setUp(): void
    {
        $this->outputStream = new Stream(fopen('php://temp', 'r+b'));
        $this->writer = new StreamResponseWriter($this->outputStream);
        $this->headers = new HttpHeaders([new KeyValuePair('Foo', 'bar')]);
        $this->body = $this->createMock(IHttpBody::class);

        // Set up the response
        $this->response = $this->createMock(IHttpResponseMessage::class);
        $this->response->method('getHeaders')
            ->willReturn($this->headers);
        $this->response->method('getBody')
            ->willReturn($this->body);
        $this->response->method('getProtocolVersion')
            ->willReturn('1.1');
        $this->response->method('getStatusCode')
            ->willReturn(200);
        $this->response->method('getReasonPhrase')
            ->willReturn('OK');
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

    public function testWritingResponseWhenHeadersAreSentDoesNotDoAnything(): void
    {
        $writer = new class($this->outputStream) extends StreamResponseWriter {
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
