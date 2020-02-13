<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Tests\Encoding;

use Aphiria\Serialization\Encoding\EncodingContext;
use Aphiria\Serialization\Encoding\ScalarEncoder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the scalar encoder
 */
class ScalarEncoderTest extends TestCase
{
    private ScalarEncoder $scalarEncoder;

    protected function setUp(): void
    {
        $this->scalarEncoder = new ScalarEncoder();
    }

    public function testDecodingNonScalarThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Type string[] is an invalid scalar');
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
        $this->expectExceptionMessage('Value must be scalar');
        $this->scalarEncoder->encode([], new EncodingContext());
    }

    public function testEncodingScalarReturnsValue(): void
    {
        $this->assertSame(123, $this->scalarEncoder->encode(123, new EncodingContext()));
    }
}
