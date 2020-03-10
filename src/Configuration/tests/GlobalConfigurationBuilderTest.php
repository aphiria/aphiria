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

use Aphiria\Configuration\HashTableConfiguration;
use Aphiria\Configuration\ConfigurationException;
use Aphiria\Configuration\GlobalConfiguration;
use Aphiria\Configuration\GlobalConfigurationBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Tests the global configuration builder
 */
class GlobalConfigurationBuilderTest extends TestCase
{
    private GlobalConfigurationBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new GlobalConfigurationBuilder();
        GlobalConfiguration::resetConfigurationSources();
    }

    public function testBuildIncludesAllSources(): void
    {
        $configurationSource = new HashTableConfiguration(['foo' => 'bar']);
        $this->builder->withConfigurationSource($configurationSource)
            ->build();
        $this->assertEquals('bar', GlobalConfiguration::getString('foo'));
    }

    public function testBuildRemovesExistingSources(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 'bar']));
        $newConfigurationSource = new HashTableConfiguration(['baz' => 'blah']);
        $this->builder->withConfigurationSource($newConfigurationSource)
            ->build();
        $value = null;
        $this->assertFalse(GlobalConfiguration::tryGetString('foo', $value));
        $this->assertEquals('blah', GlobalConfiguration::getString('baz'));
    }

    public function testWithEnvironmentVariablesAddsConfigurationSourceWithEnvironmentVariables(): void
    {
        $_ENV['__aphiria_test'] = 'foo';
        $this->builder->withEnvironmentVariables()
            ->build();
        $this->assertEquals('foo', GlobalConfiguration::getString('__aphiria_test'));
    }

    public function testWithJsonFileAddsConfigurationSourceFromContentsOfJsonFile(): void
    {
        $this->assertSame($this->builder,
            $this->builder->withJsonFileConfigurationSource(__DIR__ . '/Mocks/configuration.json')
        );
        $this->builder->build();
        $this->assertEquals('bar', GlobalConfiguration::getString('foo'));
    }

    public function testWithJsonFileThatContainsInvalidJsonThrowsException(): void
    {
        $path = __DIR__ . '/Mocks/invalid-configuration.json';
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Invalid JSON in $path");
        $this->builder->withJsonFileConfigurationSource($path);
    }

    public function testWithJsonFileForNonExistentPathThrowsException(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('/doesnotexist does not exist');
        $this->builder->withJsonFileConfigurationSource('/doesnotexist');
    }

    public function testWithJsonFileWithCustomDelimiterIsRespected(): void
    {
        $this->assertSame($this->builder,
            $this->builder->withJsonFileConfigurationSource(__DIR__ . '/Mocks/configuration-delimiter.json', ':')
        );
        $this->builder->build();
        $this->assertEquals('baz', GlobalConfiguration::getString('foo:bar'));
    }

    public function testWithPhpFileAddsConfigurationSourceFromContentsOfPhpFile(): void
    {
        $this->assertSame(
            $this->builder,
            $this->builder->withPhpFileConfigurationSource(__DIR__ . '/Mocks/configuration.php')
        );
        $this->builder->build();
        $this->assertEquals('bar', GlobalConfiguration::getString('foo'));
    }

    public function testWithPhpFileThatContainsInvalidPhpThrowsException(): void
    {
        $path = __DIR__ . '/Mocks/invalid-configuration.json';
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Configuration in $path must be an array");
        $this->builder->withPhpFileConfigurationSource($path);
    }

    public function testWithPhpFileForNonExistentPathThrowsException(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('/doesnotexist does not exist');
        $this->builder->withPhpFileConfigurationSource('/doesnotexist');
    }

    public function testWithPhpFileWithCustomDelimiterIsRespected(): void
    {
        $this->assertSame($this->builder,
            $this->builder->withPhpFileConfigurationSource(__DIR__ . '/Mocks/configuration-delimiter.php', ':')
        );
        $this->builder->build();
        $this->assertEquals('baz', GlobalConfiguration::getString('foo:bar'));
    }
}
