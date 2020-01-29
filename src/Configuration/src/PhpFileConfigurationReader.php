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
final class PhpFileConfigurationReader implements IConfigurationReader
{
    /** @var string The path to the PHP file */
    private string $path;

    /**
     * @param string $path The path to the PHP file
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @inheritdoc
     */
    public function readConfiguration(): Configuration
    {
        if (!\file_exists($this->path)) {
            throw new ConfigurationException("{$this->path} does not exist");
        }

        $rawConfig = require $this->path;

        return new Configuration($rawConfig);
    }
}
