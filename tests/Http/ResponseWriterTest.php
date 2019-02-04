<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Tests\Http;

use Opulence\IO\Streams\Stream;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\IHttpBody;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\ResponseWriter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the response writer
 */
class ResponseWriterTest extends TestCase
{
    /** @var ResponseWriter The response writer to use in tests */
    private $writer;
    /** @var Stream The output stream to use in tests */
    private $outputStream;
    /** @var IHttpResponseMessage|MockObject The response to use in tests */
    private $response;
    /** @var HttpHeaders The response headers to use in tests */
    private $headers;
    /** @var IHttpBody|MockObject the response body to use in tests */
    private $body;

    public function setUp(): void
    {
        $this->outputStream = new Stream(fopen('php://temp', 'r+b'));
        $this->writer = new ResponseWriter($this->outputStream);
        $this->headers = new HttpHeaders();
        $this->body = $this->createMock(IHttpBody::class);
        // The body will always get written to the output stream in every test
        $this->body->expects($this->once())
            ->method('writeToStream')
            ->with($this->outputStream);

        // Set up the response
        $this->response = $this->createMock(IHttpResponseMessage::class);
        $this->response->expects($this->any())
            ->method('getHeaders')
            ->willReturn($this->headers);
        $this->response->expects($this->any())
            ->method('getBody')
            ->willReturn($this->body);
        $this->response->expects($this->any())
            ->method('getProtocolVersion')
            ->willReturn('1.1');
        $this->response->expects($this->any())
            ->method('getStatusCode')
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
