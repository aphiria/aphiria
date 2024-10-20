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

/**
 * Defines the error message interpolator that does a simple string replacement to interpolate error messages
 */
final class StringReplaceErrorMessageInterpolator implements IErrorMessageInterpolator
{
    /** @inheritdoc */
    public string $defaultLocale {
        set {
            $this->_defaultLocale = $value;
        }
    }
    /**
     * The default locale if one is set, otherwise null
     *
     * @var string|null
     * @note This is a dummy variable that exists solely to make the public default locale write-only
     */
    private ?string $_defaultLocale = null;

    /**
     * @param IErrorMessageTemplateRegistry $errorMessageTemplates The error message template registry to use
     */
    public function __construct(
        private readonly IErrorMessageTemplateRegistry $errorMessageTemplates = new DefaultErrorMessageTemplateRegistry()
    ) {
    }

    /**
     * @inheritdoc
     */
    public function interpolate(
        string $errorMessageId,
        array $errorMessagePlaceholders = [],
        ?string $locale = null
    ): string {
        $interpolatedErrorMessage = $this->errorMessageTemplates->getErrorMessageTemplate($errorMessageId, $locale);

        foreach ($errorMessagePlaceholders as $key => $value) {
            $interpolatedErrorMessage = \str_replace('{' . $key . '}', (string)$value, $interpolatedErrorMessage);
        }

        // Remove any unused placeholders from the message
        $interpolatedErrorMessage = \preg_replace('/{.+}/', '', $interpolatedErrorMessage);

        return $interpolatedErrorMessage;
    }
}
