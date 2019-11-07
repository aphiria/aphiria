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
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\Option;
use Aphiria\ConsoleCommandAnnotations\Annotations\Command as CommandAnnotation;
use Doctrine\Annotations\AnnotationException;
use Doctrine\Annotations\AnnotationReader;
use Doctrine\Annotations\Reader;
use ReflectionClass;
use ReflectionException;

/**
 * Defines the command annotation registrant that uses reflection to scan for annotations
 */
final class ReflectionCommandAnnotationRegistrant implements ICommandAnnotationRegistrant
{
    /** @var string[] The paths to check for commands */
    private array $paths;
    /** @var ICommandHandlerResolver The resolver for command handlers */
    private ICommandHandlerResolver $commandHandlerResolver;
    /** @var ICommandFinder The command finder */
    private ICommandFinder $commandFinder;
    /** @var Reader The annotation reader */
    private Reader $annotationReader;

    /**
     * @param string|string[] $paths The path or paths to check for commands
     * @param ICommandHandlerResolver $commandHandlerResolver The resolver for command handlers
     * @param ICommandFinder|null $commandFinder The commands finder
     * @param Reader|null $annotationReader The annotation reader
     * @throws AnnotationException Thrown if there was an error creating the annotation reader
     */
    public function __construct(
        $paths,
        ICommandHandlerResolver $commandHandlerResolver,
        ICommandFinder $commandFinder = null,
        Reader $annotationReader = null
    ) {
        $this->paths = \is_array($paths) ? $paths : [$paths];
        $this->commandHandlerResolver = $commandHandlerResolver;
        $this->annotationReader = $annotationReader ?? new AnnotationReader();
        $this->commandFinder = $commandFinder ?? new FileCommandFinder($this->annotationReader);
    }

    /**
     * @inheritdoc
     * @throws ReflectionException Thrown if a command class could not be reflected
     */
    public function registerCommands(CommandRegistry $commands): void
    {
        foreach ($this->commandFinder->findAll($this->paths) as $commandHandlerClassName) {
            $commandHandlerReflectionClass = new ReflectionClass($commandHandlerClassName);

            foreach ($this->annotationReader->getClassAnnotations($commandHandlerReflectionClass) as $classAnnotation) {
                if (!$classAnnotation instanceof CommandAnnotation) {
                    continue;
                }

                $arguments = [];
                $options = [];

                foreach ($classAnnotation->arguments as $argumentAnnotation) {
                    $arguments[] = new Argument(
                        $argumentAnnotation->name,
                        $argumentAnnotation->type,
                        $argumentAnnotation->description,
                        $argumentAnnotation->defaultValue
                    );
                }

                foreach ($classAnnotation->options as $optionAnnotation) {
                    $options[] = new Option(
                        $optionAnnotation->name,
                        $optionAnnotation->shortName,
                        $optionAnnotation->type,
                        $optionAnnotation->description,
                        $optionAnnotation->defaultValue
                    );
                }

                $command = new Command(
                    $classAnnotation->name,
                    $arguments,
                    $options,
                    $classAnnotation->description,
                    $classAnnotation->helpText
                );
                $commands->registerCommand(
                    $command,
                    fn () => $this->commandHandlerResolver->resolve($commandHandlerClassName)
                );
            }
        }
    }
}
