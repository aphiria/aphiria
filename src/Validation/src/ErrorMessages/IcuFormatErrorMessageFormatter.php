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
 * Defines the error message formatter that formats ICU message formats
 */
final class IcuFormatErrorMessageFormatter implements IErrorMessageFormatter
{
    /** @var string The fallback locale, if none is specified */
    private string $fallbackLocale;

    /**
     * @param string $fallbackLocale The fallback locale
     */
    public function __construct(string $fallbackLocale = 'en-US')
    {
        $this->fallbackLocale = $fallbackLocale;
    }

    /**
     * @inheritdoc
     */
    public function format(string $errorMessageId, array $errorMessagePlaceholders = [], string $locale = null): string
    {
        $formattedErrorMessage = \MessageFormatter::formatMessage(
            $locale ?? $this->fallbackLocale,
            $errorMessageId,
            $errorMessagePlaceholders
        );

        if ($formattedErrorMessage === false) {
            throw new ErrorMessageFormattingException("Could not format error message ID $errorMessageId");
        }

        return $formattedErrorMessage;
    }
}
