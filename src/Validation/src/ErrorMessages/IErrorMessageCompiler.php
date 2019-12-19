<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\ErrorMessages;

/**
 * Defines the interface for error message compilers to implement
 */
interface IErrorMessageCompiler
{
    /**
     * Compiles an error message ID along with placeholders into human-readable error messages
     *
     * @param string $errorMessageId The ID of the error message to compile
     * @param array $errorMessagePlaceholders The optional mapping of placeholder names to values
     * @param string|null $locale The locale to use when compiling the message, or null if not considering it
     * @return string The compiled error message
     * @throws ErrorMessageCompilationException Thrown if the error message could not be compiled
     */
    public function compile(string $errorMessageId, array $errorMessagePlaceholders = [], string $locale = null): string;
}
