<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/serialization/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Tests\Encoding;

use Aphiria\Serialization\Encoding\ArrayEncoder;
use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Serialization\Encoding\EncodingContext;
use Aphiria\Serialization\Encoding\IEncoder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the array encoder
 */
class ArrayEncoderTest extends TestCase
{
    /** @var EncoderRegistry The encoder registry */
    private $encoders;
    /** @var ArrayEncoder The encoder to use in tests */
    private $arrayEncoder;

    protected function setUp(): void
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
        $this->expectExceptionMessage('Value must be an array');
        $this->arrayEncoder->decode('foo', 'string[]', new EncodingContext());
    }

    public function testDecodingTypeThatDoesNotEndInBracketsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Type must end in "[]"');
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
        $this->expectExceptionMessage('Value must be an array');
        $this->arrayEncoder->encode(12345, new EncodingContext());
    }

    public function testEncodingShouldReturnEmptyArrayWithEmptyArrayValues(): void
    {
        $this->assertCount(0, $this->arrayEncoder->encode([], new EncodingContext()));
    }
}
