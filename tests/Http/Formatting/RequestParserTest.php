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

use Aphiria\Net\Http\Formatting\RequestParser;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\IHttpBody;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\MultipartBodyPart;
use InvalidArgumentException;
use Opulence\Collections\HashTable;
use Opulence\Collections\IDictionary;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the HTTP request message parser
 */
class RequestParserTest extends TestCase
{
    /** @var RequestParser The parser to use in tests */
    private $parser;
    /** @var IHttpRequestMessage|MockObject The request message to use in tests */
    private $request;
    /** @var HttpHeaders The headers to use in tests */
    private $headers;
    /** @var IHttpBody|MockObject The body to use in tests */
    private $body;
    /** @var IDictionary|MockObject The request properties to use in tests */
    private $properties;

    protected function setUp(): void
    {
        $this->parser = new RequestParser();
        $this->headers = new HttpHeaders();
        $this->body = $this->createMock(IHttpBody::class);
        $this->properties = new HashTable();
        $this->request = $this->createMock(IHttpRequestMessage::class);
        $this->request->method('getHeaders')
            ->willReturn($this->headers);
        $this->request->method('getBody')
            ->willReturn($this->body);
        $this->request->method('getProperties')
            ->willReturn($this->properties);
    }

    public function testGettingClientIPAddressReturnsNullWhenPropertyIsNotSet(): void
    {
        $this->assertNull($this->parser->getClientIPAddress($this->request));
    }

    public function testGettingClientIPAddressReturnsPropertyValueWhenPropertyIsSet(): void
    {
        $this->properties->add('CLIENT_IP_ADDRESS', '127.0.0.1');
        $this->assertEquals('127.0.0.1', $this->parser->getClientIPAddress($this->request));
    }

    public function testGettingMimeTypeOfNonRequestNorMultipartBodyPartThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Request must be of type %s or %s', IHttpRequestMessage::class, MultipartBodyPart::class));
        $this->parser->getMimeType([]);
    }

    public function testParsingMultipartRequestWithoutBoundaryThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"boundary" is missing in Content-Type header');
        $this->headers->add('Content-Type', 'multipart/mixed');
        $this->parser->readAsMultipart($this->request);
    }

    public function testParsingNonRequestNorMultipartBodyPartThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Request must be of type %s or %s', IHttpRequestMessage::class, MultipartBodyPart::class));
        $this->parser->readAsMultipart([]);
    }
}
