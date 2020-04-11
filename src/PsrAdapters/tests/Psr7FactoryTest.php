<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\PsrAdapters\Tests;

use Aphiria\Collections\KeyValuePair;
use Aphiria\IO\Streams\Stream;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\MultipartBody;
use Aphiria\Net\Http\MultipartBodyPart;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use Aphiria\PsrAdapters\Psr7Factory;
use Nyholm\Psr7\Factory\Psr17Factory;
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
        );
    }

    public function testCreateRequestForMultipartRequestSetsSameUploadedFiles(): void
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

    public function testCreateRequestForMultipartRequestWithoutNameDefaultsToUsingIndex(): void
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

    public function testCreateRequestSetsParsedBodyIfOneIsPresent(): void
    {
        $aphiriaRequest = new Request('GET', new Uri('https://example.com'));
        $aphiriaRequest->getProperties()->add('__APHIRIA_PARSED_BODY', $this);
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        $this->assertSame($this, $psr7Request->getParsedBody());
    }

    public function testCreateRequestSetsSameCookies(): void
    {
        $headers = new HttpHeaders([
            new KeyValuePair('Cookie', 'foo=bar; baz=blah')
        ]);
        $aphiriaRequest = new Request('GET', new Uri('https://example.com'), $headers);
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'blah'], $psr7Request->getCookieParams());
    }

    public function testCreateRequestSetsSameHeaders(): void
    {
        $aphiriaHeaders = new HttpHeaders();
        $aphiriaHeaders->add('Foo', 'bar');
        $aphiriaHeaders->add('Baz', 'blah');
        $aphiriaRequest = new Request('GET', new Uri('https://example.com'), $aphiriaHeaders);
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        $this->assertEquals(['bar'], $psr7Request->getHeaders()['Foo']);
        $this->assertEquals(['blah'], $psr7Request->getHeaders()['Baz']);
    }

    public function testCreateRequestSetsSameHttpMethod(): void
    {
        $aphiriaRequest = new Request('GET', new Uri('https://example.com'));
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        $this->assertEquals('GET', $psr7Request->getMethod());
    }

    public function testCreateRequestSetsSameProperties(): void
    {
        $aphiriaRequest = new Request('GET', new Uri('https://example.com'));
        $aphiriaRequest->getProperties()->add('foo', 'bar');
        $aphiriaRequest->getProperties()->add('baz', 'blah');
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'blah'], $psr7Request->getAttributes());
    }

    public function testCreateRequestSetsSameQueryStringParams(): void
    {
        $aphiriaRequest = new Request('GET', new Uri('https://example.com?foo=bar&baz=blah'));
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'blah'], $psr7Request->getQueryParams());
    }

    public function testCreateRequestSetsSameUri(): void
    {
        $aphiriaRequest = new Request('GET', new Uri('https://example.com'));
        $psr7Request = $this->psr7Factory->createPsr7Request($aphiriaRequest);
        $this->assertEquals('https://example.com', (string)$psr7Request->getUri());
    }

    public function testCreateResponseSetsSameBody(): void
    {
        $aphiriaBody = new StringBody('foo');
        $aphiriaResponse = new Response(200, null, $aphiriaBody);
        $psr7Response = $this->psr7Factory->createPsr7Response($aphiriaResponse);
        $this->assertEquals('foo', (string)$psr7Response->getBody());
    }

    public function testCreateResponseSetsSameHeaders(): void
    {
        $headers = new HttpHeaders([
            new KeyValuePair('Foo', 'bar'),
            new KeyValuePair('Baz', 'blah')
        ]);
        $aphiriaResponse = new Response(200, $headers);
        $psr7Response = $this->psr7Factory->createPsr7Response($aphiriaResponse);
        $this->assertEquals(['Foo' => ['bar'], 'Baz' => ['blah']], $psr7Response->getHeaders());
    }

    public function testCreateResponseSetsSameProtocolVersion(): void
    {
        $aphiriaResponse = new Response(200);
        $psr7Response = $this->psr7Factory->createPsr7Response($aphiriaResponse);
        $this->assertEquals(1.1, $psr7Response->getProtocolVersion());
    }

    public function testCreateResponseSetsSameReasonPhrase(): void
    {
        $aphiriaResponse = new Response(200);
        $psr7Response = $this->psr7Factory->createPsr7Response($aphiriaResponse);
        $this->assertEquals('OK', $psr7Response->getReasonPhrase());
    }

    public function testCreateResponseSetsSameStatusCode(): void
    {
        $aphiriaResponse = new Response(200);
        $psr7Response = $this->psr7Factory->createPsr7Response($aphiriaResponse);
        $this->assertEquals(200, $psr7Response->getStatusCode());
    }

    public function testCreateStreamCreatesWorkingStream(): void
    {
        $aphiriaStream = new Stream(fopen('php://temp', 'r+b'));
        $aphiriaStream->write('foo');
        $psr7Stream = $this->psr7Factory->createPsr7Stream($aphiriaStream);
        $psr7Stream->rewind();
        $this->assertEquals(3, $psr7Stream->getSize());
        $this->assertEquals('foo', $psr7Stream->getContents());
    }
}
