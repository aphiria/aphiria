<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Encoding;

use InvalidArgumentException;
use Opulence\Serialization\Encoding\ArrayEncoder;
use Opulence\Serialization\Encoding\EncoderRegistry;
use Opulence\Serialization\Encoding\EncodingContext;
use Opulence\Serialization\Encoding\IEncoder;

/**
 * Tests the array encoder
 */
class ArrayEncoderTest extends \PHPUnit\Framework\TestCase
{
    /** @var EncoderRegistry The encoder registry */
    private $encoders;
    /** @var ArrayEncoder The encoder to use in tests */
    private $arrayEncoder;

    public function setUp(): void
    {
        $this->encoders = new EncoderRegistry();
        $this->arrayEncoder = new ArrayEncoder($this->encoders);
    }

    public function testDecodingCallsParentDecoderOnEachElement(): void
    {
        $context = new EncodingContext();
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('decode')
            ->with(123, 'int', $context)
            ->willReturn(123);
        $encoder->expects($this->at(1))
            ->method('decode')
            ->with(456, 'int', $context)
            ->willReturn(456);
        $this->encoders->registerEncoder('int', $encoder);
        $this->assertEquals([123, 456], $this->arrayEncoder->decode([123, 456], 'int[]', $context));
    }

    public function testDecodingNonArrayThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->arrayEncoder->decode('foo', 'string[]', new EncodingContext());
    }

    public function testDecodingTypeThatDoesNotEndInBracketsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->arrayEncoder->decode(['foo'], 'bar', new EncodingContext());
    }

    public function testEncodingCallsParentEncoderOnEachElement(): void
    {
        $context = new EncodingContext();
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('encode')
            ->with(123, $context)
            ->willReturn(123);
        $encoder->expects($this->at(1))
            ->method('encode')
            ->with(456, $context)
            ->willReturn(456);
        $this->encoders->registerEncoder('int', $encoder);
        $this->assertEquals([123, 456], $this->arrayEncoder->encode([123, 456], $context));
    }

    public function testEncodingThrowInvalidArgumentExceptionWithNonArrayValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->arrayEncoder->encode(12345, new EncodingContext());
    }

    public function testEncodingShouldReturnEmptyArrayWithEmptyArrayValues(): void
    {
        $this->assertCount(0, $this->arrayEncoder->encode([], new EncodingContext()));
    }
}
