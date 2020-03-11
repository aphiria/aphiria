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
use Aphiria\Configuration\JsonConfigurationFileReader;
use PHPUnit\Framework\TestCase;

/**
 * Tests the JSON file configuration reader
 */
class JsonConfigurationFileReaderTest extends TestCase
{
    private JsonConfigurationFileReader $reader;

    protected function setUp(): void
    {
        $this->reader = new JsonConfigurationFileReader();
    }

    public function testReadingConfigurationCreatesConfigurationFromContentsOfJsonFile(): void
    {
        $configuration = $this->reader->readConfiguration(__DIR__ . '/files/configuration.json');
        $this->assertEquals('bar', $configuration->getString('foo'));
    }

    public function testReadingConfigurationWithCustomDelimiterAllowsAccessWithThatDelimiter(): void
    {
        $configuration = $this->reader->readConfiguration(__DIR__ . '/files/configuration-delimiter.json', ':');
        $this->assertEquals('baz', $configuration->getString('foo:bar'));
    }

    public function testReadingInvalidJsonThrowsException(): void
    {
        $path = __DIR__ . '/files/invalid-configuration.json';
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Invalid JSON in $path");
        $this->reader->readConfiguration($path);
    }

    public function testReadingNonExistentPathThrowsException(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('/doesnotexist does not exist');
        $this->reader->readConfiguration('/doesnotexist');
    }
}
