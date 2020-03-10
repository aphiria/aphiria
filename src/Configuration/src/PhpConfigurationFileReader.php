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
 * Defines the configuration reader that reads a PHP files
 */
final class PhpConfigurationFileReader implements IConfigurationFileReader
{
    /**
     * @inheritdoc
     */
    public function readConfiguration(string $path): HashTableConfiguration
    {
        if (!\file_exists($path)) {
            throw new ConfigurationException("$path does not exist");
        }

        $rawConfig = require $path;

        if (!\is_array($rawConfig)) {
            throw new ConfigurationException("Configuration in $path must be an array");
        }

        return new HashTableConfiguration($rawConfig);
    }
}
