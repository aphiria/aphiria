<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\ErrorMessages;

use MessageFormatter;

/**
 * Defines the error message interpolator that interpolates ICU message formats
 */
final class IcuFormatErrorMessageInterpolator implements IErrorMessageInterpolator
{
    /** @var string The default locale, if none is specified */
    private string $defaultLocale;

    /**
     * @param IErrorMessageTemplateRegistry $errorMessageTemplates The error message template registry to use
     * @param string $defaultLocale The default locale
     */
    public function __construct(
        private readonly IErrorMessageTemplateRegistry $errorMessageTemplates = new DefaultErrorMessageTemplateRegistry(),
        string $defaultLocale = 'en'
    ) {
        $this->setDefaultLocale($defaultLocale);
    }

    /**
     * @inheritdoc
     */
    public function interpolate(string $errorMessageId, array $errorMessagePlaceholders = [], string $locale = null): string
    {
        $interpolatedErrorMessage = MessageFormatter::formatMessage(
            $locale ?? $this->defaultLocale,
            $this->errorMessageTemplates->getErrorMessageTemplate($errorMessageId, $locale),
            $errorMessagePlaceholders
        );

        if ($interpolatedErrorMessage === false) {
            throw new ErrorMessageInterpolationException("Could not interpolate error message ID $errorMessageId");
        }

        return $interpolatedErrorMessage;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultLocale(string $locale): void
    {
        $this->defaultLocale = $locale;
    }
}
