<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Serialization;

use Opulence\Net\Http\Formatting\Serialization\Encoding\Property;
use Opulence\Net\Tests\Http\Formatting\Mocks\User;

/**
 * Tests a property
 */
class PropertyTest extends \PHPUnit\Framework\TestCase
{
    /** @var Property The property to use in tests */
    private $property;

    public function setUp(): void
    {
        $this->property = new Property('id', 'int', function (User $user) {
            return $user->getId();
        });
    }

    public function testGettingNameReturnsOneSetInConstructor(): void
    {
        $this->assertEquals('id', $this->property->getName());
    }

    public function testGettingTypeReturnsOneSetInConstructor(): void
    {
        $this->assertEquals('int', $this->property->getType());
    }

    public function testGettingValueInvokesGetter(): void
    {
        $this->assertEquals(123, $this->property->getValue(new User(123, 'foo@bar.com')));
    }

    public function testIsArrayOfTypeUsesValueSetInConstructor(): void
    {
        $arrayProperty = new Property('id', 'int', function (User $user) {
            return $user->getId();
        }, true);
        $nonArrayProperty = new Property('id', 'int', function (User $user) {
            return $user->getId();
        });
        $this->assertTrue($arrayProperty->isArrayOfType());
        $this->assertFalse($nonArrayProperty->isArrayOfType());
    }

    public function testIsNullableUsesValueSetInConstructor(): void
    {
        $nullableProperty = new Property('id', 'int', function (User $user) {
            return $user->getId();
        }, false, true);
        $nonNullableProperty = new Property('id', 'int', function (User $user) {
            return $user->getId();
        }, false);
        $this->assertTrue($nullableProperty->isNullable());
        $this->assertFalse($nonNullableProperty->isNullable());
    }
}
