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
 * Defines the error message compiler that does a simple string replacement to compile error messages
 */
final class StringReplaceErrorMessageCompiler implements IErrorMessageCompiler
{
    /**
     * @inheritdoc
     */
    public function compile(string $errorMessageId, array $errorMessagePlaceholders = [], string $locale = null): string
    {
        $compiledErrorMessage = $errorMessageId;

        foreach ($errorMessagePlaceholders as $key => $value) {
            $compiledErrorMessage = \str_replace('{' . $key . '}', $value, $compiledErrorMessage);
        }

        // Remove any unused placeholders from the message
        $compiledErrorMessage = \preg_replace('/{.+}/', '', $compiledErrorMessage);

        return $compiledErrorMessage;
    }
}
