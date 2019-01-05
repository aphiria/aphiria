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
use Opulence\Serialization\Encoding\EncodingContext;
use Opulence\Serialization\Encoding\ScalarEncoder;

/**
 * Tests the scalar encoder
 */
class ScalarEncoderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ScalarEncoder The encoder to use in tests */
    private $scalarEncoder;

    public function setUp(): void
    {
        $this->scalarEncoder = new ScalarEncoder();
    }

    public function testDecodingNonScalarThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->scalarEncoder->decode(['foo'], 'string[]', new EncodingContext());
    }

    public function testDecodingScalarsNormalizesType(): void
    {
        $this->assertTrue($this->scalarEncoder->decode(1, 'bool', new EncodingContext()));
        $this->assertTrue($this->scalarEncoder->decode(1, 'boolean', new EncodingContext()));
        $this->assertSame(1.0, $this->scalarEncoder->decode(1.0, 'float', new EncodingContext()));
        $this->assertSame(1.0, $this->scalarEncoder->decode(1.0, 'double', new EncodingContext()));
        $this->assertSame(1, $this->scalarEncoder->decode(1, 'int', new EncodingContext()));
        $this->assertSame(1, $this->scalarEncoder->decode(1, 'integer', new EncodingContext()));
    }

    public function testEncodingNonScalarThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->scalarEncoder->encode([], new EncodingContext());
    }

    public function testEncodingScalarReturnsValue(): void
    {
        $this->assertSame(123, $this->scalarEncoder->encode(123, new EncodingContext()));
    }
}
