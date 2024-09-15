<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
use Aphiria\Net\Http\Request;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RequestParserTest extends TestCase
{
    private IBody&MockObject $body;
    private Headers $headers;
    private RequestParser $parser;
    private IDictionary $properties;
    private IRequest&MockObject $request;

    protected function setUp(): void
    {
        $this->parser = new RequestParser();
        $this->headers = new Headers();
        $this->body = $this->createMock(IBody::class);
        $this->properties = new HashTable();
        $this->request = $this->createMock(IRequest::class);
        $this->request->method('$headers::get')
            ->willReturn($this->headers);
        $this->request->method('$body::get')
            ->willReturn($this->body);
        $this->request->method('$properties::get')
            ->willReturn($this->properties);
    }

    public static function provideMissingBoundaryRequests(): array
    {
        $headers = new Headers([new KeyValuePair('Content-Type', 'multipart/mixed')]);
        $badRequest = new Request('GET', new Uri('http://localhost'), $headers);
        $badMultipartBodyPart = new MultipartBodyPart($headers, null);

        return [
            [$badRequest],
            [$badMultipartBodyPart]
        ];
    }

    public static function provideValidMultipartRequests(): array
    {
        $expectedBody = 'body';
        $serializedBody = "--boundary\r\nFoo: bar\r\nBaz: blah\r\n\r\n$expectedBody\r\n--boundary--";
        $headers = new Headers([new KeyValuePair('Content-Type', 'multipart/form-data; boundary=boundary')]);
        $body = new StringBody($serializedBody);
        $request = new Request('POST', new Uri('http://localhost'), $headers, $body);
        $multipartBodyPart = new MultipartBodyPart($headers, $body);

        return [
            [$request, $expectedBody],
            [$multipartBodyPart, $expectedBody]
        ];
    }

    public function testGettingActualMimeTypeReturnsCorrectMimeType(): void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('<?xml version="1.0"?><foo />');
        $this->assertSame('text/xml', $this->parser->getActualMimeType($this->request));
    }

    public function testGettingClientIPAddressReturnsNullWhenPropertyIsNotSet(): void
    {
        $this->assertNull($this->parser->getClientIPAddress($this->request));
    }

    public function testGettingClientIPAddressReturnsPropertyValueWhenPropertyIsSet(): void
    {
        $this->properties->add('CLIENT_IP_ADDRESS', '127.0.0.1');
        $this->assertSame('127.0.0.1', $this->parser->getClientIPAddress($this->request));
    }

    public function testGettingClientMimeTypeForMultipartWithContentTypeReturnsCorrectMimeType(): void
    {
        $bodyPart = new MultipartBodyPart(
            new Headers([new KeyValuePair('Content-Type', 'image/png')]),
            new StringBody('')
        );
        $this->assertSame('image/png', $this->parser->getClientMimeType($bodyPart));
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
        $this->assertSame('0.1', $values[0]->parameters->get('q'));
    }

    public function testParseAcceptHeaderReturnsThem(): void
    {
        $this->headers->add('Accept', 'tex/plain; q=0.1');
        $values = $this->parser->parseAcceptHeader($this->request);
        $this->assertCount(1, $values);
        $this->assertSame('0.1', $values[0]->parameters->get('q'));
    }

    public function testParseAcceptLanguageHeaderReturnsThem(): void
    {
        $this->headers->add('Accept-language', 'en; q=0.1');
        $values = $this->parser->parseAcceptLanguageHeader($this->request);
        $this->assertCount(1, $values);
        $this->assertSame('0.1', $values[0]->parameters->get('q'));
    }

    public function testParseContentTypeReturnsContentTypeHeader(): void
    {
        $this->headers->add('Content-Type', 'application/json; charset=utf-8');
        $header = $this->parser->parseContentTypeHeader($this->request);
        $this->assertSame('application/json', $header?->mediaType);
        $this->assertSame('utf-8', $header?->charset);
    }

    public function testParsingCookiesReturnsCorrectValuesWithMultipleCookieValues(): void
    {
        $this->headers->add('Cookie', 'foo=bar; baz=blah');
        $cookies = $this->parser->parseCookies($this->request);
        $this->assertSame('bar', $cookies->get('foo'));
        $this->assertSame('blah', $cookies->get('baz'));
    }

    /**
     * @param IRequest|MultipartBodyPart $request The request to test
     */
    #[DataProvider('provideMissingBoundaryRequests')]
    public function testParsingMultipartRequestWithoutBoundaryThrowsException(IRequest|MultipartBodyPart $request): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"boundary" is missing in Content-Type header');
        $this->parser->readAsMultipart($request);
    }

    public function testParsingParametersReturnsCorrectParameters(): void
    {
        $this->headers->add('Foo', 'bar; baz="blah"');
        $values = $this->parser->parseParameters($this->request, 'Foo');
        $this->assertNull($values->get('bar'));
        $this->assertSame('blah', $values->get('baz'));
    }

    public function testParsingQueryStringReturnsDictionaryOfValues(): void
    {
        $request = $this->createMock(IRequest::class);
        $request->expects($this->once())
            ->method('$uri::get')
            ->willReturn(new Uri('http://host.com?foo=bar'));
        $this->assertSame('bar', $this->parser->parseQueryString($request)->get('foo'));
    }

    public function testReadAsFormInputReturnsInput(): void
    {
        $this->body->method('readAsString')
            ->willReturn('foo=bar');
        $value = $this->parser->readAsFormInput($this->request);
        $this->assertSame('bar', $value->get('foo'));
    }

    public function testReadAsJsonReturnsInput(): void
    {
        $this->body->method('readAsString')
            ->willReturn('{"foo":"bar"}');
        $value = $this->parser->readAsJson($this->request);
        $this->assertSame('bar', $value['foo']);
    }

    /**
     * @param IRequest|MultipartBodyPart $request The request to test
     * @param string $expectedBody The expected body
     */
    #[DataProvider('provideValidMultipartRequests')]
    public function testReadingAsMultipartRequestWithHeadersExtractsBody(
        IRequest|MultipartBodyPart $request,
        string $expectedBody
    ): void {
        $multipartBody = $this->parser->readAsMultipart($request);
        $this->assertNotNull($multipartBody);
        $bodyParts = $multipartBody->parts;
        $this->assertCount(1, $bodyParts);
        $body = $bodyParts[0]->body;
        $this->assertNotNull($body);
        $this->assertSame($expectedBody, $body->readAsString());
    }
}
