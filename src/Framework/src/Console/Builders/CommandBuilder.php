<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Builders;

use Aphiria\ApplicationBuilders\IApplicationBuilder;
use Aphiria\ApplicationBuilders\IComponentBuilder;
use Aphiria\Console\Commands\Annotations\AnnotationCommandRegistrant;
use Aphiria\Console\Commands\ClosureCommandRegistrant;
use Aphiria\Console\Commands\CommandRegistrantCollection;
use Aphiria\Console\Commands\CommandRegistry;
use Closure;
use RuntimeException;

/**
 * Defines the console command component builder
 */
class CommandBuilder implements IComponentBuilder
{
    /** @var CommandRegistry The registry of commands */
    private CommandRegistry $commands;
    /** @var CommandRegistrantCollection The list of command registrants to add to */
    private CommandRegistrantCollection $commandRegistrants;
    /** @var AnnotationCommandRegistrant|null The optional annotation command registrant */
    private ?AnnotationCommandRegistrant $annotationCommandRegistrant;
    /** @var Closure[] The list of callbacks that can register commands */
    private array $callbacks = [];

    /**
     * @param CommandRegistry $commands The registry of commands
     * @param CommandRegistrantCollection $commandRegistrants The list of command registrants to add to
     * @param AnnotationCommandRegistrant|null $annotationCommandRegistrant The optional annotation command registrant
     */
    public function __construct(
        CommandRegistry $commands,
        CommandRegistrantCollection $commandRegistrants,
        AnnotationCommandRegistrant $annotationCommandRegistrant = null
    ) {
        $this->commands = $commands;
        $this->commandRegistrants = $commandRegistrants;
        $this->annotationCommandRegistrant = $annotationCommandRegistrant;
    }

    /**
     * @inheritdoc
     */
    public function build(IApplicationBuilder $appBuilder): void
    {
        $this->commandRegistrants->add(new ClosureCommandRegistrant($this->callbacks));
        $this->commandRegistrants->registerCommands($this->commands);
    }

    /**
     * Enables route annotations
     *
     * @return CommandBuilder For chaining
     * @throws RuntimeException Thrown if the annotation command registrant was not set
     */
    public function withAnnotations(): self
    {
        if ($this->annotationCommandRegistrant === null) {
            throw new RuntimeException(AnnotationCommandRegistrant::class . ' cannot be null if using annotations');
        }

        $this->commandRegistrants->add($this->annotationCommandRegistrant);

        return $this;
    }

    /**
     * Adds commands to the registry
     *
     * @param Closure $callback The callback that takes in an instance of CommandRegistry
     * @return CommandBuilder For chaining
     */
    public function withCommands(Closure $callback): self
    {
        $this->callbacks[] = $callback;

        return $this;
    }
}
