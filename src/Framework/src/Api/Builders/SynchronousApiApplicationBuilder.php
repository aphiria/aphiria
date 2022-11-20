<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Builders;

use Aphiria\Application\Builders\ApplicationBuilder;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Framework\Api\SynchronousApiApplication;
use RuntimeException;

/**
 * Defines the application builder for synchronous API applications
 */
final class SynchronousApiApplicationBuilder extends ApplicationBuilder
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
    public function build(): SynchronousApiApplication
    {
        $this->configureModules();
        $this->buildComponents();

        try {
            return $this->container->resolve(SynchronousApiApplication::class);
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the API application', 0, $ex);
        }
    }
}
