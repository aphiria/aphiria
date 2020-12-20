<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input\Compilers;

use Aphiria\Console\Input\Input;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the interface for input compilers to implement
 */
interface IInputCompiler
{
    /**
     * Compiles raw input
     *
     * @param string|array $rawInput The raw input to compile
     * @return Input The compiled input
     * @throws CommandNotFoundException Thrown if the input command was not found
     * @throws InvalidArgumentException Thrown if the input was not of the type the compiler was expecting
     * @throws RuntimeException Thrown if the input could not be compiled
     */
    public function compile(string|array $rawInput): Input;
}
