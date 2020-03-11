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
 * Defines a wrapper around a hash table configuration
 */
class HashTableConfiguration implements IConfiguration
{
    /** @var array The hash table that backs the configuration */
    private array $hashTable;
    /** @var string The delimiter to use for nested path segments */
    private string $pathDelimiter;

    /**
     * @param array $hashTable The hash table that backs the configuration
     * @param string $pathDelimiter The delimiter to use for nested path segments
     */
    public function __construct(array $hashTable, string $pathDelimiter = '.')
    {
        $this->hashTable = $hashTable;
        $this->pathDelimiter = $pathDelimiter;
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
        $explodedPath = \explode($this->pathDelimiter, $path);
        $value = $this->hashTable;

        foreach ($explodedPath as $i => $pathPart) {
            if (!isset($value[$pathPart])) {
                $fullPathToThisPart = implode($this->pathDelimiter, \array_slice($explodedPath, 0, $i + 1));

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
