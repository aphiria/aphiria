<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use InvalidArgumentException;
use Opulence\IO\Streams\IStream;
use Opulence\Net\Http\Formatting\JsonMediaTypeFormatter;
use Opulence\Net\Http\Formatting\ModelMapper;
use Opulence\Net\Tests\Http\Formatting\Mocks\User;
use RuntimeException;

/**
 * Tests the JSON media type formatter
 */
class JsonMediaTypeFormatterTest extends \PHPUnit\Framework\TestCase
{
    /** @var JsonMediaTypeFormatter The formatter to use in tests */
    private $formatter;
    /** @var ModelMapper The model mapper to use in tests */
    private $modelMapper;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->modelMapper = new ModelMapper();
        $this->modelMapper->registerMapper(
            User::class,
            function (User $user, ModelMapper $modelMapper) {
                return ['id' => $user->getId(), 'email' => $user->getEmail()];
            },
            function (array $hash, ModelMapper $modelMapper) {
                return new User((int)$hash['id'], $hash['email']);
            }
        );
        $this->formatter = new JsonMediaTypeFormatter($this->modelMapper);
    }

    /**
     * Tests that the correct supported media types are returned
     */
    public function testCorrectSupportedMediaTypesAreReturned() : void
    {
        $this->assertEquals(['application/json', 'text/json'], $this->formatter->getSupportedMediaTypes());
    }

    /**
     * Tests that reading invalid JSON will throw an exception
     */
    public function testReadingInvalidJsonWillThrowException() : void
    {
        $this->expectException(RuntimeException::class);
        $stream = $this->createStreamWithStringBody("\x0");
        $this->formatter->readFromStream(User::class, $stream);
    }

    /**
     * Tests that reading an array of scalars casts each element to the correct type
     */
    public function testReadingArrayOfScalarsCastsEachElementToCorrectType() : void
    {
        $stream = $this->createStreamWithStringBody(json_encode(['1.1', '2.2']));
        $actualValues = $this->formatter->readFromStream('float', $stream, true);
        $this->assertCount(2, $actualValues);
        $this->assertSame(1.1, $actualValues[0]);
        $this->assertSame(2.2, $actualValues[1]);
    }

    /**
     * Tests that reading an object whose decoded value is not a hash throws an exception
     */
    public function testReadingObjectWhoseDecodedValueIsNotHashThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $stream = $this->createStreamWithStringBody(json_encode('foo'));
        $this->formatter->readFromStream(User::class, $stream);
    }

    /**
     * Tests that reading null returns null
     */
    public function testReadingNullReturnsNull() : void
    {
        $stream = $this->createStreamWithStringBody(json_encode(null));
        $this->assertNull($this->formatter->readFromStream('int', $stream));
    }

    /**
     * Tests that reading scalars casts them to the correct types
     */
    public function testReadingScalarsCastsThemToCorrectTypes() : void
    {
        $scalarData = [
            ['int', '1', 1],
            ['integer', '1', 1],
            ['double', '1.1', 1.1],
            ['float', '1.1', 1.1],
            ['string', 'foo', 'foo'],
            ['bool', '1', true],
            ['bool', '0', false],
            ['boolean', '1', true],
            ['boolean', '0', false]
        ];

        foreach ($scalarData as $scalarDatum) {
            $stream = $this->createStreamWithStringBody(json_encode($scalarDatum[1]));
            $this->assertEquals(
                $scalarDatum[2],
                $this->formatter->readFromStream($scalarDatum[0], $stream),
                "Failed to assert that {$scalarDatum[0]} value {$scalarDatum[1]} matches {$scalarDatum[2]}"
            );
        }
    }

    /**
     * Tests that reading an array of serialized objects will use the model mapper to deserialize the objects
     */
    public function testReadingSerializedArrayOfObjectsWillUseModelMapperToDeserializeObjects() : void
    {
        $userHashes = [
            ['id' => 123, 'email' => 'foo@bar.com'],
            ['id' => 456, 'email' => 'baz@blah.com']
        ];
        $stream = $this->createStreamWithStringBody(json_encode($userHashes));
        $users = $this->formatter->readFromStream(User::class, $stream, true);
        $this->assertTrue(is_array($users));
        $this->assertCount(2, $users);
        $this->assertInstanceOf(User::class, $users[0]);
        $this->assertInstanceOf(User::class, $users[1]);
        $this->assertEquals(123, $users[0]->getId());
        $this->assertEquals(456, $users[1]->getId());
        $this->assertEquals('foo@bar.com', $users[0]->getEmail());
        $this->assertEquals('baz@blah.com', $users[1]->getEmail());
    }

    /**
     * Tests that reading a serialized object will use the model mapper to deserialize the object
     */
    public function testReadingSerializedObjectWillUseModelMapperToDeserializeObject() : void
    {
        $stream = $this->createStreamWithStringBody(json_encode(['id' => 123, 'email' => 'foo@bar.com']));
        $user = $this->formatter->readFromStream(User::class, $stream);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(123, $user->getId());
        $this->assertEquals('foo@bar.com', $user->getEmail());
    }

    /**
     * Tests that writing an array of objects uses the model mapper to serialize them
     */
    public function testWritingArrayOfObjectsUsesModelMapperToSerializeThem() : void
    {
        $users = [new User(123, 'foo@bar.com'), new User(456, 'baz@blah.com')];
        $usersHash = [
            ['id' => 123, 'email' => 'foo@bar.com'],
            ['id' => 456, 'email' => 'baz@blah.com']
        ];
        $stream = $this->createStreamThatExpectsBody(json_encode($usersHash));
        $this->formatter->writeToStream($users, $stream);
    }

    /**
     * Tests that writing an array of scalars JSON-encodes those values
     */
    public function testWritingArrayOfScalarsJsonEncodesThoseValues() : void
    {
        $scalars = [1.1, 2.2];
        $stream = $this->createStreamThatExpectsBody(json_encode($scalars));
        $this->formatter->writeToStream($scalars, $stream);
    }

    /**
     * Tests that writing a non-scalar/object throws an exception
     */
    public function testWritingNonScalarNorObjectThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->formatter->writeToStream(function () {
        }, $this->createMock(IStream::class));
    }

    /**
     * Tests that writing an object uses the model mapper to serialize it
     */
    public function testWritingObjectUsesModelMapperToSerializeIt() : void
    {
        $user = new User(123, 'foo@bar.com');
        $stream = $this->createStreamThatExpectsBody(json_encode(['id' => 123, 'email' => 'foo@bar.com']));
        $this->formatter->writeToStream($user, $stream);
    }

    /**
     * Tests that writing a scalar JSON-encodes the value
     */
    public function testWritingScalarJsonEncodesThatValue() : void
    {
        $scalars = [1, 1.1, 'foo', true, false, null];

        foreach ($scalars as $scalar) {
            $stream = $this->createStreamThatExpectsBody(json_encode($scalar));
            $this->formatter->writeToStream($scalar, $stream);
        }
    }

    /**
     * Creates a stream with an expected body that will be written to it
     *
     * @param string $body The expected body of the stream
     * @return IStream|\PHPUnit_Framework_MockObject_MockObject The stream that expects the input body
     */
    private function createStreamThatExpectsBody(string $body) : IStream
    {
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('write')
            ->with($body);

        return $stream;
    }

    /**
     * Creates a stream with a string body
     *
     * @param string $body The body of the stream
     * @return IStream|\PHPUnit_Framework_MockObject_MockObject The stream with the input body as its string body
     */
    private function createStreamWithStringBody(string $body) : IStream
    {
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn($body);

        return $stream;
    }
}
