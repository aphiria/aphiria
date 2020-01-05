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
 * Defines the interface for error message interpolaters to implement
 */
interface IErrorMessageInterpolater
{
    /**
     * Interpolates an error message ID along with placeholders into human-readable error messages
     *
     * @param string $errorMessageId The ID of the error message to interpolate
     * @param array $errorMessagePlaceholders The optional mapping of placeholder names to values
     * @param string|null $locale The locale to use when interpolating the message, or null if not considering it
     * @return string The interpolated error message
     * @throws ErrorMessageInterpolationException Thrown if the error message could not be interpolated
     */
    public function interpolate(string $errorMessageId, array $errorMessagePlaceholders = [], string $locale = null): string;

    /**
     * Sets the default locale
     *
     * @param string $locale The default locale to use
     */
    public function setDefaultLocale(string $locale): void;
}
