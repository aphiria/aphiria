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
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Framework\Console\ConsoleApplication;
use RuntimeException;

/**
 * Defines the application builder for console applications
 */
final class ConsoleApplicationBuilder extends ApplicationBuilder
{
    /**
     * @param IServiceResolver $container The DI container
     */
    public function __construct(private readonly IServiceResolver $container)
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
            return $this->container->resolve(ConsoleApplication::class);
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the console application', 0, $ex);
        }
    }
}
