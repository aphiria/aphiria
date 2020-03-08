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

use Aphiria\Application\Builders\ApplicationBuilder;
use Aphiria\Application\IBootstrapper;
use Aphiria\Console\App;
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
    /** @var IContainer The DI container */
    private IContainer $container;

    /**
     * @param IContainer $container The DI container
     * @param IBootstrapper[] $bootstrappers The list of bootstrappers to run to bootstrap the application
     */
    public function __construct(IContainer $container, array $bootstrappers)
    {
        parent::__construct($bootstrappers);

        $this->container = $container;
        // TODO: Should bootstrap happen here, or outside?  I don't want to confuse devs with bootstrap() and build() methods in the same class.
        $this->bootstrap();
    }

    /**
     * @inheritdoc
     */
    public function build(): ICommandBus
    {
        $this->buildModules();
        $this->initializeComponents();

        try {
            $consoleApp = new App($this->container->resolve(CommandRegistry::class));
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the console application', 0, $ex);
        }

        $this->container->bindInstance(ICommandBus::class, $consoleApp);

        return $consoleApp;
    }
}
