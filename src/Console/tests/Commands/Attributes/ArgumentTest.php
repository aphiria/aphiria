<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Attributes;

use Aphiria\Console\Commands\Attributes\Argument;
use Aphiria\Console\Input\ArgumentType;
use PHPUnit\Framework\TestCase;

class ArgumentTest extends TestCase
{
    public function getTypes(): array
    {
        return [
            [[ArgumentType::Required], ArgumentType::Required],
            [[ArgumentType::Required], [ArgumentType::Required]]
        ];
    }

    public function testAllPropertiesAreSetInConstructor(): void
    {
        $argument = new Argument('arg', ArgumentType::Required, 'description', 'foo');
        $this->assertSame('arg', $argument->name);
        $this->assertSame([ArgumentType::Required], $argument->type);
        $this->assertSame('description', $argument->description);
        $this->assertSame('foo', $argument->defaultValue);
    }

    /**
     * @dataProvider getTypes
     * @param list<ArgumentType> $expectedType The expected type
     * @param list<ArgumentType>|ArgumentType $paramType The type passed into the argument constructor
     */
    public function testTypeIsAlwaysArray(array $expectedType, array|ArgumentType $paramType): void
    {
        $argument = new Argument('arg', $paramType);
        $this->assertSame($expectedType, $argument->type);
    }
}
