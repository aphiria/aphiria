<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
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
    /** @inheritdoc */
    public string $defaultLocale {
        set {
            $this->_defaultLocale = $value;
        }
    }
    /** @var string The default locale, if none is specified */
    private string $_defaultLocale;

    /**
     * @param IErrorMessageTemplateRegistry $errorMessageTemplates The error message template registry to use
     * @param string $defaultLocale The default locale
     */
    public function __construct(
        private readonly IErrorMessageTemplateRegistry $errorMessageTemplates = new DefaultErrorMessageTemplateRegistry(),
        string $defaultLocale = 'en'
    ) {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @inheritdoc
     */
    public function interpolate(
        string $errorMessageId,
        array $errorMessagePlaceholders = [],
        ?string $locale = null
    ): string {
        $interpolatedErrorMessage = MessageFormatter::formatMessage(
            $locale ?? $this->_defaultLocale,
            $this->errorMessageTemplates->getErrorMessageTemplate($errorMessageId, $locale),
            $errorMessagePlaceholders
        );

        if ($interpolatedErrorMessage === false) {
            throw new ErrorMessageInterpolationException("Could not interpolate error message ID $errorMessageId");
        }

        return $interpolatedErrorMessage;
    }
}
