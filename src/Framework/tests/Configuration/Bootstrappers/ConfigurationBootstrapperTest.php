<?php
/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Configuration\Bootstrappers;

use Aphiria\Configuration\GlobalConfiguration;
use Aphiria\Configuration\IConfiguration;
use Aphiria\Configuration\IConfigurationReader;
use Aphiria\Framework\Configuration\Bootstrappers\ConfigurationBootstrapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the configuration bootstrapper
 */
class ConfigurationBootstrapperTest extends TestCase
{
    /** @var IConfigurationReader|MockObject */
    private IConfigurationReader $configurationReader;
    private ConfigurationBootstrapper $configurationBootstrapper;

    protected function setUp(): void
    {
        $this->configurationReader = $this->createMock(IConfigurationReader::class);
        $this->configurationBootstrapper = new ConfigurationBootstrapper($this->configurationReader);
    }

    public function testBootstrapSetsGlobalInstanceFromReadConfiguration(): void
    {
        $expectedConfiguration = $this->createMock(IConfiguration::class);
        $expectedConfiguration->expects($this->once())
            ->method('getString')
            ->with('foo')
            ->willReturn('bar');
        $this->configurationReader->expects($this->once())
            ->method('readConfiguration')
            ->willReturn($expectedConfiguration);
        $this->configurationBootstrapper->bootstrap();
        $this->assertEquals('bar', GlobalConfiguration::getString('foo'));
    }
}
