<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/serialization/blob/master/LICENSE.md
 */

namespace Aphiria\Serialization\Tests\Encoding;

use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Serialization\Encoding\IEncoder;
use Aphiria\Serialization\Tests\Encoding\Mocks\User;
use OutOfBoundsException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the encoder registry
 */
class EncoderRegistryTest extends TestCase
{
    /** @var EncoderRegistry The encoder registry to test */
    private $encoderRegistry;

    public function setUp(): void
    {
        $this->encoderRegistry = new EncoderRegistry();
    }

    public function testGettingEncoderForScalarTypeNormalizesTypeString(): void
    {
        /** @var IEncoder|MockObject $expectedEncoder */
        $expectedEncoder = $this->createMock(IEncoder::class);
        $this->encoderRegistry->registerEncoder('INT', $expectedEncoder);
        $this->assertSame($expectedEncoder, $this->encoderRegistry->getEncoderForType('INT'));
        $this->assertSame($expectedEncoder, $this->encoderRegistry->getEncoderForType('int'));
    }

    public function testGettingEncoderByTypeForArrayOfTypeGetsEncoderForArray(): void
    {
        /** @var IEncoder|MockObject $arrayEncoder */
        $arrayEncoder = $this->createMock(IEncoder::class);
        $this->encoderRegistry->registerEncoder('array', $arrayEncoder);
        $this->assertSame($arrayEncoder, $this->encoderRegistry->getEncoderForType('foo[]'));
    }

    public function testGettingEncoderByTypeForObjectUsesDefaultEncoder(): void
    {
        /** @var IEncoder|MockObject $expectedEncoder */
        $expectedEncoder = $this->createMock(IEncoder::class);
        $this->encoderRegistry->registerDefaultObjectEncoder($expectedEncoder);
        $this->assertSame($expectedEncoder, $this->encoderRegistry->getEncoderForType(User::class));
    }

    public function testGettingEncoderByTypeForObjectWithoutEncoderThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('No default object encoder is registered');
        $this->encoderRegistry->getEncoderForType(User::class);
    }

    public function testGettingEncoderByTypeForScalarUsesDefaultEncoder(): void
    {
        /** @var IEncoder|MockObject $expectedEncoder */
        $expectedEncoder = $this->createMock(IEncoder::class);
        $this->encoderRegistry->registerDefaultScalarEncoder($expectedEncoder);
        $this->assertSame($expectedEncoder, $this->encoderRegistry->getEncoderForType('int'));
    }

    public function testGettingEncoderByTypeForScalarWithoutEncoderThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('No default scalar encoder is registered');
        $this->encoderRegistry->getEncoderForType('int');
    }

    public function testGettingEncoderByTypeUsesCustomEncoderIfOneIsRegistered(): void
    {
        /** @var IEncoder|MockObject $expectedEncoder */
        $expectedEncoder = $this->createMock(IEncoder::class);
        $this->encoderRegistry->registerEncoder('foo', $expectedEncoder);
        $this->assertSame($expectedEncoder, $this->encoderRegistry->getEncoderForType('foo'));
    }

    public function gettingEncoderByTypeNormalizesTypeProvider(): array
    {
        return [
            ['bool', 'boolean', $this->createMock(IEncoder::class)],
            ['float', 'double', $this->createMock(IEncoder::class)],
            ['int', 'integer', $this->createMock(IEncoder::class)],
        ];
    }

    /**
     * @dataProvider gettingEncoderByTypeNormalizesTypeProvider
     */
    public function testGettingEncoderByTypeNormalizesType($aliasType, $type, $expectedEncoder): void
    {
        $this->encoderRegistry->registerEncoder($aliasType, $expectedEncoder);
        $this->assertSame($expectedEncoder, $this->encoderRegistry->getEncoderForType($type));
    }

    public function testGettingEncoderByValueForObjectUsesDefaultEncoder(): void
    {
        /** @var IEncoder|MockObject $expectedEncoder */
        $expectedEncoder = $this->createMock(IEncoder::class);
        $this->encoderRegistry->registerDefaultObjectEncoder($expectedEncoder);
        $this->assertSame($expectedEncoder, $this->encoderRegistry->getEncoderForValue(new User(123, 'foo@bar.com')));
    }

    public function testGettingEncoderByValueForScalarUsesDefaultEncoder(): void
    {
        /** @var IEncoder|MockObject $expectedEncoder */
        $expectedEncoder = $this->createMock(IEncoder::class);
        $this->encoderRegistry->registerDefaultScalarEncoder($expectedEncoder);
        $this->assertSame($expectedEncoder, $this->encoderRegistry->getEncoderForValue(123));
    }

    public function testGettingEncoderByValueUsesCustomEncoderIfOneIsRegistered(): void
    {
        /** @var IEncoder|MockObject $expectedEncoder */
        $expectedEncoder = $this->createMock(IEncoder::class);
        $this->encoderRegistry->registerEncoder(User::class, $expectedEncoder);
        $this->assertSame($expectedEncoder, $this->encoderRegistry->getEncoderForValue(new User(123, 'foo@bar.com')));
    }
}
