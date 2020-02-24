<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\ApplicationBuilders;

use Aphiria\Console\App as ConsoleApp;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\DependencyInjection\ResolutionException;
use RuntimeException;

/**
 * Defines the application builder for console applications
 */
final class ConsoleApplicationBuilder extends AphiriaApplicationBuilder
{
    /**
     * @inheritdoc
     */
    public function build(): ICommandBus
    {
        $this->buildComponents();

        try {
            $consoleApp = new ConsoleApp($this->container->resolve(CommandRegistry::class));
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the console application', 0, $ex);
        }

        $this->container->bindInstance(ICommandBus::class, $consoleApp);

        return $consoleApp;
    }
}
