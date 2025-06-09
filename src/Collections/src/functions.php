<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections\Functions;

use Aphiria\Collections\ArrayList;

/**
 * Creates an ArrayList from an array of values
 *
 * @template T
 * @param  array<T>  $values  The values to add to the list
 * @return ArrayList<T> The created list
 */
function array_list(array $values = []): ArrayList
{
    return new ArrayList($values);
}
