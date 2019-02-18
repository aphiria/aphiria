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
use Aphiria\Console\Commands\CommandHandlerBinding;
use Aphiria\Console\Commands\CommandHandlerBindingRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command handler binding registry
 */
class CommandHandlerBindingRegistryTest extends TestCase
{
    /** @var CommandHandlerBindingRegistry */
    private $registry;

    public function setUp(): void
    {
        $this->registry = new CommandHandlerBindingRegistry();
    }

    public function testGettingAllBindingsReturnsAllBindings(): void
    {
        $command = new Command('name', [], [], '', '');
        /** @var ICommandHandler|MockObject $commandHandler */
        $commandHandler = $this->createMock(ICommandHandler::class);
        $expectedBinding = new CommandHandlerBinding($command, $commandHandler);
        $this->registry->registerCommandHandlerBinding($expectedBinding);
        $actualBindings = $this->registry->getAllCommandHandlerBindings();
        $this->assertCount(1, $actualBindings);
        $this->assertSame($expectedBinding, $actualBindings[0]);
    }

    public function testGettingNonExistentBindingThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->registry->getCommandHandlerBinding('foo');
    }

    public function testGettingRegisteredBindingNormalizesName(): void
    {
        $command = new Command('name', [], [], '', '');
        /** @var ICommandHandler|MockObject $commandHandler */
        $commandHandler = $this->createMock(ICommandHandler::class);
        $expectedBinding = new CommandHandlerBinding($command, $commandHandler);
        $this->registry->registerCommandHandlerBinding($expectedBinding);
        $this->assertSame($expectedBinding, $this->registry->getCommandHandlerBinding('NAME'));
    }

    public function testGettingRegisteredBindingReturnsSameInstanceThatWasRegistered(): void
    {
        $command = new Command('name', [], [], '', '');
        /** @var ICommandHandler|MockObject $commandHandler */
        $commandHandler = $this->createMock(ICommandHandler::class);
        $expectedBinding = new CommandHandlerBinding($command, $commandHandler);
        $this->registry->registerCommandHandlerBinding($expectedBinding);
        $this->assertSame($expectedBinding, $this->registry->getCommandHandlerBinding('name'));
    }
}
