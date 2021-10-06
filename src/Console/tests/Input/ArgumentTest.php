<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Input;

use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\ArgumentType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ArgumentTest extends TestCase
{
    private Argument $argument;

    protected function setUp(): void
    {
        $this->argument = new Argument('foo', ArgumentType::OPTIONAL, 'Foo argument', 'bar');
    }

    public function testCheckingIsArray(): void
    {
        $requiredArgument = new Argument('foo', ArgumentType::REQUIRED, 'Foo argument', 'bar');
        $optionalArgument = new Argument('foo', ArgumentType::OPTIONAL, 'Foo argument', 'bar');
        $arrayArgument = new Argument('foo', ArgumentType::IS_ARRAY, 'Foo argument');
        $this->assertTrue($arrayArgument->isArray());
        $this->assertFalse($requiredArgument->isArray());
        $this->assertFalse($optionalArgument->isArray());
        $arrayArgument = new Argument('foo', ArgumentType::IS_ARRAY | ArgumentType::OPTIONAL, 'Foo argument');
        $this->assertTrue($arrayArgument->isArray());
        $arrayArgument = new Argument('foo', ArgumentType::IS_ARRAY | ArgumentType::REQUIRED, 'Foo argument');
        $this->assertTrue($arrayArgument->isArray());
    }

    public function testCheckingIsOptional(): void
    {
        $requiredArgument = new Argument('foo', ArgumentType::REQUIRED, 'Foo argument', 'bar');
        $optionalArgument = new Argument('foo', ArgumentType::OPTIONAL, 'Foo argument', 'bar');
        $optionalArrayArgument = new Argument('foo', ArgumentType::OPTIONAL | ArgumentType::IS_ARRAY, 'Foo argument');
        $this->assertFalse($requiredArgument->isOptional());
        $this->assertTrue($optionalArgument->isOptional());
        $this->assertTrue($optionalArrayArgument->isOptional());
    }

    public function testCheckingIsRequired(): void
    {
        $requiredArgument = new Argument('foo', ArgumentType::REQUIRED, 'Foo argument', 'bar');
        $requiredArrayArgument = new Argument('foo', ArgumentType::REQUIRED | ArgumentType::IS_ARRAY, 'Foo argument');
        $optionalArgument = new Argument('foo', ArgumentType::OPTIONAL, 'Foo argument', 'bar');
        $this->assertTrue($requiredArgument->isRequired());
        $this->assertTrue($requiredArrayArgument->isRequired());
        $this->assertFalse($optionalArgument->isRequired());
    }

    public function testEmptyArgumentNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument name cannot be empty');
        new Argument('', ArgumentType::REQUIRED);
    }

    public function testGettingDefaultValue(): void
    {
        $this->assertSame('bar', $this->argument->defaultValue);
    }

    public function testGettingDescription(): void
    {
        $this->assertSame('Foo argument', $this->argument->description);
    }

    public function testGettingName(): void
    {
        $this->assertSame('foo', $this->argument->name);
    }

    public function testSettingTypeToOptionalAndRequired(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Argument('foo', ArgumentType::OPTIONAL | ArgumentType::REQUIRED, 'Foo argument');
    }
}
