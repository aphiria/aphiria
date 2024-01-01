<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Configuration;

use Aphiria\Application\Configuration\InvalidConfigurationFileException;
use Aphiria\Application\Configuration\YamlConfigurationFileReader;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class YamlConfigurationFileReaderTest extends TestCase
{
    private YamlConfigurationFileReader $reader;

    protected function setUp(): void
    {
        $this->reader = new YamlConfigurationFileReader();
    }

    public function testEmptyPathDelimiterThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Path delimiter cannot be empty');
        /** @psalm-suppress InvalidArgument Purposely testing an empty path delimiter */
        $this->reader->readConfiguration(__DIR__ . '/files/configuration.yaml', '');
    }

    public function testReadingConfigurationCreatesConfigurationFromContentsOfYamlFile(): void
    {
        $configuration = $this->reader->readConfiguration(__DIR__ . '/files/configuration.yaml');
        $this->assertSame('bar', $configuration->getString('foo'));
    }

    public function testReadingConfigurationWithCustomDelimiterAllowsAccessWithThatDelimiter(): void
    {
        $configuration = $this->reader->readConfiguration(__DIR__ . '/files/configuration-delimiter.yaml', ':');
        $this->assertSame('baz', $configuration->getString('foo:bar'));
    }

    public function testReadingInvalidYamlThrowsException(): void
    {
        $path = __DIR__ . '/files/invalid-configuration.yaml';
        $this->expectException(InvalidConfigurationFileException::class);
        $this->expectExceptionMessage("Invalid YAML in $path");
        $this->reader->readConfiguration($path);
    }

    public function testReadingNonExistentPathThrowsException(): void
    {
        $this->expectException(InvalidConfigurationFileException::class);
        $this->expectExceptionMessage('/doesnotexist does not exist');
        $this->reader->readConfiguration('/doesnotexist');
    }

    public function testReadingYamlThatDoesNotMapToAssociativeArrayThrowsException(): void
    {
        $path = __DIR__ . '/files/non-hash-table.yaml';
        $this->expectException(InvalidConfigurationFileException::class);
        $this->expectExceptionMessage("YAML in $path does not parse to an associative array");
        $this->reader->readConfiguration($path);
    }
}
