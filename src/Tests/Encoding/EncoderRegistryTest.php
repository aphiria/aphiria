<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests;

use DateTime;
use Opulence\Serialization\Encoding\EncoderRegistry;
use Opulence\Serialization\Encoding\ObjectEncoder;
use Opulence\Serialization\Encoding\Property;
use Opulence\Serialization\Encoding\StructEncoder;
use Opulence\Serialization\Tests\Mocks\User;
use OutOfBoundsException;

/**
 * Tests the encoder registry
 */
class EncoderRegistryTest extends \PHPUnit\Framework\TestCase
{
    /** @var EncoderRegistry The registry to use in tests */
    private $encoders;

    public function setUp(): void
    {
        $this->encoders = new EncoderRegistry();
    }

    public function testGettingEncoderByObjectValueGetsEncoderRegisteredForItsType(): void
    {
        $expectedEncoder = new StructEncoder(
            DateTime::class,
            function ($value) {
                return DateTime::createFromFormat(DateTime::ISO8601, $value);
            },
            function (DateTime $value) {
                return $value->format(DateTime::ISO8601);
            }
        );
        $this->encoders->registerEncoder($expectedEncoder);
        $this->assertSame($expectedEncoder, $this->encoders->getEncoderForValue(new DateTime));
    }

    public function testGettingEncoderByScalarValueGetsEncoderRegisteredForItsType(): void
    {
        $expectedEncoder = new StructEncoder(
            'int',
            function ($value) {
                return (int)$value;
            },
            function (int $value) {
                return $value;
            }
        );
        $this->encoders->registerEncoder($expectedEncoder);
        $this->assertSame($expectedEncoder, $this->encoders->getEncoderForValue(123));
    }

    public function testGettingEncoderByTypeGetsEncoderWithThatType(): void
    {
        $expectedEncoder = new StructEncoder(
            'int',
            function ($value) {
                return (int)$value;
            },
            function (int $value) {
                return $value;
            }
        );
        $this->encoders->registerEncoder($expectedEncoder);
        $this->assertSame($expectedEncoder, $this->encoders->getEncoderForType('int'));
        $this->assertSame($expectedEncoder, $this->encoders->getEncoderForType('integer'));
    }

    public function testGettingEncoderForTypeWithoutEncoderThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->encoders->getEncoderForType('foo');
    }

    public function testGettingEncoderForValueWithoutEncoderThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->encoders->getEncoderForValue($this);
    }

    public function testRegisteringObjectEncoderCreatesAnInstanceOfOne(): void
    {
        $expectedEncoder = new ObjectEncoder(
            User::class,
            $this->encoders,
            function (array $hash) {
                return new User($hash['id'], $hash['email']);
            },
            new Property('id', 'int', function (User $user) {
                return $user->getId();
            }),
            new Property('email', 'string', function (User $user) {
                return $user->getEmail();
            })
        );
        $this->encoders->registerObjectEncoder(
            User::class,
            function (array $hash) {
                return new User($hash['id'], $hash['email']);
            },
            new Property('id', 'int', function (User $user) {
                return $user->getId();
            }),
            new Property('email', 'string', function (User $user) {
                return $user->getEmail();
            })
        );
        $this->assertEquals($expectedEncoder, $this->encoders->getEncoderForType(User::class));
    }

    public function testRegisteringStructEncoderCreatesAnInstanceOfOne(): void
    {
        $expectedEncoder = new StructEncoder(
            'int',
            function ($value) {
                return (int)$value;
            },
            function (int $value) {
                return $value;
            }
        );
        $this->encoders->registerStructEncoder(
            'int',
            function ($value) {
                return (int)$value;
            },
            function (int $value) {
                return $value;
            }
        );
        $this->assertEquals($expectedEncoder, $this->encoders->getEncoderForType('int'));
    }
}
