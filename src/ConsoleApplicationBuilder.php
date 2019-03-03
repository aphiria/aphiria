<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/configuration/blob/master/LICENSE.md
 */

namespace Aphiria\Configuration;

use Aphiria\Console\Commands\CommandRegistry;
use Closure;
use Opulence\Ioc\Bootstrappers\IBootstrapperRegistry;

/**
 * Defines the console application builder
 */
final class ConsoleApplicationBuilder extends ApplicationBuilder implements IConsoleApplicationBuilder
{
    /** @var CommandRegistry The command registry to use in delegates */
    private $commands;
    /** @var Closure[] The list of command delegates */
    private $delegates = [];

    /**
     * @inheritdoc
     * @param CommandRegistry $commands The command registry to use in delegates
     */
    public function __construct(CommandRegistry $commands, IBootstrapperRegistry $bootstrappers)
    {
        parent::__construct($bootstrappers);

        $this->commands = $commands;
    }

    /**
     * @inheritdoc
     */
    public function build(): void
    {
        $this->runBootstrapperDelegates();

        foreach ($this->delegates as $delegate) {
            $delegate($this->commands);
        }
    }

    /**
     * @inheritdoc
     */
    public function withCommands(Closure $delegate): IConsoleApplicationBuilder
    {
        $this->delegates[] = $delegate;

        return $this;
    }
}
