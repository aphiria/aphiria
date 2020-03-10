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
 * Defines the interface for configuration readers to implement
 */
interface IConfigurationFileReader
{
    /**
     * Reads the configuration from storage
     *
     * @param string $path The path to the file to read
     * @return IConfiguration The configuration that was read
     * @throws ConfigurationException Thrown if the configuration could not be read
     */
    public function readConfiguration(string $path): IConfiguration;
}
