<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration;

/**
 * Defines the global configuration builder
 */
class GlobalConfigurationBuilder
{
    /** @var IConfiguration[] The list of configuration sources */
    private array $configurationSources = [];
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
     */
    public function build(): void
    {
        GlobalConfiguration::resetConfigurationSources();

        foreach ($this->configurationSources as $configurationSource) {
            GlobalConfiguration::addConfigurationSource($configurationSource);
        }
    }

    /**
     * Adds another source to the global configuration
     *
     * @param IConfiguration $configurationSource The source to add
     * @return self For chaining
     */
    public function withConfigurationSource(IConfiguration $configurationSource): self
    {
        $this->configurationSources[] = $configurationSource;

        return $this;
    }

    /**
     * Adds environment variables to the global configuration
     *
     * @return self For chaining
     */
    public function withEnvironmentVariables(): self
    {
        $this->withConfigurationSource(new HashTableConfiguration($_ENV));

        return $this;
    }

    /**
     * Adds a JSON file that contains a configuration object to the global configuration
     *
     * @param string $path The path to the PHP file
     * @param string $pathDelimiter The delimiter between nested path segments
     * @return self For chaining
     * @throws ConfigurationException Thrown if the JSON file was non-existent or did not contain valid JSON
     */
    public function withJsonFileConfigurationSource(string $path, string $pathDelimiter = '.'): self
    {
        $this->withConfigurationSource($this->jsonConfigurationFileReader->readConfiguration($path, $pathDelimiter));

        return $this;
    }

    /**
     * Adds a PHP file that returns a configuration array to the global configuration
     *
     * @param string $path The path to the PHP file
     * @param string $pathDelimiter The delimiter between nested path segments
     * @return self For chaining
     * @throws ConfigurationException Thrown if the PHP file was non-existent or did not contain an array
     */
    public function withPhpFileConfigurationSource(string $path, string $pathDelimiter = '.'): self
    {
        $this->withConfigurationSource($this->phpConfigurationFileReader->readConfiguration($path, $pathDelimiter));

        return $this;
    }
}
