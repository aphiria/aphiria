<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
 *
 * @extends HashTable<string, string|int|float|list<string|int|float>>
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
            $headerString .= "{$kvp->key}: " . \implode(', ', \array_map(static fn (mixed $value) => (string)$value, (array)$kvp->value)) . "\r\n";
        }

        return \rtrim($headerString);
    }

    /**
     * Headers are allowed to have multiple values, so we must add support for that
     *
     * @inheritdoc
     * @param bool $append Whether or not to append the value to the other header values
     * @throws InvalidArgumentException Thrown if the header value is not a valid type
     */
    public function add(mixed $key, mixed $value, bool $append = false): void
    {
        self::validateHeaderValue($value);
        /** @var string|int|float|list<string|int|float> $value At this point, we know the value is one of these types */
        $normalizedName = self::normalizeHeaderName((string)$key);

        if (!$append || !$this->containsKey($normalizedName)) {
            parent::add($normalizedName, (array)$value);
        } else {
            $currentValues = [];
            $this->tryGet($normalizedName, $currentValues);
            parent::add($normalizedName, [...(array)$currentValues, ...(array)$value]);
        }
    }

    /**
     * @inheritdoc
     * @throws InvalidArgumentException Thrown if the header value is not a valid type
     */
    public function addRange(array $values): void
    {
        foreach ($values as $kvp) {
            /** @psalm-suppress DocblockTypeContradiction We do not want to rely solely on Psalm's type checks */
            if (!$kvp instanceof KeyValuePair) {
                throw new InvalidArgumentException('Value must be instance of ' . KeyValuePair::class);
            }

            $this->add($kvp->key, $kvp->value);
        }
    }

    /**
     * @inheritdoc
     */
    public function containsKey(mixed $key): bool
    {
        return parent::containsKey(self::normalizeHeaderName((string)$key));
    }

    /**
     * @inheritdoc
     */
    public function get(mixed $key): mixed
    {
        return parent::get(self::normalizeHeaderName((string)$key));
    }

    /**
     * Gets the first value of a header
     *
     * @param string $name The name of the header whose value we want
     * @return string|int|float The first value of the header
     * @throws OutOfBoundsException Thrown if the header could not be found
     * @throws RuntimeException Thrown if the key could not be calculated
     */
    public function getFirst(string $name): string|int|float
    {
        if (!$this->containsKey($name)) {
            throw new OutOfBoundsException("Header \"$name\" does not exist");
        }

        return ((array)$this->get($name))[0];
    }

    /**
     * @inheritdoc
     */
    public function removeKey(mixed $key): void
    {
        parent::removeKey(self::normalizeHeaderName((string)$key));
    }

    /**
     * Tries to get the first value of a header
     *
     * @param string $name The name of the header whose value we want
     * @param string|int|float|null $value The value, if it is found
     * @param-out string|int|float|null $value
     * @return bool True if the key exists, otherwise false
     * @throws RuntimeException Thrown if the key could not be calculated
     */
    public function tryGetFirst(mixed $name, mixed &$value): bool
    {
        try {
            $value = ((array)$this->get($name))[0];

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
        return \ucwords(\str_replace('_', '-', \strtolower($name)), '-');
    }

    /**
     * Validates the header value type
     *
     * @param mixed $value The header value to validate
     * @throws InvalidArgumentException Thrown if the header value is invalid
     */
    private static function validateHeaderValue(mixed $value): void
    {
        $exceptionMessage = 'Header values can only be strings, numbers, or lists of strings or numbers';

        if (\is_string($value) || \is_int($value) || \is_float($value)) {
            return;
        }

        if (\is_array($value)) {
            foreach ($value as $singleValue) {
                if (!\is_string($singleValue) && !\is_int($singleValue) && !\is_float($singleValue)) {
                    throw new InvalidArgumentException($exceptionMessage);
                }
            }

            return;
        }

        throw new InvalidArgumentException($exceptionMessage);
    }
}
