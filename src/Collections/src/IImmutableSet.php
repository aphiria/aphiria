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

use Countable;
use IteratorAggregate;
use RuntimeException;

/**
 * Defines the interface for immutable sets to implement
 */
interface IImmutableSet extends Countable, IteratorAggregate
{
    /**
     * Gets whether or not the value exists
     *
     * @param mixed $value The value to search for
     * @return bool True if the value exists, otherwise false
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function containsValue($value): bool;

    /**
     * Gets all of the values as an array
     *
     * @return array All of the values
     */
    public function toArray(): array;
}
