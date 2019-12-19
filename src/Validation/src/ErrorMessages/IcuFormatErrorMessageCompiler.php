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
 * Defines the error message compiler that compiles ICU message formats
 */
final class IcuFormatErrorMessageCompiler implements IErrorMessageCompiler
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
    public function compile(string $errorMessageId, array $errorMessagePlaceholders = [], string $locale = null): string
    {
        $compiledErrorMessage = \MessageFormatter::formatMessage(
            $locale ?? $this->fallbackLocale,
            $errorMessageId,
            $errorMessagePlaceholders
        );

        if ($compiledErrorMessage === false) {
            throw new ErrorMessageCompilationException("Could not compile error message ID $errorMessageId");
        }

        return $compiledErrorMessage;
    }
}
