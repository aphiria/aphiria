<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
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
     * @param IServiceResolver $serviceResolver The resolver to use
     */
    public function __construct(private readonly IServiceResolver $serviceResolver)
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
            return $this->serviceResolver->resolve(ConsoleApplication::class);
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the console application', 0, $ex);
        }
    }
}
