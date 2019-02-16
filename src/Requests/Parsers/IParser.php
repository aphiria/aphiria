<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Requests\Parsers;

use InvalidArgumentException;
use Aphiria\Console\Requests\IRequest;
use RuntimeException;

/**
 * Defines the interface for request parsers to implement
 */
interface IParser
{
    /**
     * Parses raw input into a request
     *
     * @param mixed $input The input to parse
     * @return IRequest The parsed request
     * @throws InvalidArgumentException Thrown if the input was not of the type the parser was expecting
     * @throws RuntimeException Thrown if the input could not be parsed
     */
    public function parse($input): IRequest;
}
