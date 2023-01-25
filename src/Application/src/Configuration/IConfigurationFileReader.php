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

use InvalidArgumentException;

/**
 * Defines the interface for configuration file readers to implement
 */
interface IConfigurationFileReader
{
    /**
     * Reads the configuration from storage
     *
     * @param string $path The path to the file to read
     * @param non-empty-string $pathDelimiter The delimiter for nested path segments
     * @return IConfiguration The configuration that was read
     * @throws InvalidArgumentException Thrown if the path delimiter was invalid
     * @throws InvalidConfigurationFileException Thrown if the configuration could not be read
     */
    public function readConfiguration(string $path, string $pathDelimiter = '.'): IConfiguration;
}
