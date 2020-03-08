<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Configuration\Bootstrappers;

use Aphiria\Application\IBootstrapper;
use Aphiria\Configuration\GlobalConfiguration;
use Aphiria\Configuration\IConfigurationReader;

/**
 * Defines the configuration bootstrapper
 */
final class ConfigurationBootstrapper implements IBootstrapper
{
    private IConfigurationReader $configurationReader;

    /**
     * @param IConfigurationReader $configurationReader The configuration reader
     */
    public function __construct(IConfigurationReader $configurationReader)
    {
        $this->configurationReader = $configurationReader;
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(): void
    {
        GlobalConfiguration::setInstance($this->configurationReader->readConfiguration());
    }
}
