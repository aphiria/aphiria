<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Requests\Compilers;

use Aphiria\Console\Requests\Request;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the interface for request compilers to implement
 */
interface IRequestCompiler
{
    /**
     * Compiles raw input into a request
     *
     * @param mixed $input The input to compile
     * @return Request The compiled request
     * @throws InvalidArgumentException Thrown if the input was not of the type the compiler was expecting
     * @throws RuntimeException Thrown if the input could not be compiled
     */
    public function compile($input): Request;
}
