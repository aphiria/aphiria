<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Encoding;

use InvalidArgumentException;
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
        $this->scalarEncoder->decode(['foo'], 'string[]');
    }

    public function testDecodingScalarsNormalizesType(): void
    {
        $this->assertTrue($this->scalarEncoder->decode(1, 'bool'));
        $this->assertTrue($this->scalarEncoder->decode(1, 'boolean'));
        $this->assertEquals(1.0, $this->scalarEncoder->decode(1.0, 'float'));
        $this->assertEquals(1.0, $this->scalarEncoder->decode(1.0, 'double'));
        $this->assertEquals(1, $this->scalarEncoder->decode(1, 'int'));
        $this->assertEquals(1, $this->scalarEncoder->decode(1, 'integer'));
    }

    public function testEncodingNonScalarThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->scalarEncoder->encode([]);
    }

    public function testEncodingScalarReturnsValue(): void
    {
        $this->assertSame(123, $this->scalarEncoder->encode(123));
    }
}
