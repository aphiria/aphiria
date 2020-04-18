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

use Aphiria\Collections\HashTable;
use Aphiria\Collections\IDictionary;
use Aphiria\Collections\KeyValuePair;
use Aphiria\Net\Http\Formatting\RequestParser;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\IHttpBody;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\MultipartBodyPart;
use Aphiria\Net\Http\StringBody;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RequestParserTest extends TestCase
{
    private RequestParser $parser;
    /** @var IHttpRequestMessage|MockObject The request message to use in tests */
    private IHttpRequestMessage $request;
    private HttpHeaders $headers;
    /** @var IHttpBody|MockObject The body to use in tests */
    private IHttpBody $body;
    private IDictionary $properties;

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

    public function testGettingActualMimeTypeOfNonRequestNorMultipartBodyPartThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Request must be of type %s or %s', IHttpRequestMessage::class, MultipartBodyPart::class));
        $this->parser->getActualMimeType([]);
    }

    public function testGettingActualMimeTypeReturnsCorrectMimeType(): void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('<?xml version="1.0"?><foo />');
        $this->assertEquals('text/xml', $this->parser->getActualMimeType($this->request));
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

    public function testGettingClientMimeTypeForMultipartWithContentTypeReturnsCorrectMimeType(): void
    {
        $bodyPart = new MultipartBodyPart(
            new HttpHeaders([new KeyValuePair('Content-Type', 'image/png')]),
            new StringBody('')
        );
        $this->assertEquals('image/png', $this->parser->getClientMimeType($bodyPart));
    }

    public function testGettingClientMimeTypeForMultipartWithoutContentTypeHeaderReturnsNull(): void
    {
        $bodyPart = new MultipartBodyPart(new HttpHeaders(), new StringBody(''));
        $this->assertNull($this->parser->getClientMimeType($bodyPart));
    }

    public function testGettingClientMimeTypeForMultipartWithUncommonFilenameExtensionReturnsNull(): void
    {
        $bodyPart = new MultipartBodyPart(
            new HttpHeaders([new KeyValuePair('Content-Disposition', 'filename=foo.dave')]),
            new StringBody('')
        );
        $this->assertNull($this->parser->getClientMimeType($bodyPart));
    }

    public function testIsJsonReturnsWhetherOrNotARequestIsJson(): void
    {
        $this->headers->add('Content-Type', 'application/json');
        $this->assertTrue($this->parser->isJson($this->request));
        $this->headers->removeKey('Content-Type');
        $this->assertFalse($this->parser->isJson($this->request));
    }

    public function testParseAcceptCharsetHeaderReturnsThem(): void
    {
        $this->headers->add('Accept-Charset', 'utf-8; q=0.1');
        $values = $this->parser->parseAcceptCharsetHeader($this->request);
        $this->assertCount(1, $values);
        $this->assertEquals(0.1, $values[0]->getParameters()->get('q'));
    }

    public function testParseAcceptHeaderReturnsThem(): void
    {
        $this->headers->add('Accept', 'tex/plain; q=0.1');
        $values = $this->parser->parseAcceptHeader($this->request);
        $this->assertCount(1, $values);
        $this->assertEquals(0.1, $values[0]->getParameters()->get('q'));
    }

    public function testParseAcceptLanguageHeaderReturnsThem(): void
    {
        $this->headers->add('Accept-language', 'en; q=0.1');
        $values = $this->parser->parseAcceptLanguageHeader($this->request);
        $this->assertCount(1, $values);
        $this->assertEquals(0.1, $values[0]->getParameters()->get('q'));
    }

    public function testParseContentTypeHeaderReturnsIt(): void
    {
        $this->headers->add('Content-Type', 'application/json');
        $value = $this->parser->parseContentTypeHeader($this->request);
        $this->assertEquals('application/json', $value->getMediaType());
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

    public function testReadAsFormInputReturnsInput(): void
    {
        $this->body->method('readAsString')
            ->willReturn('foo=bar');
        $value = $this->parser->readAsFormInput($this->request);
        $this->assertEquals('bar', $value->get('foo'));
    }

    public function testReadAsJsonReturnsInput(): void
    {
        $this->body->method('readAsString')
            ->willReturn('{"foo":"bar"}');
        $value = $this->parser->readAsJson($this->request);
        $this->assertEquals('bar', $value['foo']);
    }
}
