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
use Opulence\Net\Http\Formatting\ModelMapper;
use Opulence\Net\Http\Formatting\ModelMapperRegistry;
use Opulence\Net\Tests\Http\Formatting\Mocks\User;

/**
 * Tests the model mapper
 */
class ModelMapperTest extends \PHPUnit\Framework\TestCase
{
    /** @var ModelMapper The model mapper to use in tests */
    private $modelMapper;
    /** @var ModelMapperRegistry The registry of mappers to use in tests */
    private $registry;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->registry = new ModelMapperRegistry();
        $this->modelMapper = new ModelMapper($this->registry);
    }

    /**
     * Tests that converting from a hash for a model without a mapper throws an exception
     */
    public function testConvertFromHashForModelWithoutMapperThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->modelMapper->convertFromHash('foo', []);
    }

    /**
     * Tests that converting from a hash uses the registered mapper
     */
    public function testConvertingFromHashUsesRegisteredMapper() : void
    {
        $toHashMapper = function (User $user, ModelMapper $modelMapper) {
            return ['id' => $user->getId(), 'email' => $user->getEmail()];
        };
        $fromHasher = function (array $hash, ModelMapper $modelMapper) {
            return new User((int)$hash['id'], $hash['email']);
        };
        $this->registry->registerMappers(User::class, $toHashMapper, $fromHasher);
        $hash = ['id' => 123, 'email' => 'foo@bar.com'];
        $expectedUser = new User(123, 'foo@bar.com');
        $this->assertEquals($expectedUser, $this->modelMapper->convertFromHash(User::class, $hash));
    }

    /**
     * Tests that converting to a hash for a model without a mapper throws an exception
     */
    public function testConvertToHashForModelWithoutMapperThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->modelMapper->convertToHash(new User(123, 'foo@bar.com'));
    }

    /**
     * Tests that converting to a hash uses the registered mapper
     */
    public function testConvertingToHashUsesRegisteredMapper() : void
    {
        $toHashMapper = function (User $user, ModelMapper $modelMapper) {
            return ['id' => $user->getId(), 'email' => $user->getEmail()];
        };
        $fromHasher = function (array $hash, ModelMapper $modelMapper) {
            return new User((int)$hash['id'], $hash['email']);
        };
        $this->registry->registerMappers(User::class, $toHashMapper, $fromHasher);
        $user = new User(123, 'foo@bar.com');
        $this->assertEquals(['id' => 123, 'email' => 'foo@bar.com'], $this->modelMapper->convertToHash($user));
    }
}
