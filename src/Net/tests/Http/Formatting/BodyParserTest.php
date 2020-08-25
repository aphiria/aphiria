<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting;

use Aphiria\Collections\HashTable;
use Aphiria\Net\Http\Formatting\BodyParser;
use Aphiria\Net\Http\IBody;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class BodyParserTest extends TestCase
{
    private BodyParser $parser;
    /** @var IBody|MockObject The body to use in tests */
    private IBody $body;

    protected function setUp(): void
    {
        $this->parser = new BodyParser();
        $this->body = $this->createMock(IBody::class);
    }

    public function testGettingExistingFormInputReturnsThatInputsValue(): void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('foo=bar');
        $this->assertSame('bar', $this->parser->readAsFormInput($this->body)->get('foo'));
    }

    public function testGettingMimeTypeOfNullBodyReturnsNull(): void
    {
        $this->assertNull($this->parser->getMimeType(null));
    }

    public function testGettingMimeTypeForBodyThatAlreadyHasBeenCheckedReturnsSameMimeType(): void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('<?xml version="1.0"?><foo />');
        $this->assertSame('text/xml', $this->parser->getMimeType($this->body));
        $this->assertSame('text/xml', $this->parser->getMimeType($this->body));
    }

    public function testGettingMimeTypeReturnsCorrectMimeType(): void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('<?xml version="1.0"?><foo />');
        $this->assertSame('text/xml', $this->parser->getMimeType($this->body));
    }

    public function testParsingInputWithFormUrlEncodedBodyThatAlreadyHasBeenCheckedReturnsSameInput(): void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('foo=bar');
        $this->assertSame('bar', $this->parser->readAsFormInput($this->body)->get('foo'));
        $this->assertSame('bar', $this->parser->readAsFormInput($this->body)->get('foo'));
    }

    public function testParsingInputWithFormUrlEncodedBodyReturnsParsedFormData(): void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('foo=bar');
        $this->assertSame('bar', $this->parser->readAsFormInput($this->body)->get('foo'));
    }

    public function testParsingInputWithNullBodyReturnsEmptyDictionary(): void
    {
        $this->assertEquals(new HashTable(), $this->parser->readAsFormInput(null));
    }

    public function testParsingJsonForJsonRequestReturnsJsonDecodedBody(): void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn(json_encode(['foo' => 'bar']));
        $this->assertEquals(['foo' => 'bar'], $this->parser->readAsJson($this->body));
    }

    public function testParsingJsonWithIncorrectlyFormattedJsonThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Body could not be decoded as JSON');
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn("\x0");
        $this->parser->readAsJson($this->body);
    }

    public function testParsingJsonWithNullBodyReturnsEmptyArray(): void
    {
        $this->assertEquals([], $this->parser->readAsJson(null));
    }

    public function testParsingMultipartRequestExtractsHeaders(): void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn("--boundary\r\nFoo: bar\r\nBaz: blah\r\n\r\nbody\r\n--boundary--");
        $multipartBody = $this->parser->readAsMultipart($this->body, 'boundary');
        $this->assertNotNull($multipartBody);
        $bodyParts = $multipartBody->getParts();
        $this->assertCount(1, $bodyParts);
        $this->assertSame('bar', $bodyParts[0]->getHeaders()->getFirst('Foo'));
        $this->assertSame('blah', $bodyParts[0]->getHeaders()->getFirst('Baz'));
    }

    public function testParsingMultipartRequestWithHeadersExtractsBody(): void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn("--boundary\r\nFoo: bar\r\nBaz: blah\r\n\r\nbody\r\n--boundary--");
        $multipartBody = $this->parser->readAsMultipart($this->body, 'boundary');
        $this->assertNotNull($multipartBody);
        $bodyParts = $multipartBody->getParts();
        $this->assertCount(1, $bodyParts);
        $body = $bodyParts[0]->getBody();
        $this->assertNotNull($body);
        $this->assertSame('body', $body->readAsString());
    }

    public function testParsingMultipartRequestWithNullBodyReturnsNull(): void
    {
        $this->assertNull($this->parser->readAsMultipart(null, 'boundary'));
    }

    public function testParsingNestedMultipartBodiesAddsRawChildBodiesToParentsBody(): void
    {
        $bodyString = "--boundary1\r\n" .
            // First nested multipart body
            'Content-Type: multipart/mixed; boundary="boundary2"' .
            "\r\n" .
            "\r\n" .
            'body1' .
            "\r\n" .
            // Second part of the nested multipart body
            '--boundary2' .
            "\r\n" .
            'Content-Type: multipart/mixed; boundary="boundary3"' .
            "\r\n" .
            "\r\n" .
            'body2' .
            "\r\n" .
            '--boundary3--' .
            "\r\n" .
            '--boundary2--' .
            "\r\n" .
            '--boundary1--';
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn($bodyString);
        $multipartBody = $this->parser->readAsMultipart($this->body, 'boundary1');
        $this->assertNotNull($multipartBody);
        $bodyParts = $multipartBody->getParts();
        $this->assertCount(1, $bodyParts);
        $this->assertSame(
            'multipart/mixed; boundary="boundary2"',
            $bodyParts[0]->getHeaders()->getFirst('Content-Type')
        );
        $expectedBodyString = 'body1' .
            "\r\n" .
            '--boundary2' .
            "\r\n" .
            'Content-Type: multipart/mixed; boundary="boundary3"' .
            "\r\n" .
            "\r\n" .
            'body2' .
            "\r\n" .
            '--boundary3--' .
            "\r\n" .
            '--boundary2--';
        $body = $bodyParts[0]->getBody();
        $this->assertNotNull($body);
        $this->assertSame($expectedBodyString, $body->readAsString());
    }
}
