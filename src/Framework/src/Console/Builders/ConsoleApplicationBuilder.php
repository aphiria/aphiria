<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Builders;

use Aphiria\Application\Builders\ApplicationBuilder;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\ConsoleGateway;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Framework\Console\ConsoleApplication;
use RuntimeException;

/**
 * Defines the application builder for console applications
 */
final class ConsoleApplicationBuilder extends ApplicationBuilder
{
    /**
     * @param IContainer $container The DI container
     */
    public function __construct(private readonly IContainer $container)
    {
    }

    /**
     * @inheritdoc
     */
    public function build(): ConsoleApplication
    {
        $this->configureModules();
        $this->buildComponents();

        try {
            $consoleGateway = new ConsoleGateway($this->container->resolve(CommandRegistry::class), $this->container);
            $this->container->bindInstance(ICommandHandler::class, $consoleGateway);

            return new ConsoleApplication(
                $consoleGateway,
                $this->container->resolve(Input::class),
                $this->container->resolve(IOutput::class)
            );
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the console application', 0, $ex);
        }
    }
}
