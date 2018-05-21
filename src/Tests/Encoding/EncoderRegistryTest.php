<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Encoding;

use Opulence\Serialization\Encoding\EncoderRegistry;
use Opulence\Serialization\Encoding\IEncoder;
use Opulence\Serialization\Tests\Encoding\Mocks\User;

/**
 * Tests the encoder registry
 */
class EncoderRegistryTest extends \PHPUnit\Framework\TestCase
{
    /** @var EncoderRegistry The encoder registry to test */
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
    }

    public function testGettingEncoderByTypeForArrayOfTypeGetsEncoderForArray(): void
    {
        $arrayEncoder = $this->createMock(IEncoder::class);
        $this->encoderRegistry->registerEncoder('array', $arrayEncoder);
        $this->assertSame($arrayEncoder, $this->encoderRegistry->getEncoderForType('foo[]'));
    }

    public function testGettingEncoderByTypeForObjectUsesDefaultEncoder(): void
    {
        $this->assertSame($this->defaultObjectEncoder, $this->encoderRegistry->getEncoderForType(User::class));
    }

    public function testGettingEncoderByTypeForScalarUsesDefaultEncoder(): void
    {
        $this->assertSame($this->defaultScalarEncoder, $this->encoderRegistry->getEncoderForType('int'));
    }

    public function testGettingEncoderByTypeUsesCustomEncoderIfOneIsRegistered(): void
    {
        $expectedEncoder = $this->createMock(IEncoder::class);
        $this->encoderRegistry->registerEncoder('foo', $expectedEncoder);
        $this->assertSame($expectedEncoder, $this->encoderRegistry->getEncoderForType('foo'));
    }

    public function testGettingEncoderByTypeNormalizesType(): void
    {
        $data = [
            ['bool', 'boolean', $this->createMock(IEncoder::class)],
            ['float', 'double', $this->createMock(IEncoder::class)],
            ['int', 'integer', $this->createMock(IEncoder::class)]
        ];

        foreach ($data as $datum) {
            $this->encoderRegistry->registerEncoder($datum[0], $datum[2]);
            $this->assertSame($datum[2], $this->encoderRegistry->getEncoderForType($datum[1]));
        }
    }

    public function testGettingEncoderByValueForObjectUsesDefaultEncoder(): void
    {
        $this->assertSame(
            $this->defaultObjectEncoder,
            $this->encoderRegistry->getEncoderForValue(new User(123, 'foo@bar.com'))
        );
    }

    public function testGettingEncoderByValueForScalarUsesDefaultEncoder(): void
    {
        $this->assertSame(
            $this->defaultScalarEncoder,
            $this->encoderRegistry->getEncoderForValue(123)
        );
    }

    public function testGettingEncoderByValueUsesCustomEncoderIfOneIsRegistered(): void
    {
        $expectedEncoder = $this->createMock(IEncoder::class);
        $this->encoderRegistry->registerEncoder(User::class, $expectedEncoder);
        $this->assertSame($expectedEncoder, $this->encoderRegistry->getEncoderForValue(new User(123, 'foo@bar.com')));
    }
}
