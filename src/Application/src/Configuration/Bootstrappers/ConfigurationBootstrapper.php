<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Configuration\Bootstrappers;

use Aphiria\Application\Configuration\GlobalConfigurationBuilder;
use Aphiria\Application\IBootstrapper;

/**
 * Defines the configuration bootstrapper
 */
final class ConfigurationBootstrapper implements IBootstrapper
{
    /**
     * @param GlobalConfigurationBuilder $configurationBuilder The global configuration builder
     */
    public function __construct(private readonly GlobalConfigurationBuilder $configurationBuilder)
    {
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(): void
    {
        $this->configurationBuilder->build();
    }
}
