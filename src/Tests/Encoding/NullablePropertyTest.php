<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests;

use Opulence\Serialization\Encoding\NullableProperty;
use Opulence\Net\Tests\Http\Formatting\Mocks\User;

/**
 * Tests a nullable property
 */
class NullablePropertyTest extends \PHPUnit\Framework\TestCase
{
    /** @var NullableProperty The property to use in tests */
    private $property;

    public function setUp(): void
    {
        $this->property = new NullableProperty('id', 'int', function (User $user) {
            return $user->getId();
        });
    }

    public function testIsNullableIsAlwaysTrue(): void
    {
        $this->assertTrue($this->property->isNullable());
    }
}
