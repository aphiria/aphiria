<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Encoding;

use Opulence\Serialization\Encoding\DefaultEncoder;
use Opulence\Serialization\Encoding\EncoderRegistry;
use Opulence\Serialization\Encoding\IEncoder;
use Opulence\Serialization\Tests\Encoding\Mocks\User;

/**
 * Tests the default encoder
 */
class DefaultEncoderTest extends \PHPUnit\Framework\TestCase
{
    /** @var DefaultEncoder The default encoder */
    private $defaultEncoder;
    /** @var EncoderRegistry The encoder registry to use */
    private $encoderRegistry;
    /** @var IEncoder The default object encoder */
    private $defaultObjectEncoder;
    /** @var IEncoder The default scalar encoder */
    private $defaultScalarEncoder;

    public function setUp(): void
    {
        $this->defaultObjectEncoder = $this->createMock(IEncoder::class);
        $this->defaultScalarEncoder = $this->createMock(IEncoder::class);
        $this->encoderRegistry = new EncoderRegistry($this->defaultObjectEncoder, $this->defaultScalarEncoder);
        $this->defaultEncoder = new DefaultEncoder($this->encoderRegistry);
    }

    public function testDecodingNullValueReturnsNull(): void
    {
        $this->assertNull($this->defaultEncoder->decode(null, 'foo'));
    }

    public function testDecodingUsesEncoderFromRegistry(): void
    {
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->once())
            ->method('decode')
            ->with('foo', 'bar')
            ->willReturn('baz');
        $this->encoderRegistry->registerEncoder('bar', $encoder);
        $this->assertEquals('baz', $this->defaultEncoder->decode('foo', 'bar'));
    }

    public function testEncodingNullValueReturnsNull(): void
    {
        $this->assertNull($this->defaultEncoder->encode(null));
    }

    public function testEncodingObjectWithNoCustomEncoderUsesDefaultOne(): void
    {
        $user = new User(123, 'foo@bar.com');
        $expectedEncodedValue = ['id' => 123, 'email' => 'foo@bar.com'];
        $this->defaultObjectEncoder->expects($this->once())
            ->method('encode')
            ->with($user)
            ->willReturn($expectedEncodedValue);
        $this->assertEquals($expectedEncodedValue, $this->defaultEncoder->encode($user));
    }

    public function testEncodingScalarWithNoCustomEncoderUsesDefaultOne(): void
    {
        $this->defaultScalarEncoder->expects($this->once())
            ->method('encode')
            ->with(123)
            ->willReturn(123);
        $this->assertEquals(123, $this->defaultEncoder->encode(123));
    }

    public function testEncodingUsesEncoderFromRegistry(): void
    {
        $user = new User(123, 'foo@bar.com');
        $expectedEncodedValue = ['id' => 123, 'email' => 'foo@bar.com'];
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->once())
            ->method('encode')
            ->with($user)
            ->willReturn($expectedEncodedValue);
        $this->encoderRegistry->registerEncoder(User::class, $encoder);
        $this->assertEquals($expectedEncodedValue, $this->defaultEncoder->encode($user));
    }
}
