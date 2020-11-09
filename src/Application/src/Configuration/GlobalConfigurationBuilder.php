<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Configuration;

use Closure;

/**
 * Defines the global configuration builder
 */
class GlobalConfigurationBuilder
{
    /** @var array<array-key, array{type: string, value: IConfiguration|Closure(): IConfiguration}> The list of structs that store data about the configuration sources */
    private array $configurationSourceStructs = [];
    /** @var IConfigurationFileReader The PHP configuration file reader */
    private IConfigurationFileReader $phpConfigurationFileReader;
    /** @var IConfigurationFileReader The JSON configuration file reader */
    private IConfigurationFileReader $jsonConfigurationFileReader;

    /**
     * @param IConfigurationFileReader|null $phpConfigurationFileReader The PHP configuration file reader
     * @param IConfigurationFileReader|null $jsonConfigurationFileReader The JSON configuration file reader
     */
    public function __construct(
        IConfigurationFileReader $phpConfigurationFileReader = null,
        IConfigurationFileReader $jsonConfigurationFileReader = null
    ) {
        $this->phpConfigurationFileReader = $phpConfigurationFileReader ?? new PhpConfigurationFileReader();
        $this->jsonConfigurationFileReader = $jsonConfigurationFileReader ?? new JsonConfigurationFileReader();
    }

    /**
     * Builds the global configuration
     *
     * @psalm-suppress PossiblyInvalidArgument The "value" will always be an instance of IConfiguration
     * @psalm-suppress PossiblyInvalidFunctionCall The "value" will always resolve an instance of IConfiguration
     */
    public function build(): void
    {
        GlobalConfiguration::resetConfigurationSources();

        foreach ($this->configurationSourceStructs as $configurationSourceStruct) {
            switch ($configurationSourceStruct['type']) {
                case 'instance':
                    GlobalConfiguration::addConfigurationSource($configurationSourceStruct['value']);
                    break;
                case 'factory':
                    GlobalConfiguration::addConfigurationSource($configurationSourceStruct['value']());
                    break;
            }
        }
    }

    /**
     * Adds another source to the global configuration
     *
     * @param IConfiguration $configurationSource The source to add
     * @return static For chaining
     */
    public function withConfigurationSource(IConfiguration $configurationSource): static
    {
        $this->configurationSourceStructs[] = ['type' => 'instance', 'value' => $configurationSource];

        return $this;
    }

    /**
     * Adds environment variables to the global configuration
     *
     * @return static For chaining
     */
    public function withEnvironmentVariables(): static
    {
        /**
         * We delay grabbing the environment variables until we're building the configuration.  This allows us to
         * populate the environment variables (eg in a bootstrapper) prior to adding a configuration with those values.
         *
         * @var array<string, mixed> $_ENV
         */
        $this->configurationSourceStructs[] = ['type' => 'factory', 'value' => fn (): HashTableConfiguration => new HashTableConfiguration($_ENV)];

        return $this;
    }

    /**
     * Adds a JSON file that contains a configuration object to the global configuration
     *
     * @param string $path The path to the PHP file
     * @param string $pathDelimiter The delimiter between nested path segments
     * @return static For chaining
     */
    public function withJsonFileConfigurationSource(string $path, string $pathDelimiter = '.'): static
    {
        $this->configurationSourceStructs[] = [
            'type' => 'factory',
            'value' => fn (): IConfiguration => $this->jsonConfigurationFileReader->readConfiguration($path, $pathDelimiter)
        ];

        return $this;
    }

    /**
     * Adds a PHP file that returns a configuration array to the global configuration
     *
     * @param string $path The path to the PHP file
     * @param string $pathDelimiter The delimiter between nested path segments
     * @return static For chaining
     */
    public function withPhpFileConfigurationSource(string $path, string $pathDelimiter = '.'): static
    {
        $this->configurationSourceStructs[] = [
            'type' => 'factory',
            'value' => fn (): IConfiguration => $this->phpConfigurationFileReader->readConfiguration($path, $pathDelimiter)
        ];

        return $this;
    }
}
