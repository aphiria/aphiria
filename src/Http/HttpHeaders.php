<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use InvalidArgumentException;
use Opulence\Collections\HashTable;
use Opulence\Collections\KeyValuePair;
use OutOfBoundsException;
use RuntimeException;

/**
 * Defines HTTP headers
 */
final class HttpHeaders extends HashTable
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
     * @param string|array $values The value or values
     * @param bool $append Whether or not to append the value to to the other header values
     */
    public function add($name, $values, bool $append = false): void
    {
        $normalizedName = self::normalizeHeaderName($name);

        if (!$append || !$this->containsKey($normalizedName)) {
            parent::add($normalizedName, (array)$values);
        } else {
            $currentValues = [];
            $this->tryGet($normalizedName, $currentValues);
            parent::add($normalizedName, array_merge($currentValues, (array)$values));
        }
    }

    /**
     * @inheritdoc
     */
    public function addRange(array $kvps): void
    {
        foreach ($kvps as $kvp) {
            if (!$kvp instanceof KeyValuePair) {
                throw new InvalidArgumentException('Value must be instance of ' . KeyValuePair::class);
            }

            $this->add($kvp->getKey(), $kvp->getValue());
        }
    }

    /**
     * @inheritdoc
     */
    public function containsKey($name): bool
    {
        return parent::containsKey(self::normalizeHeaderName($name));
    }

    /**
     * @inheritdoc
     */
    public function get($name)
    {
        return parent::get(self::normalizeHeaderName($name));
    }

    /**
     * Gets the first value of a header
     *
     * @param string $name The name of the header whose value we want
     * @return mixed The first value of the header
     * @throws OutOfBoundsException Thrown if the header could not be found
     * @throws RuntimeException Thrown if the key could not be calculated
     */
    public function getFirst($name)
    {
        if (!$this->containsKey($name)) {
            throw new OutOfBoundsException("Header \"$name\" does not exist");
        }

        return $this->get($name)[0];
    }

    /**
     * @inheritdoc
     */
    public function removeKey($name): void
    {
        parent::removeKey(self::normalizeHeaderName($name));
    }

    /**
     * Tries to get the first value of a header
     *
     * @param mixed $name The name of the header whose value we want
     * @param mixed $value The value, if it is found
     * @return bool True if the key exists, otherwise false
     * @throws RuntimeException Thrown if the key could not be calculated
     */
    public function tryGetFirst($name, &$value): bool
    {
        try {
            $value = $this->get($name)[0];

            return true;
        } catch (OutOfBoundsException $ex) {
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
