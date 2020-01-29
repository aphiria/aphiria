<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Tests;

use Aphiria\Configuration\Configuration;
use Aphiria\Configuration\ConfigurationException;
use Aphiria\Configuration\PhpFileConfigurationReader;
use PHPUnit\Framework\TestCase;

/**
 * Tests the PHP file configuration reader
 */
class PhpFileConfigurationReaderTest extends TestCase
{
    public function testReadingConfigurationCreatesConfigurationFromContentsOfPhpFile(): void
    {
        $configReader = new PhpFileConfigurationReader(__DIR__ . '/Mocks/configuration.php');
        $configReader->readConfiguration();
        $this->assertEquals('bar', Configuration::getString('foo'));
    }

    public function testReadingNonExistentPathThrowsException(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('/doesnotexist does not exist');
        $configReader = new PhpFileConfigurationReader('/doesnotexist');
        $configReader->readConfiguration();
    }
}
