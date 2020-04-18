<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\PsrAdapters\Tests\Psr7;

use Aphiria\Collections\KeyValuePair;
use Aphiria\IO\Streams\Stream;
use Aphiria\Net\Http\Formatting\RequestParser;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\MultipartBody;
use Aphiria\Net\Http\MultipartBodyPart;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use Aphiria\PsrAdapters\Psr7\Psr7Factory;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response as Psr7Response;
use Nyholm\Psr7\ServerRequest as Psr7Request;
use Nyholm\Psr7\Stream as Psr7Stream;
use Nyholm\Psr7\UploadedFile;
use Nyholm\Psr7\Uri as Psr7Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Tests the PSR-7 factory
 */
class Psr7FactoryTest extends TestCase
{
    private Psr7Factory $psr7Factory;

    protected function setUp(): void
    {
        $psr17Factory = new Psr17Factory();
        $this->psr7Factory = new Psr7Factory(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory
        );
    }

    public function testCreateAphiriaRequestSetsSameBody(): void
    {
        $psr7Body = Psr7Stream::create('foo');
        $psr7Request = new Psr7Request('GET', 'https://example.com', [], $psr7Body);
        $aphiriaRequest = $this->psr7Factory->createAphiriaRequest($psr7Request);
        $this->assertEquals('foo', $aphiriaRequest->getBody()->readAsString());
    }

    public function testCreateAphiriaRequestSetsParsedBody(): void
    {
        $psr7Body = Psr7Stream::create('foo');
        $psr7Request = (new Psr7Request('GET', 'https://example.com', [], $psr7Body))
            ->withParsedBody($this);
        $aphiriaRequest = $this->psr7Factory->createAphiriaRequest($psr7Request);
        $this->assertSame($this, $aphiriaRequest->getProperties()->get('__APHIRIA_PARSED_BODY'));
    }

    public function testCreateAphiriaRequestSetsSameHeaders(): void
    {
        $psr7Request = new Psr7Request('GET', 'https://example.com', ['Foo' => 'bar', 'Baz' => 'blah']);
        $aphiriaRequest = $this->psr7Factory->createAphiriaRequest($psr7Request);
        $this->assertEquals(['bar'], $aphiriaRequest->getHeaders()->get('Foo'));
        $this->assertEquals(['blah'], $aphiriaRequest->getHeaders()->get('Baz'));
    }

    public function testCreateAphiriaRequestSetsSameProperties(): void
    {
        $psr7Request = (new Psr7Request('GET', 'https://example.com'))
            ->withAttribute('foo', 'bar')
            ->withAttribute('baz', 'blah');
        $aphiriaRequest = $this->psr7Factory->createAphiriaRequest($psr7Request);
        $this->assertEquals('bar', $aphiriaRequest->getProperties()->get('foo'));
        $this->assertEquals('blah', $aphiriaRequest->getProperties()->get('baz'));
    }

    public function testCreateAphiriaRequestSetsSameUri(): void
    {
        $psr7Request = new Psr7Request('GET', 'https://dave:abc123@example.com?foo=bar#baz=blah');
        $aphiriaRequest = $this->psr7Factory->createAphiriaRequest($psr7Request);
        $this->assertEquals('https://dave:abc123@example.com?foo=bar#baz=blah', (string)$aphiriaRequest->getUri());
    }

    public function testCreateAphiriaRequestWithFileUploadsCreatesMultipartRequest(): void
    {
        $psr7Request = (new Psr7Request('GET', 'https://example.com', ['Content-Type' => 'multipart/form-data; boundary=--test']))
            ->withUploadedFiles([
                // Test a file with everything
                'foo' => new UploadedFile(Psr7Stream::create('foo'), 3, \UPLOAD_ERR_OK, 'foo.png', 'image/png'),
                // Test a file without a MIME type
                'bar' => new UploadedFile(Psr7Stream::create('bar'), 3, \UPLOAD_ERR_OK, 'bar.png'),
                // Test a file without a filename
                'baz' => new UploadedFile(Psr7Stream::create('baz'), 3, \UPLOAD_ERR_OK)
            ]);
        $aphiriaRequest = $this->psr7Factory->createAphiriaRequest($psr7Request);
        $aphiriaMultipartBody = (new RequestParser())->readAsMultipart($aphiriaRequest);
        $aphiriaMultipartBodyParts = $aphiriaMultipartBody->getParts();
        $this->assertCount(3, $aphiriaMultipartBodyParts);
        $this->assertEquals('foo', $aphiriaMultipartBodyParts[0]->getBody()->readAsString());
        $this->assertEquals('image/png', $aphiriaMultipartBodyParts[0]->getHeaders()->getFirst('Content-Type'));
        $this->assertEquals(
            'name=foo; filename=foo.png',
            $aphiriaMultipartBodyParts[0]->getHeaders()->getFirst('Content-Disposition')
        );
        $this->assertEquals('bar', $aphiriaMultipartBodyParts[1]->getBody()->readAsString());
        $this->assertFalse($aphiriaMultipartBodyParts[1]->getHeaders()->containsKey('Content-Type'));
        $this->assertEquals(
            'name=bar; filename=bar.png',
            $aphiriaMultipartBodyParts[1]->getHeaders()->getFirst('Content-Disposition')
        );
        $this->assertEquals('baz', $aphiriaMultipartBodyParts[2]->getBody()->readAsString());
        $this->assertFalse($aphiriaMultipartBodyParts[2]->getHeaders()->containsKey('Content-Type'));
        $this->assertEquals(
            'name=baz',
            $aphiriaMultipartBodyParts[2]->getHeaders()->getFirst('Content-Disposition')
        );
    }

    public function testCreateAphiriaResponseSetsSameBody(): void
    {
        $psr7Body = Psr7Stream::create('foo');
        $psr7Response = new Psr7Response(200, [], $psr7Body);
        $aphiriaResponse = $this->psr7Factory->createAphiriaResponse($psr7Response);
        $this->assertEquals('foo', $aphiriaResponse->getBody()->readAsString());
    }

    public function testCreateAphiriaResponseSetsSameHeaders(): void
    {
        $psr7Response = new Psr7Response(200, ['Foo' => ['bar'], 'Baz' => ['blah']]);
        $aphiriaResponse = $this->psr7Factory->createAphiriaResponse($psr7Response);
        $this->assertEquals(['bar'], $aphiriaResponse->getHeaders()->get('Foo'));
        $this->assertEquals(['blah'], $aphiriaResponse->getHeaders()->get('Baz'));
    }

    public function testCreateAphiriaResponseSetsSameProtocolVersion(): void
    {
        $psr7Response = new Psr7Response(200);
        $aphiriaResponse = $this->psr7Factory->createAphiriaResponse($psr7Response);
        $this->assertEquals(1.1, $aphiriaResponse->getProtocolVersion());
    }

    public function testCreateAphiriaResponseSetsSameReasonPhrase(): void
    {
        $psr7Response = new Psr7Response(200);
        $aphiriaResponse = $this->psr7Factory->createAphiriaResponse($psr7Response);
        $this->assertEquals('OK', $aphiriaResponse->getReasonPhrase());
    }

    public function testCreateAphiriaResponseSetsSameStatusCode(): void
    {
        $psr7Response = new Psr7Response(200);
        $aphiriaResponse = $this->psr7Factory->createAphiriaResponse($psr7Response);
        $this->assertEquals(200, $aphiriaResponse->getStatusCode());
    }

    public function testCreateAphiriaStreamCreatesStreamWithSameContents(): void
    {
        $psr7Stream = Psr7Stream::create('foo');
        $this->assertEquals('foo', (string)$this->psr7Factory->createAphiriaStream($psr7Stream));
    }

    public function testCreateAphiriaUriCreatesUriWithAllProperties(): void
    {
        $expectedUri = 'https://dave:abc123@example.com/path?foo=bar#baz=blah';
        $psr7Uri = new Psr7Uri($expectedUri);
        $aphiriaUri = $this->psr7Factory->createAphiriaUri($psr7Uri);
        $this->assertEquals($expectedUri, (string)$aphiriaUri);
    }

    public function testCreatePsr7RequestForMultipartRequestSetsSameUploadedFiles(): void
    {
        $aphiriaHeaders = new HttpHeaders([new KeyValuePair('Content-Type', 'multipart/form-data; boundary=--test')]);
        $file1BodyPart = new MultipartBodyPart(
            new HttpHeaders([
                new KeyValuePair('Content-Disposition', 'name="file1"; filename="foo.png"'),
                new KeyValuePair('Content-Type', 'image/png')
            ]),
            new StringBody('file1contents')
        );
        $file2BodyPart = new MultipartBodyPart(
            new HttpHeaders([
                new KeyValuePair('Content-Disposition', 'name="file2"; filename="bar.png"'),
                new KeyValuePair('Content-Type', 'image/png')
            ]),
            new StringBody('file2contents')
        );
        $aphiriaRequest = new Request(
            'GET',
            new Uri('https://example.com'),
            $aphiriaHeaders,
            new MultipartBody([$file1BodyPart, $file2BodyPart], '--test')
        );
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        /** @var UploadedFileInterface[] $psr7UploadedFiles */
        $psr7UploadedFiles = $psr7Request->getUploadedFiles();
        $this->assertCount(2, $psr7UploadedFiles);
        $this->assertEquals('file1contents', (string)$psr7UploadedFiles['file1']->getStream());
        $this->assertEquals('foo.png', $psr7UploadedFiles['file1']->getClientFilename());
        $this->assertEquals('image/png', $psr7UploadedFiles['file1']->getClientMediaType());
        $this->assertEquals(\UPLOAD_ERR_OK, $psr7UploadedFiles['file1']->getError());
        $this->assertEquals('file2contents', (string)$psr7UploadedFiles['file2']->getStream());
        $this->assertEquals('bar.png', $psr7UploadedFiles['file2']->getClientFilename());
        $this->assertEquals('image/png', $psr7UploadedFiles['file2']->getClientMediaType());
        $this->assertEquals(\UPLOAD_ERR_OK, $psr7UploadedFiles['file2']->getError());
    }

    public function testCreatePsr7RequestForMultipartRequestWithoutNameDefaultsToUsingIndex(): void
    {
        $aphiriaHeaders = new HttpHeaders([new KeyValuePair('Content-Type', 'multipart/form-data; boundary=--test')]);
        $fileBodyPart = new MultipartBodyPart(
            new HttpHeaders([
                new KeyValuePair('Content-Disposition', 'filename="foo.png"'),
                new KeyValuePair('Content-Type', 'image/png')
            ]),
            new StringBody('filecontents')
        );
        $aphiriaRequest = new Request(
            'GET',
            new Uri('https://example.com'),
            $aphiriaHeaders,
            new MultipartBody([$fileBodyPart], '--test')
        );
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        /** @var UploadedFileInterface[] $psr7UploadedFiles */
        $psr7UploadedFiles = $psr7Request->getUploadedFiles();
        $this->assertCount(1, $psr7UploadedFiles);
        $this->assertEquals('filecontents', (string)$psr7UploadedFiles['0']->getStream());
        $this->assertEquals('foo.png', $psr7UploadedFiles['0']->getClientFilename());
        $this->assertEquals('image/png', $psr7UploadedFiles['0']->getClientMediaType());
        $this->assertEquals(\UPLOAD_ERR_OK, $psr7UploadedFiles['0']->getError());
    }

    public function testCreatePsr7RequestSetsParsedBodyIfOneIsPresent(): void
    {
        $aphiriaRequest = new Request('GET', new Uri('https://example.com'));
        $aphiriaRequest->getProperties()->add('__APHIRIA_PARSED_BODY', $this);
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        $this->assertSame($this, $psr7Request->getParsedBody());
    }

    public function testCreatePsr7RequestSetsSameBody(): void
    {
        $aphiriaBody = new StringBody('foo');
        $aphiriaRequest = new Request('GET', new Uri('https://example.com'), null, $aphiriaBody);
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        $this->assertEquals('foo', (string)$psr7Request->getBody());
    }

    public function testCreatePsr7RequestSetsSameCookies(): void
    {
        $headers = new HttpHeaders([
            new KeyValuePair('Cookie', 'foo=bar; baz=blah')
        ]);
        $aphiriaRequest = new Request('GET', new Uri('https://example.com'), $headers);
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'blah'], $psr7Request->getCookieParams());
    }

    public function testCreatePsr7RequestSetsSameHeaders(): void
    {
        $aphiriaHeaders = new HttpHeaders();
        $aphiriaHeaders->add('Foo', 'bar');
        $aphiriaHeaders->add('Baz', 'blah');
        $aphiriaRequest = new Request('GET', new Uri('https://example.com'), $aphiriaHeaders);
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        $this->assertEquals(['bar'], $psr7Request->getHeaders()['Foo']);
        $this->assertEquals(['blah'], $psr7Request->getHeaders()['Baz']);
    }

    public function testCreatePsr7RequestSetsSameHttpMethod(): void
    {
        $aphiriaRequest = new Request('GET', new Uri('https://example.com'));
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        $this->assertEquals('GET', $psr7Request->getMethod());
    }

    public function testCreatePsr7RequestSetsSameProperties(): void
    {
        $aphiriaRequest = new Request('GET', new Uri('https://example.com'));
        $aphiriaRequest->getProperties()->add('foo', 'bar');
        $aphiriaRequest->getProperties()->add('baz', 'blah');
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'blah'], $psr7Request->getAttributes());
    }

    public function testCreatePsr7RequestSetsSameQueryStringParams(): void
    {
        $aphiriaRequest = new Request('GET', new Uri('https://example.com?foo=bar&baz=blah'));
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'blah'], $psr7Request->getQueryParams());
    }

    public function testCreatePsr7RequestSetsSameUri(): void
    {
        $aphiriaRequest = new Request('GET', new Uri('https://example.com'));
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        $this->assertEquals('https://example.com', (string)$psr7Request->getUri());
    }

    public function testCreatePsr7ResponseSetsSameBody(): void
    {
        $aphiriaBody = new StringBody('foo');
        $aphiriaResponse = new Response(200, null, $aphiriaBody);
        $psr7Response = $this->psr7Factory->createPsr7Response($aphiriaResponse);
        $this->assertEquals('foo', (string)$psr7Response->getBody());
    }

    public function testCreatePsr7ResponseSetsSameHeaders(): void
    {
        $headers = new HttpHeaders([
            new KeyValuePair('Foo', 'bar'),
            new KeyValuePair('Baz', 'blah')
        ]);
        $aphiriaResponse = new Response(200, $headers);
        $psr7Response = $this->psr7Factory->createPsr7Response($aphiriaResponse);
        $this->assertEquals(['Foo' => ['bar'], 'Baz' => ['blah']], $psr7Response->getHeaders());
    }

    public function testCreatePsr7ResponseSetsSameProtocolVersion(): void
    {
        $aphiriaResponse = new Response(200);
        $psr7Response = $this->psr7Factory->createPsr7Response($aphiriaResponse);
        $this->assertEquals(1.1, $psr7Response->getProtocolVersion());
    }

    public function testCreatePsr7ResponseSetsSameReasonPhrase(): void
    {
        $aphiriaResponse = new Response(200);
        $psr7Response = $this->psr7Factory->createPsr7Response($aphiriaResponse);
        $this->assertEquals('OK', $psr7Response->getReasonPhrase());
    }

    public function testCreatePsr7ResponseSetsSameStatusCode(): void
    {
        $aphiriaResponse = new Response(200);
        $psr7Response = $this->psr7Factory->createPsr7Response($aphiriaResponse);
        $this->assertEquals(200, $psr7Response->getStatusCode());
    }

    public function testCreatePsr7StreamCreatesWorkingStream(): void
    {
        $aphiriaStream = new Stream(fopen('php://temp', 'r+b'));
        $aphiriaStream->write('foo');
        $psr7Stream = $this->psr7Factory->createPsr7Stream($aphiriaStream);
        $psr7Stream->rewind();
        $this->assertEquals(3, $psr7Stream->getSize());
        $this->assertEquals('foo', $psr7Stream->getContents());
    }

    public function testCreatePsr7UploadedFilesForMultipartRequestCreatesUploadedFiles(): void
    {
        $aphiriaHeaders = new HttpHeaders([new KeyValuePair('Content-Type', 'multipart/form-data; boundary=--test')]);
        $file1BodyPart = new MultipartBodyPart(
            new HttpHeaders([
                new KeyValuePair('Content-Disposition', 'name="file1"; filename="foo.png"'),
                new KeyValuePair('Content-Type', 'image/png')
            ]),
            new StringBody('file1contents')
        );
        $file2BodyPart = new MultipartBodyPart(
            new HttpHeaders([
                new KeyValuePair('Content-Disposition', 'name="file2"; filename="bar.png"'),
                new KeyValuePair('Content-Type', 'image/png')
            ]),
            new StringBody('file2contents')
        );
        $aphiriaRequest = new Request(
            'GET',
            new Uri('https://example.com'),
            $aphiriaHeaders,
            new MultipartBody([$file1BodyPart, $file2BodyPart], '--test')
        );
        $psr7UploadedFiles = $this->psr7Factory->createPsr7UploadedFiles($aphiriaRequest);
        $this->assertCount(2, $psr7UploadedFiles);
        $this->assertEquals('file1contents', (string)$psr7UploadedFiles['file1']->getStream());
        $this->assertEquals('foo.png', $psr7UploadedFiles['file1']->getClientFilename());
        $this->assertEquals('image/png', $psr7UploadedFiles['file1']->getClientMediaType());
        $this->assertEquals(\UPLOAD_ERR_OK, $psr7UploadedFiles['file1']->getError());
        $this->assertEquals('file2contents', (string)$psr7UploadedFiles['file2']->getStream());
        $this->assertEquals('bar.png', $psr7UploadedFiles['file2']->getClientFilename());
        $this->assertEquals('image/png', $psr7UploadedFiles['file2']->getClientMediaType());
        $this->assertEquals(\UPLOAD_ERR_OK, $psr7UploadedFiles['file2']->getError());
    }

    public function testCreatePsr7UploadedFilesForMultipartRequestWithPartThatHasEmptyBodyGetsSkipped(): void
    {
        $aphiriaHeaders = new HttpHeaders([new KeyValuePair('Content-Type', 'multipart/form-data; boundary=--test')]);
        $fileBodyPart = new MultipartBodyPart(
            new HttpHeaders([
                new KeyValuePair('Content-Disposition', 'name="file1"; filename="foo.png"'),
                new KeyValuePair('Content-Type', 'image/png')
            ]),
            null
        );
        $aphiriaRequest = new Request(
            'GET',
            new Uri('https://example.com'),
            $aphiriaHeaders,
            new MultipartBody([$fileBodyPart], '--test')
        );
        /**
         * Technically, the request parser will always force a StringBody, even if it's empty.  However, just to add a
         * safe guard, let's double check if the body is null before doing anything.  So, let's fake the request parser
         * allowing null bodies on multipart body parts.
         */
        $aphiriaRequestParser = new class() extends RequestParser {
            public function readAsMultipart($request): ?MultipartBody
            {
                return new MultipartBody([new MultipartBodyPart(new HttpHeaders(), null)]);
            }
        };
        $psr17Factory = new Psr17Factory();
        $psr7Factory = new Psr7Factory(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            null,
            $aphiriaRequestParser
        );
        $this->assertCount(0, $psr7Factory->createPsr7UploadedFiles($aphiriaRequest));
    }

    public function testCreatePsr7UploadedFilesForNonMultipartRequestReturnsEmptyUploadedFiles(): void
    {
        $aphiriaRequest = new Request('GET', new Uri('https://example.com'));
        $this->assertEmpty($this->psr7Factory->createPsr7UploadedFiles($aphiriaRequest));
    }

    public function testCreatePsr7UriCreatesUriWithAllProperties(): void
    {
        $expectedUri = 'https://dave:abc123@example.com/path?foo=bar#baz=blah';
        $aphiriaUri = new Uri($expectedUri);
        $psr7Uri = $this->psr7Factory->createPsr7Uri($aphiriaUri);
        $this->assertEquals($expectedUri, (string)$psr7Uri);
    }
}
