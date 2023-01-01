<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Configuration;

/**
 * Defines the configuration reader that reads a PHP files
 */
class PhpConfigurationFileReader implements IConfigurationFileReader
{
    /**
     * @inheritdoc
     */
    public function readConfiguration(string $path, string $pathDelimiter = '.'): IConfiguration
    {
        if (!\file_exists($path)) {
            throw new InvalidConfigurationFileException("$path does not exist");
        }

        $hashTable = require $path;

        if (!\is_array($hashTable)) {
            throw new InvalidConfigurationFileException("Configuration in $path must be an array");
        }

        /** @var array<string, mixed> $hashTable */
        return new HashTableConfiguration($hashTable, $pathDelimiter);
    }
}
