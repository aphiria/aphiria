<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\CommandBindingRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command binding registry
 */
class CommandBindingRegistryTest extends TestCase
{
    /** @var CommandBindingRegistry */
    private $registry;

    public function setUp(): void
    {
        $this->registry = new CommandBindingRegistry();
    }

    public function testGettingAllBindingsReturnsAllBindings(): void
    {
        $command = new Command('name', [], [], '', '');
        /** @var ICommandHandler|MockObject $commandHandler */
        $commandHandler = $this->createMock(ICommandHandler::class);
        $expectedBinding = new CommandBinding($command, $commandHandler);
        $this->registry->registerCommandBinding($expectedBinding);
        $actualBindings = $this->registry->getAllCommandBindings();
        $this->assertCount(1, $actualBindings);
        $this->assertSame($expectedBinding, $actualBindings[0]);
    }

    public function testGettingNonExistentBindingThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->registry->getCommandBinding('foo');
    }

    public function testGettingRegisteredBindingNormalizesName(): void
    {
        $command = new Command('name', [], [], '', '');
        /** @var ICommandHandler|MockObject $commandHandler */
        $commandHandler = $this->createMock(ICommandHandler::class);
        $expectedBinding = new CommandBinding($command, $commandHandler);
        $this->registry->registerCommandBinding($expectedBinding);
        $this->assertSame($expectedBinding, $this->registry->getCommandBinding('NAME'));
    }

    public function testGettingRegisteredBindingReturnsSameInstanceThatWasRegistered(): void
    {
        $command = new Command('name', [], [], '', '');
        /** @var ICommandHandler|MockObject $commandHandler */
        $commandHandler = $this->createMock(ICommandHandler::class);
        $expectedBinding = new CommandBinding($command, $commandHandler);
        $this->registry->registerCommandBinding($expectedBinding);
        $this->assertSame($expectedBinding, $this->registry->getCommandBinding('name'));
    }

    public function testRegisteringManyBindingsReturnsAddsAllToRegistry(): void
    {
        $expectedBindings = [
            new CommandBinding(
                new Command('foo', [], [], ''),
                $this->createMock(ICommandHandler::class)
            ),
            new CommandBinding(
                new Command('bar', [], [], ''),
                $this->createMock(ICommandHandler::class)
            )
        ];
        $this->registry->registerManyCommandBindings($expectedBindings);
        $this->assertSame($expectedBindings, $this->registry->getAllCommandBindings());
    }
}
