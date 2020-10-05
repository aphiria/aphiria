<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Builders;

use Aphiria\Application\Builders\ApplicationBuilder;
use Aphiria\Console\Application;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use RuntimeException;

/**
 * Defines the application builder for console applications
 */
final class ConsoleApplicationBuilder extends ApplicationBuilder
{
    /**
     * @param IContainer $container The DI container
     */
    public function __construct(private IContainer $container)
    {
    }

    /**
     * @inheritdoc
     */
    public function build(): ICommandBus
    {
        $this->buildModules();
        $this->buildComponents();

        try {
            $consoleApp = new Application($this->container->resolve(CommandRegistry::class), $this->container);
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the console application', 0, $ex);
        }

        $this->container->bindInstance(ICommandBus::class, $consoleApp);

        return $consoleApp;
    }
}
