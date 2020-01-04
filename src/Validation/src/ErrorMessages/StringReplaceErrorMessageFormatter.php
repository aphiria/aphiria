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
 * Defines the error message formatter that does a simple string replacement to format error messages
 */
final class StringReplaceErrorMessageFormatter implements IErrorMessageFormatter
{
    /**
     * @inheritdoc
     */
    public function format(string $errorMessageId, array $errorMessagePlaceholders = [], string $locale = null): string
    {
        $formattedErrorMessage = $errorMessageId;

        foreach ($errorMessagePlaceholders as $key => $value) {
            $formattedErrorMessage = \str_replace('{' . $key . '}', $value, $formattedErrorMessage);
        }

        // Remove any unused placeholders from the message
        $formattedErrorMessage = \preg_replace('/{.+}/', '', $formattedErrorMessage);

        return $formattedErrorMessage;
    }
}
