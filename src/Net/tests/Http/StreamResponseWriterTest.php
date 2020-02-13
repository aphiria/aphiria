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

use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\IHttpBody;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\StreamResponseWriter;
use Aphiria\IO\Streams\Stream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the stream response writer
 */
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
        $this->headers = new HttpHeaders();
        $this->body = $this->createMock(IHttpBody::class);
        // The body will always get written to the output stream in every test
        $this->body->expects($this->once())
            ->method('writeToStream')
            ->with($this->outputStream);

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
    }

    /**
     * @runInSeparateProcess
     */
    public function testBodyIsWrittenToOutputStream(): void
    {
        $this->response->expects($this->once())
            ->method('getReasonPhrase')
            ->willReturn(null);
        $this->writer->writeResponse($this->response);
    }
}
