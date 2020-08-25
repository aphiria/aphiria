<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Configuration;

use Aphiria\Application\Configuration\InvalidConfigurationFileException;
use Aphiria\Application\Configuration\PhpConfigurationFileReader;
use PHPUnit\Framework\TestCase;

class PhpConfigurationFileReaderTest extends TestCase
{
    private PhpConfigurationFileReader $reader;

    protected function setUp(): void
    {
        $this->reader = new PhpConfigurationFileReader();
    }

    public function testReadingConfigurationCreatesConfigurationFromContentsOfPhpFile(): void
    {
        $configuration = $this->reader->readConfiguration(__DIR__ . '/files/configuration.php');
        $this->assertSame('bar', $configuration->getString('foo'));
    }

    public function testReadingConfigurationWithCustomDelimiterAllowsAccessWithThatDelimiter(): void
    {
        $configuration = $this->reader->readConfiguration(__DIR__ . '/files/configuration-delimiter.php', ':');
        $this->assertSame('baz', $configuration->getString('foo:bar'));
    }

    public function testReadingInvalidPhpThrowsException(): void
    {
        $path = __DIR__ . '/files/invalid-configuration.php';
        $this->expectException(InvalidConfigurationFileException::class);
        $this->expectExceptionMessage("Configuration in $path must be an array");
        $this->reader->readConfiguration($path);
    }

    public function testReadingNonExistentPathThrowsException(): void
    {
        $this->expectException(InvalidConfigurationFileException::class);
        $this->expectExceptionMessage('/doesnotexist does not exist');
        $this->reader->readConfiguration('/doesnotexist');
    }
}
