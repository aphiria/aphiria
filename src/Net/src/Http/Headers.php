<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Aphiria\Collections\HashTable;
use Aphiria\Collections\KeyValuePair;
use InvalidArgumentException;
use OutOfBoundsException;
use RuntimeException;

/**
 * Defines HTTP headers
 */
final class Headers extends HashTable
{
    /**
     * Gets the headers as a string
     * Note: This can be used for the headers of a raw HTTP message
     *
     * @return string The serialized headers
     */
    public function __toString(): string
    {
        $headerString = '';

        foreach ($this->hashKeysToKvps as $kvp) {
            $headerString .= "{$kvp->getKey()}: " . implode(', ', $kvp->getValue()) . "\r\n";
        }

        return rtrim($headerString);
    }

    /**
     * Headers are allowed to have multiple values, so we must add support for that
     *
     * @inheritdoc
     *
     * @param array-key|mixed $key The header name to add
     * @param mixed|string[]|int|string $value The value or values
     * @param bool $append Whether or not to append the value to to the other header values
     */
    public function add(mixed $key, mixed $value, bool $append = false): void
    {
        $normalizedName = self::normalizeHeaderName($key);

        if (!$append || !$this->containsKey($normalizedName)) {
            parent::add($normalizedName, (array)$value);
        } else {
            $currentValues = [];
            $this->tryGet($normalizedName, $currentValues);
            parent::add($normalizedName, [...$currentValues, ...(array)$value]);
        }
    }

    /**
     * @inheritdoc
     */
    public function addRange(array $values): void
    {
        foreach ($values as $kvp) {
            if (!$kvp instanceof KeyValuePair) {
                throw new InvalidArgumentException('Value must be instance of ' . KeyValuePair::class);
            }

            $this->add($kvp->getKey(), $kvp->getValue());
        }
    }

    /**
     * @inheritdoc
     */
    public function containsKey(mixed $key): bool
    {
        return parent::containsKey(self::normalizeHeaderName($key));
    }

    /**
     * @inheritdoc
     */
    public function get(mixed $key): mixed
    {
        return parent::get(self::normalizeHeaderName($key));
    }

    /**
     * Gets the first value of a header
     *
     * @param string $name The name of the header whose value we want
     * @return mixed The first value of the header
     * @throws OutOfBoundsException Thrown if the header could not be found
     * @throws RuntimeException Thrown if the key could not be calculated
     */
    public function getFirst(string $name): mixed
    {
        if (!$this->containsKey($name)) {
            throw new OutOfBoundsException("Header \"$name\" does not exist");
        }

        return $this->get($name)[0];
    }

    /**
     * @inheritdoc
     */
    public function removeKey(mixed $key): void
    {
        parent::removeKey(self::normalizeHeaderName($key));
    }

    /**
     * Tries to get the first value of a header
     *
     * @param mixed $name The name of the header whose value we want
     * @param mixed $value The value, if it is found
     * @param-out mixed $value
     * @return bool True if the key exists, otherwise false
     * @throws RuntimeException Thrown if the key could not be calculated
     */
    public function tryGetFirst(mixed $name, mixed &$value): bool
    {
        try {
            $value = $this->get($name)[0];

            return true;
        } catch (OutOfBoundsException) {
            return false;
        }
    }

    /**
     * Normalizes the name of the header so that capitalization and snake-casing doesn't matter
     *
     * @param string $name The name of the header to normalize
     * @return string The normalized header name
     */
    private static function normalizeHeaderName(string $name): string
    {
        return ucwords(str_replace('_', '-', strtolower($name)), '-');
    }
}
