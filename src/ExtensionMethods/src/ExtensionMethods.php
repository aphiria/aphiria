<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ExtensionMethods;

use BadMethodCallException;

/**
 * Defines the extension methods trait
 */
trait ExtensionMethods
{
    /**
     * Calls an extension method
     *
     * @param string $methodName The name of the extension method to call
     * @param list<mixed> $args The list of arguments to pass in
     * @return mixed The return value, if there was one
     * @throws BadMethodCallException Thrown if the input method did not exist
     */
    public function __call(string $methodName, array $args): mixed
    {
        $closure = ExtensionMethodRegistry::getExtensionMethod($this, $methodName);

        if ($closure === null || ($closure = $closure->bindTo($this, $this)) === false) {
            throw new BadMethodCallException($this::class . "::$methodName() does not exist");
        }

        return $closure(...$args);
    }
}
