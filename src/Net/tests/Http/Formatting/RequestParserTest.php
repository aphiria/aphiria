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
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\MultipartBodyPart;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RequestParserTest extends TestCase
{
    private RequestParser $parser;
    /** @var IRequest|MockObject The request message to use in tests */
    private IRequest $request;
    private Headers $headers;
    /** @var IBody|MockObject The body to use in tests */
    private IBody $body;
    private IDictionary $properties;

    protected function setUp(): void
    {
        $this->parser = new RequestParser();
        $this->headers = new Headers();
        $this->body = $this->createMock(IBody::class);
        $this->properties = new HashTable();
        $this->request = $this->createMock(IRequest::class);
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
        $this->expectExceptionMessage(sprintf('Request must be of type %s or %s', IRequest::class, MultipartBodyPart::class));
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
            new Headers([new KeyValuePair('Content-Type', 'image/png')]),
            new StringBody('')
        );
        $this->assertEquals('image/png', $this->parser->getClientMimeType($bodyPart));
    }

    public function testGettingClientMimeTypeForMultipartWithoutContentTypeHeaderReturnsNull(): void
    {
        $bodyPart = new MultipartBodyPart(new Headers(), new StringBody(''));
        $this->assertNull($this->parser->getClientMimeType($bodyPart));
    }

    public function testGettingClientMimeTypeForMultipartWithUncommonFilenameExtensionReturnsNull(): void
    {
        $bodyPart = new MultipartBodyPart(
            new Headers([new KeyValuePair('Content-Disposition', 'filename=foo.dave')]),
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

    public function testIsMultipartReturnsWhetherOrNotARequestIsMultipart(): void
    {
        $this->headers->add('Content-Type', 'text/plain');
        $this->assertFalse($this->parser->isMultipart($this->request));
        $this->headers->removeKey('Content-Type');
        $this->headers->add('Content-Type', 'multipart/mixed');
        $this->assertTrue($this->parser->isMultipart($this->request));
        $this->headers->removeKey('Content-Type');
        $this->headers->add('Content-Type', 'multipart/form-data');
        $this->assertTrue($this->parser->isMultipart($this->request));
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

    public function testParsingCookiesReturnsCorrectValuesWithMultipleCookieValues(): void
    {
        $this->headers->add('Cookie', 'foo=bar; baz=blah');
        $cookies = $this->parser->parseCookies($this->request);
        $this->assertEquals('bar', $cookies->get('foo'));
        $this->assertEquals('blah', $cookies->get('baz'));
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
        $this->expectExceptionMessage(sprintf('Request must be of type %s or %s', IRequest::class, MultipartBodyPart::class));
        $this->parser->readAsMultipart([]);
    }

    public function testParsingParametersReturnsCorrectParameters(): void
    {
        $this->headers->add('Foo', 'bar; baz="blah"');
        $values = $this->parser->parseParameters($this->request, 'Foo');
        $this->assertNull($values->get('bar'));
        $this->assertEquals('blah', $values->get('baz'));
    }

    public function testParsingQueryStringReturnsDictionaryOfValues(): void
    {
        $request = $this->createMock(IRequest::class);
        $request->expects($this->once())
            ->method('getUri')
            ->willReturn(new Uri('http://host.com?foo=bar'));
        $this->assertEquals('bar', $this->parser->parseQueryString($request)->get('foo'));
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

    public function testReadingAsMultipartRequestWithHeadersExtractsBody(): void
    {
        $this->headers->add('Content-Type', 'multipart/form-data; boundary=boundary');
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn("--boundary\r\nFoo: bar\r\nBaz: blah\r\n\r\nbody\r\n--boundary--");
        $multipartBody = $this->parser->readAsMultipart($this->request);
        $this->assertNotNull($multipartBody);
        $bodyParts = $multipartBody->getParts();
        $this->assertCount(1, $bodyParts);
        $body = $bodyParts[0]->getBody();
        $this->assertNotNull($body);
        $this->assertEquals('body', $body->readAsString());
    }
}
