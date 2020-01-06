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
 * Defines the error message interpolater that interpolates ICU message formats
 */
final class IcuFormatErrorMessageInterpolater implements IErrorMessageInterpolater
{
    /** @var string The default locale, if none is specified */
    private string $defaultLocale;

    /**
     * @param string $defaultLocale The default locale
     */
    public function __construct(string $defaultLocale = 'en')
    {
        $this->setDefaultLocale($defaultLocale);
    }

    /**
     * @inheritdoc
     */
    public function interpolate(string $errorMessageId, array $errorMessagePlaceholders = [], string $locale = null): string
    {
        $interpolatedErrorMessage = \MessageFormatter::formatMessage(
            $locale ?? $this->defaultLocale,
            $errorMessageId,
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
