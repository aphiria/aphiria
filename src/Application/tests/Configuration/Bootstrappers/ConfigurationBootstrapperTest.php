<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Configuration\Bootstrappers;

use Aphiria\Application\Configuration\Bootstrappers\ConfigurationBootstrapper;
use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\GlobalConfigurationBuilder;
use Aphiria\Application\Configuration\HashTableConfiguration;
use PHPUnit\Framework\TestCase;

class ConfigurationBootstrapperTest extends TestCase
{
    private GlobalConfigurationBuilder $globalConfigurationBuilder;
    private ConfigurationBootstrapper $configurationBootstrapper;

    protected function setUp(): void
    {
        $this->globalConfigurationBuilder = new GlobalConfigurationBuilder();
        $this->configurationBootstrapper = new ConfigurationBootstrapper($this->globalConfigurationBuilder);
        GlobalConfiguration::resetConfigurationSources();
    }

    public function testBootstrapAddsSourcesToGlobalConfiguration(): void
    {
        $expectedConfiguration = new HashTableConfiguration(['foo' => 'bar']);
        $this->globalConfigurationBuilder->withConfigurationSource($expectedConfiguration);
        $this->configurationBootstrapper->bootstrap();
        $this->assertEquals('bar', GlobalConfiguration::getString('foo'));
    }
}
