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
 * Defines a wrapper around an application's raw configuration
 */
class ArrayConfiguration implements IConfiguration
{
    /** @var array The raw config */
    private array $rawConfig;

    /**
     * @param array $rawConfig The raw config
     */
    public function __construct(array $rawConfig)
    {
        $this->rawConfig = $rawConfig;
    }

    /**
     * @inheritdoc
     */
    public function getArray(string $path): array
    {
        return (array)$this->getValue($path);
    }

    /**
     * @inheritdoc
     */
    public function getBool(string $path): bool
    {
        return (bool)$this->getValue($path);
    }

    /**
     * @inheritdoc
     */
    public function getFloat(string $path): float
    {
        return (float)$this->getValue($path);
    }

    /**
     * @inheritdoc
     */
    public function getInt(string $path): int
    {
        return (int)$this->getValue($path);
    }

    /**
     * @inheritdoc
     */
    public function getString(string $path): string
    {
        return (string)$this->getValue($path);
    }

    /**
     * @inheritdoc
     */
    public function getValue(string $path)
    {
        $explodedPath = \explode('.', $path);
        $value = $this->rawConfig;

        foreach ($explodedPath as $i => $pathPart) {
            if (!isset($value[$pathPart])) {
                $fullPathToThisPart = implode('.', \array_slice($explodedPath, 0, $i + 1));

                throw new ConfigurationException("No configuration value at $fullPathToThisPart");
            }

            $value = $value[$pathPart];
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function tryGetArray(string $path, ?array &$value): bool
    {
        try {
            $value = $this->getArray($path);

            return true;
        } catch (ConfigurationException $ex) {
            $value = null;

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function tryGetBool(string $path, ?bool &$value): bool
    {
        try {
            $value = $this->getBool($path);

            return true;
        } catch (ConfigurationException $ex) {
            $value = null;

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function tryGetFloat(string $path, ?float &$value): bool
    {
        try {
            $value = $this->getFloat($path);

            return true;
        } catch (ConfigurationException $ex) {
            $value = null;

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function tryGetInt(string $path, ?int &$value): bool
    {
        try {
            $value = $this->getInt($path);

            return true;
        } catch (ConfigurationException $ex) {
            $value = null;

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function tryGetString(string $path, ?string &$value): bool
    {
        try {
            $value = $this->getString($path);

            return true;
        } catch (ConfigurationException $ex) {
            $value = null;

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function tryGetValue(string $path, &$value): bool
    {
        try {
            $value = $this->getValue($path);

            return true;
        } catch (ConfigurationException $ex) {
            $value = null;

            return false;
        }
    }
}
