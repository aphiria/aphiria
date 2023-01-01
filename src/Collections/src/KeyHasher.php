<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections;

/**
 * Defines the key hasher
 * @internal
 */
class KeyHasher
{
    /**
     * Gets the hash key for a value
     *
     * @param mixed $value The value whose hash key we want
     * @return string The value's hash key
     */
    public function getHashKey(mixed $value): string
    {
        if (\is_string($value)) {
            return "__aphiria:s:$value";
        }

        if (\is_int($value)) {
            return "__aphiria:i:$value";
        }

        if (\is_float($value)) {
            return "__aphiria:f:$value";
        }

        if (\is_object($value)) {
            if (\method_exists($value, '__toString')) {
                return "__aphiria:so:$value";
            }

            return '__aphiria:o:' . \spl_object_hash($value);
        }

        if (\is_array($value)) {
            return '__aphiria:a:' . \md5(\serialize($value));
        }

        if (\is_resource($value)) {
            /** @psalm-suppress InvalidOperand This is valid code - bug */
            return '__aphiria:r:' . $value;
        }

        // As a last-ditch effort, try to convert the value to a string
        return "__aphiria:u$value";
    }
}
