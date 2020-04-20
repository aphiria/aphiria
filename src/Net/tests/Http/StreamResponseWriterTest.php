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
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\StreamResponseWriter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StreamResponseWriterTest extends TestCase
{
    private StreamResponseWriter $writer;
    private Stream $outputStream;
    /** @var IResponse|MockObject The response to use in tests */
    private IResponse $response;
    private Headers $headers;
    /** @var IBody|MockObject the response body to use in tests */
    private IBody $body;

    protected function setUp(): void
    {
        $this->outputStream = new Stream(fopen('php://temp', 'r+b'));
        $this->writer = new StreamResponseWriter($this->outputStream);
        $this->headers = new Headers([new KeyValuePair('Foo', 'bar')]);
        $this->body = $this->createMock(IBody::class);

        // Set up the response
        $this->response = $this->createMock(IResponse::class);
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
