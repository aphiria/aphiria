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
 * Defines the interface for error message formatters to implement
 */
interface IErrorMessageFormatter
{
    /**
     * Formats an error message ID along with placeholders into human-readable error messages
     *
     * @param string $errorMessageId The ID of the error message to format
     * @param array $errorMessagePlaceholders The optional mapping of placeholder names to values
     * @param string|null $locale The locale to use when formatting the message, or null if not considering it
     * @return string The formatted error message
     * @throws ErrorMessageFormattingException Thrown if the error message could not be formatted
     */
    public function format(string $errorMessageId, array $errorMessagePlaceholders = [], string $locale = null): string;
}
