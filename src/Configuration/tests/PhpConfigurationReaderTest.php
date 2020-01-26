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
use Aphiria\Configuration\ConfigurationReadException;
use Aphiria\Configuration\PhpConfigurationReader;
use PHPUnit\Framework\TestCase;

/**
 * Tests the PHP configuration reader
 */
class PhpConfigurationReaderTest extends TestCase
{
    public function testReadingConfigurationCreatesConfigurationFromContentsOfPhpFile(): void
    {
        $configReader = new PhpConfigurationReader(__DIR__ . '/Mocks/configuration.php');
        $configReader->readConfiguration();
        $this->assertEquals('bar', Configuration::getString('foo'));
    }

    public function testReadingNonExistentPathThrowsException(): void
    {
        $this->expectException(ConfigurationReadException::class);
        $this->expectExceptionMessage('/doesnotexist does not exist');
        $configReader = new PhpConfigurationReader('/doesnotexist');
        $configReader->readConfiguration();
    }
}
