<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\MediaTypeFormatters;

/**
 * Defines the plain text media type formatter
 */
final class PlainTextMediaTypeFormatter extends TextMediaTypeFormatter
{
    /** @inheritdoc */
    public array $supportedEncodings {
        get => ['utf-8'];
    }
    /** @inheritdoc */
    public array $supportedMediaTypes {
        get => ['text/plain'];
    }
}
