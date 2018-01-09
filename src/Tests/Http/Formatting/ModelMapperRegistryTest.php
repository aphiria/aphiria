<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use InvalidArgumentException;
use Opulence\Net\Http\Formatting\ModelMapperRegistry;

/**
 * Tests the model mapper registry
 */
class ModelMapperRegistryTest extends \PHPUnit\Framework\TestCase
{
    /** @var ModelMapperRegistry The model mapper registry to use in tests */
    private $registry;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->registry = new ModelMapperRegistry;
    }

    /**
     * Tests that getting the from-hash mapper for a type without a mapper throws an exception
     */
    public function testGettingFromHashMapperForTypeWithoutMapperThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->registry->getFromHashMapper('foo');
    }

    /**
     * Tests that getting the to-hash mapper for a type without a mapper throws an exception
     */
    public function testGettingToHashMapperForTypeWithoutMapperThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->registry->getToHashMapper('foo');
    }

    /**
     * Tests that getting mappers returns the mappers registered for the type
     */
    public function testGettingMappersReturnsMappersRegisteredForType() : void
    {
        $toHashMapper = function () {
        };
        $fromHashMapper = function () {
        };
        $this->registry->registerMappers('foo', $toHashMapper, $fromHashMapper);
        $this->assertSame($toHashMapper, $this->registry->getToHashMapper('foo'));
        $this->assertSame($fromHashMapper, $this->registry->getFromHashMapper('foo'));
    }
}
