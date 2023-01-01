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

use Closure;
use RuntimeException;

/**
 * Defines a wrapper around a hash table configuration
 */
class HashTableConfiguration implements IConfiguration
{
    /**
     * @param array<string, mixed> $hashTable The hash table that backs the configuration
     * @param string $pathDelimiter The delimiter to use for nested path segments
     */
    public function __construct(private readonly array $hashTable, private readonly string $pathDelimiter = '.')
    {
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
    public function getObject(string $path, Closure $factory): object
    {
        $object = $factory($this->getValue($path));

        /** @psalm-suppress DocblockTypeContradiction We don't want to solely rely on PHPDoc for types */
        if (!\is_object($object)) {
            throw new RuntimeException('Factory must return an object');
        }

        return $object;
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
    public function getValue(string $path): mixed
    {
        $explodedPath = \explode($this->pathDelimiter, $path);
        $value = $this->hashTable;

        foreach ($explodedPath as $i => $pathPart) {
            if (!isset($value[$pathPart])) {
                $fullPathToThisPart = \implode(
                    $this->pathDelimiter,
                    \array_map(static fn (mixed $value): string => (string)$value, \array_slice($explodedPath, 0, $i + 1))
                );

                throw new MissingConfigurationValueException($fullPathToThisPart);
            }

            /**
             * @psalm-suppress MixedAssignment We are purposely adding mixed values
             * @psalm-suppress MixedArrayAccess The array value is guaranteed to exist per a check above
             */
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
        } catch (MissingConfigurationValueException) {
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
        } catch (MissingConfigurationValueException) {
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
        } catch (MissingConfigurationValueException) {
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
        } catch (MissingConfigurationValueException) {
            $value = null;

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function tryGetObject(string $path, Closure $factory, ?object &$value): bool
    {
        try {
            $value = $this->getObject($path, $factory);

            return true;
        } catch (MissingConfigurationValueException) {
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
        } catch (MissingConfigurationValueException) {
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
            /** @psalm-suppress MixedAssignment We are purposely adding mixed values */
            $value = $this->getValue($path);

            return true;
        } catch (MissingConfigurationValueException) {
            $value = null;

            return false;
        }
    }
}
