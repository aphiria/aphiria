<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Extensions;

use BadMethodCallException;

/**
 * Defines the extension methods trait
 */
trait ExtensionMethods
{
    /**
     * Calls a method on an extendable object
     *
     * @param string $method The name of the method to call
     * @param list<mixed> $args The list of arguments to pass in
     * @return mixed The return value, if there was one
     * @throws BadMethodCallException Thrown if the input method did not exist
     */
    public function __call(string $method, array $args): mixed
    {
        /** @psalm-suppress InternalClass We're intentionally calling the internal class */
        return Extensions::call($this, $method, $args);
    }
}
