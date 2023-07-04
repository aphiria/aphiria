<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api;

use Aphiria\Application\ApplicationBuilder;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\DependencyInjection\ResolutionException;
use RuntimeException;

/**
 * Defines the application builder for synchronous API applications
 */
final class SynchronousApiApplicationBuilder extends ApplicationBuilder
{
    /**
     * @param IServiceResolver $serviceResolver The resolver to use
     */
    public function __construct(private readonly IServiceResolver $serviceResolver)
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
            return $this->serviceResolver->resolve(SynchronousApiApplication::class);
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the API application', 0, $ex);
        }
    }
}
