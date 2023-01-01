<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Configuration;

use Aphiria\Application\Configuration\InvalidConfigurationFileException;
use Aphiria\Application\Configuration\JsonConfigurationFileReader;
use PHPUnit\Framework\TestCase;

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
        $this->assertSame('bar', $configuration->getString('foo'));
    }

    public function testReadingConfigurationWithCustomDelimiterAllowsAccessWithThatDelimiter(): void
    {
        $configuration = $this->reader->readConfiguration(__DIR__ . '/files/configuration-delimiter.json', ':');
        $this->assertSame('baz', $configuration->getString('foo:bar'));
    }

    public function testReadingInvalidJsonThrowsException(): void
    {
        $path = __DIR__ . '/files/invalid-configuration.json';
        $this->expectException(InvalidConfigurationFileException::class);
        $this->expectExceptionMessage("Invalid JSON in $path");
        $this->reader->readConfiguration($path);
    }

    public function testReadingNonExistentPathThrowsException(): void
    {
        $this->expectException(InvalidConfigurationFileException::class);
        $this->expectExceptionMessage('/doesnotexist does not exist');
        $this->reader->readConfiguration('/doesnotexist');
    }
}
