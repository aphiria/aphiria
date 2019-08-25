<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/collections/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections;

use RuntimeException;
use Throwable;

/**
 * Defines the key hasher
 * @internal
 */
class KeyHasher
{
    /**
     * Gets the hash key for a value
     *
     * @param string|float|int|object|array|resource $value The value whose hash key we want
     * @return string The value's hash key
     * @throws RuntimeException Thrown if the value's hash key could not be calculated
     */
    public function getHashKey($value): string
    {
        if (is_string($value)) {
            return "__aphiria:s:$value";
        }

        if (is_int($value)) {
            return "__aphiria:i:$value";
        }

        if (is_float($value)) {
            return "__aphiria:f:$value";
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return "__aphiria:so:$value";
            }

            return '__aphiria:o:' . spl_object_hash($value);
        }

        if (is_array($value)) {
            return '__aphiria:a:' . md5(serialize($value));
        }

        if (is_resource($value)) {
            return '__aphiria:r:' . $value;
        }

        // As a last-ditch effort, try to convert the value to a string
        try {
            return '__aphiria:u' . $value;
        } catch (Throwable $ex) {
            throw new RuntimeException('Value could not be converted to a key', 0, $ex);
        }
    }
}
