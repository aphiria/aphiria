<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Configuration;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines the configuration reader that reads YAML files
 *
 */
class YamlConfigurationFileReader implements IConfigurationFileReader
{
    /**
     * @inheritdoc
     */
    public function readConfiguration(string $path, string $pathDelimiter = '.'): IConfiguration
    {
        if (!\file_exists($path)) {
            throw new InvalidConfigurationFileException("$path does not exist");
        }

        try {
            $hashTable = Yaml::parseFile($path);

            if (!\is_array($hashTable) || \array_is_list($hashTable)) {
                throw new InvalidConfigurationFileException("YAML in $path does not parse to an associative array");
            }
        } catch (ParseException $ex) {
            throw new InvalidConfigurationFileException("Invalid YAML in $path", 0, $ex);
        }

        /** @var array<string, mixed> $hashTable */
        return new HashTableConfiguration($hashTable, $pathDelimiter);
    }
}
