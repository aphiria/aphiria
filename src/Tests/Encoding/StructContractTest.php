<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests;

use Opulence\Serialization\Encoding\IEncodingInterceptor;
use Opulence\Serialization\Encoding\StructEncoder;

/**
 * Tests the struct encoder
 */
class StructEncoderTest extends \PHPUnit\Framework\TestCase
{
    /** @var StructEncoder The encoder to use in tests */
    private $encoder;

    public function setUp(): void
    {
        $this->encoder = new StructEncoder(
            'int',
            function ($value) {
                return (int)$value;
            },
            function (int $value) {
                return $value;
            }
        );
    }

    public function testDecodedValueIsSentThroughInterceptors(): void
    {
        /** @var IEncodingInterceptor $interceptor */
        $interceptor = $this->createMock(IEncodingInterceptor::class);
        $interceptor->expects($this->at(0))
            ->method('onPreDecoding')
            ->with(123, 'int')
            ->willReturn(456);
        $this->assertSame(456, $this->encoder->decode(123, [$interceptor]));
    }

    public function testDecodingValueUsesValueFactory(): void
    {
        $this->assertSame(123, $this->encoder->decode('123'));
    }

    public function testEncodedValueIsSentThroughInterceptors(): void
    {
        /** @var IEncodingInterceptor $interceptor */
        $interceptor = $this->createMock(IEncodingInterceptor::class);
        $interceptor->expects($this->at(0))
            ->method('onPostEncoding')
            ->with(123, 'int')
            ->willReturn(456);
        $this->assertSame(456, $this->encoder->encode(123, [$interceptor]));
    }

    public function testEncodingValueUsesEncodingFactory(): void
    {
        $this->assertSame(123, $this->encoder->encode(123));
    }

    public function testGettingTypeReturnsOneSetInConstructor(): void
    {
        $this->assertEquals('int', $this->encoder->getType());
    }
}
