<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Compilers;

use RuntimeException;

/**
 * Defines the interface for output compilers to implement
 */
interface IOutputCompiler
{
    /**
     * Compiles a message
     *
     * @param string $message The message to compile
     * @param bool $includeStyles Whether or not to include output styles
     * @return string The compiled message
     * @throws RuntimeException Thrown if there was an issue compiling the message
     */
    public function compile(string $message, bool $includeStyles = true): string;
}
