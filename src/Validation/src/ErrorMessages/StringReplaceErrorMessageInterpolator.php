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
 * Defines the error message interpolator that does a simple string replacement to interpolate error messages
 */
final class StringReplaceErrorMessageInterpolator implements IErrorMessageInterpolator
{
    /** @var string|null The default locale */
    private ?string $defaultLocale = null;

    /**
     * @inheritdoc
     */
    public function interpolate(string $errorMessageId, array $errorMessagePlaceholders = [], string $locale = null): string
    {
        $interpolatedErrorMessage = $errorMessageId;

        foreach ($errorMessagePlaceholders as $key => $value) {
            $interpolatedErrorMessage = \str_replace('{' . $key . '}', $value, $interpolatedErrorMessage);
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
