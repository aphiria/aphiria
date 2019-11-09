<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands;

use Closure;
use InvalidArgumentException;
use Opis\Closure\SerializableClosure;

/**
 * Defines the binding between a command and its handler
 */
final class CommandBinding
{
    /** @var Command The command */
    public Command $command;
    /** @var Closure The factory that will create the command handler */
    public ?Closure $commandHandlerFactory;
    /** @var string The serialized command handler */
    protected string $serializedCommandHandlerFactory = '';

    /**
     * @param Command $command The command handler
     * @param Closure $commandHandlerFactory The factory that will create the command handler
     *      It must be parameterless
     */
    public function __construct(Command $command, Closure $commandHandlerFactory)
    {
        $this->command = $command;
        $this->commandHandlerFactory = $commandHandlerFactory;
    }

    /**
     * Serializes the binding
     *
     * @return array The list of properties to store
     */
    public function __sleep(): array
    {
        $this->serializedCommandHandlerFactory = \serialize(new SerializableClosure($this->commandHandlerFactory));
        $this->commandHandlerFactory = null;

        return \array_keys(\get_object_vars($this));
    }

    /**
     * Deserializes the binding
     */
    public function __wakeup()
    {
        /** @var SerializableClosure $serializedClosure */
        $serializedClosure = \unserialize(
            $this->serializedCommandHandlerFactory,
            ['allowed_classes' => [SerializableClosure::class]]
        );
        $this->commandHandlerFactory = $serializedClosure->getClosure();
        $this->serializedCommandHandlerFactory = '';
    }

    /**
     * Resolves the command handler
     *
     * @return ICommandHandler The resolved command handler
     * @throws InvalidArgumentException Thrown if the command handler wasn't valid
     */
    public function resolveCommandHandler(): ICommandHandler
    {
        $commandHandler = ($this->commandHandlerFactory)();

        if ($commandHandler instanceof ICommandHandler) {
            return $commandHandler;
        }

        if ($commandHandler instanceof Closure) {
            return new ClosureCommandHandler($commandHandler);
        }

        throw new InvalidArgumentException(
            'Command handler must implement ' . ICommandHandler::class . ' or be a closure'
        );
    }
}
