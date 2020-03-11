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

use JsonException;

/**
 * Defines the configuration reader that reads JSON files
 */
class JsonConfigurationFileReader implements IConfigurationFileReader
{
    /**
     * @inheritdoc
     */
    public function readConfiguration($path, string $pathDelimiter = '.'): IConfiguration
    {
        if (!\file_exists($path)) {
            throw new ConfigurationException("$path does not exist");
        }

        try {
            $decodedJson = \json_decode(\file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            throw new ConfigurationException("Invalid JSON in $path", 0, $ex);
        }

        return new HashTableConfiguration($decodedJson, $pathDelimiter);
    }
}
