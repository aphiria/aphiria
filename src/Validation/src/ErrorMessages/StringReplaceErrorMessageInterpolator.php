<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\ErrorMessages;

/**
 * Defines the error message interpolator that does a simple string replacement to interpolate error messages
 */
final class StringReplaceErrorMessageInterpolator implements IErrorMessageInterpolator
{
    /** @var IErrorMessageTemplateRegistry The registry of error message templates */
    private IErrorMessageTemplateRegistry $errorMessageTemplates;
    /** @var string|null The default locale */
    private ?string $defaultLocale = null;

    /**
     * @param IErrorMessageTemplateRegistry|null $errorMessageTemplates The error message template registry to use
     */
    public function __construct(IErrorMessageTemplateRegistry $errorMessageTemplates = null)
    {
        $this->errorMessageTemplates = $errorMessageTemplates ?? new DefaultErrorMessageTemplateRegistry();
    }

    /**
     * @inheritdoc
     */
    public function interpolate(string $errorMessageId, array $errorMessagePlaceholders = [], string $locale = null): string
    {
        $interpolatedErrorMessage = $this->errorMessageTemplates->getErrorMessageTemplate($errorMessageId, $locale);

        foreach ($errorMessagePlaceholders as $key => $value) {
            $interpolatedErrorMessage = \str_replace('{' . $key . '}', (string)$value, $interpolatedErrorMessage);
        }

        // Remove any unused placeholders from the message
        $interpolatedErrorMessage = \preg_replace('/{.+}/', '', $interpolatedErrorMessage);

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
