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
 * Defines a key-value pair
 *
 * @template TKey
 * @template TValue
 */
readonly class KeyValuePair
{
    /**
     * @param TKey $key The key
     * @param TValue $value The value
     */
    public function __construct(public mixed $key, public mixed $value)
    {
    }
}
