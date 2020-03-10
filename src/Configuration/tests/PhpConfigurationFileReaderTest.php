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

use Aphiria\Configuration\ConfigurationException;
use Aphiria\Configuration\PhpConfigurationFileReader;
use PHPUnit\Framework\TestCase;

/**
 * Tests the PHP file configuration reader
 */
class PhpConfigurationFileReaderTest extends TestCase
{
    private PhpConfigurationFileReader $reader;

    protected function setUp(): void
    {
        $this->reader = new PhpConfigurationFileReader();
    }

    public function testReadingConfigurationCreatesConfigurationFromContentsOfPhpFile(): void
    {
        $configuration = $this->reader->readConfiguration(__DIR__ . '/Mocks/configuration.php');
        $this->assertEquals('bar', $configuration->getString('foo'));
    }

    public function testReadingInvalidPhpThrowsException(): void
    {
        $path = __DIR__ . '/Mocks/invalid-configuration.json';
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Configuration in $path must be an array");
        $this->reader->readConfiguration($path);
    }

    public function testReadingNonExistentPathThrowsException(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('/doesnotexist does not exist');
        $this->reader->readConfiguration('/doesnotexist');
    }
}
