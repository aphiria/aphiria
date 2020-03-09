<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Components;

use Aphiria\Application\IComponent;
use Aphiria\Console\Commands\Annotations\AnnotationCommandRegistrant;
use Aphiria\Console\Commands\ClosureCommandRegistrant;
use Aphiria\Console\Commands\CommandRegistrantCollection;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\DependencyInjection\IServiceResolver;
use Closure;
use RuntimeException;

/**
 * Defines the command component
 */
class CommandComponent implements IComponent
{
    /** @var IServiceResolver The dependency resolver */
    private IServiceResolver $dependencyResolver;
    /** @var Closure[] The list of callbacks that can register commands */
    private array $callbacks = [];
    /** @var bool Whether or not annotations are enabled */
    private bool $annotationsEnabled = false;

    /**
     * @param IServiceResolver $dependencyResolver The dependency resolver
     */
    public function __construct(IServiceResolver $dependencyResolver)
    {
        $this->dependencyResolver = $dependencyResolver;
    }

    /**
     * @inheritdoc
     */
    public function build(): void
    {
        $commands = $this->dependencyResolver->resolve(CommandRegistry::class);
        $commandRegistrants = $this->dependencyResolver->resolve(CommandRegistrantCollection::class);

        if ($this->annotationsEnabled) {
            $annotationCommandRegistrant = null;

            if (!$this->dependencyResolver->tryResolve(AnnotationCommandRegistrant::class, $annotationCommandRegistrant)) {
                throw new RuntimeException(AnnotationCommandRegistrant::class . ' cannot be null if using annotations');
            }

            $commandRegistrants->add($annotationCommandRegistrant);
        }

        $commandRegistrants->add(new ClosureCommandRegistrant($this->callbacks));
        $commandRegistrants->registerCommands($commands);
    }

    /**
     * Enables route annotations
     *
     * @return self For chaining
     */
    public function withAnnotations(): self
    {
        $this->annotationsEnabled = true;

        return $this;
    }

    /**
     * Adds commands to the registry
     *
     * @param Closure $callback The callback that takes in an instance of CommandRegistry
     * @return self For chaining
     */
    public function withCommands(Closure $callback): self
    {
        $this->callbacks[] = $callback;

        return $this;
    }
}
