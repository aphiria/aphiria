<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands\Attributes;

use Aphiria\Console\Commands\Attributes\Argument as ArgumentAttribute;
use Aphiria\Console\Commands\Attributes\Command as CommandAttribute;
use Aphiria\Console\Commands\Attributes\Option as OptionAttribute;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Commands\ICommandRegistrant;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\Option;
use Aphiria\Reflection\ITypeFinder;
use Aphiria\Reflection\TypeFinder;
use ReflectionClass;
use ReflectionException;

/**
 * Defines the command registrant that registers commands via attributes
 */
final class AttributeCommandRegistrant implements ICommandRegistrant
{
    /** @var list<string> The paths to check for commands */
    private array $paths;
    /** @var ITypeFinder The type finder */
    private ITypeFinder $typeFinder;

    /**
     * @param string|list<string> $paths The path or paths to check for commands
     * @param ITypeFinder|null $typeFinder The type finder
     */
    public function __construct(string|array $paths, ITypeFinder $typeFinder = null)
    {
        $this->paths = \is_array($paths) ? $paths : [$paths];
        $this->typeFinder = $typeFinder ?? new TypeFinder();
    }

    /**
     * @inheritdoc
     * @throws ReflectionException Thrown if a command class could not be reflected
     */
    public function registerCommands(CommandRegistry $commands): void
    {
        foreach ($this->typeFinder->findAllSubtypesOfType(ICommandHandler::class, $this->paths, true) as $commandHandler) {
            $reflectedCommandHandler = new ReflectionClass($commandHandler);
            $commandAttributes = $reflectedCommandHandler->getAttributes(CommandAttribute::class);
            $argumentAttributes = $reflectedCommandHandler->getAttributes(ArgumentAttribute::class);
            $optionAttributes = $reflectedCommandHandler->getAttributes(OptionAttribute::class);

            if (empty($commandAttributes)) {
                continue;
            }

            $arguments = $options = [];

            foreach ($argumentAttributes as $argumentAttribute) {
                $argumentAttributeInstance = $argumentAttribute->newInstance();
                $arguments[] = new Argument(
                    $argumentAttributeInstance->name,
                    $argumentAttributeInstance->type,
                    $argumentAttributeInstance->description,
                    $argumentAttributeInstance->defaultValue
                );
            }

            foreach ($optionAttributes as $optionAttribute) {
                $optionAttributeInstance = $optionAttribute->newInstance();
                $options[] = new Option(
                    $optionAttributeInstance->name,
                    $optionAttributeInstance->type,
                    $optionAttributeInstance->shortName,
                    $optionAttributeInstance->description,
                    $optionAttributeInstance->defaultValue
                );
            }

            $commandAttributeInstance = $commandAttributes[0]->newInstance();
            $command = new Command(
                $commandAttributeInstance->name,
                $arguments,
                $options,
                $commandAttributeInstance->description,
                $commandAttributeInstance->helpText
            );
            $commands->registerCommand($command, $commandHandler);
        }
    }
}
