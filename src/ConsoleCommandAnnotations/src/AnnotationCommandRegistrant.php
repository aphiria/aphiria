<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ConsoleCommandAnnotations;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Commands\ICommandRegistrant;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\Option;
use Aphiria\ConsoleCommandAnnotations\Annotations\Command as CommandAnnotation;
use Aphiria\Reflection\ITypeFinder;
use Aphiria\Reflection\TypeFinder;
use Doctrine\Annotations\AnnotationException;
use Doctrine\Annotations\AnnotationReader;
use Doctrine\Annotations\Reader;
use ReflectionClass;
use ReflectionException;

/**
 * Defines the command registrant that registers commands via annotations
 */
final class AnnotationCommandRegistrant implements ICommandRegistrant
{
    /** @var string[] The paths to check for commands */
    private array $paths;
    /** @var ICommandHandlerResolver The resolver for command handlers */
    private ICommandHandlerResolver $commandHandlerResolver;
    /** @var ITypeFinder The type finder */
    private ITypeFinder $typeFinder;
    /** @var Reader The annotation reader */
    private Reader $annotationReader;

    /**
     * @param string|string[] $paths The path or paths to check for commands
     * @param ICommandHandlerResolver $commandHandlerResolver The resolver for command handlers
     * @param Reader|null $annotationReader The annotation reader
     * @param ITypeFinder|null $typeFinder The type finder
     * @throws AnnotationException Thrown if there was an error creating the annotation reader
     */
    public function __construct(
        $paths,
        ICommandHandlerResolver $commandHandlerResolver,
        Reader $annotationReader = null,
        ITypeFinder $typeFinder = null
    ) {
        $this->paths = \is_array($paths) ? $paths : [$paths];
        $this->commandHandlerResolver = $commandHandlerResolver;
        $this->annotationReader = $annotationReader ?? new AnnotationReader();
        $this->typeFinder = $typeFinder ?? new TypeFinder();
    }

    /**
     * @inheritdoc
     * @throws ReflectionException Thrown if a command class could not be reflected
     */
    public function registerCommands(CommandRegistry $commands): void
    {
        foreach ($this->typeFinder->findAllSubtypesOfType(ICommandHandler::class, $this->paths, true) as $commandHandler) {
            foreach ($this->annotationReader->getClassAnnotations(new ReflectionClass($commandHandler)) as $commandAnnotation) {
                if (!$commandAnnotation instanceof CommandAnnotation) {
                    continue;
                }

                $arguments = [];
                $options = [];

                foreach ($commandAnnotation->arguments as $argumentAnnotation) {
                    $arguments[] = new Argument(
                        $argumentAnnotation->name,
                        $argumentAnnotation->type,
                        $argumentAnnotation->description,
                        $argumentAnnotation->defaultValue
                    );
                }

                foreach ($commandAnnotation->options as $optionAnnotation) {
                    $options[] = new Option(
                        $optionAnnotation->name,
                        $optionAnnotation->shortName,
                        $optionAnnotation->type,
                        $optionAnnotation->description,
                        $optionAnnotation->defaultValue
                    );
                }

                $command = new Command(
                    $commandAnnotation->name,
                    $arguments,
                    $options,
                    $commandAnnotation->description,
                    $commandAnnotation->helpText
                );
                $commands->registerCommand(
                    $command,
                    fn () => $this->commandHandlerResolver->resolve($commandHandler)
                );
            }
        }
    }
}
