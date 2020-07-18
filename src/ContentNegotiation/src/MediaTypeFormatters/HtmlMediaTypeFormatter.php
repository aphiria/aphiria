<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\MediaTypeFormatters;

/**
 * Defines the HTML media type formatter
 */
final class HtmlMediaTypeFormatter extends TextMediaTypeFormatter
{
    /** @var array The list of supported character encodings */
    private static array $supportedEncodings = ['utf-8', 'utf-16'];
    /** @var array The list of supported media types */
    private static array $supportedMediaTypes = ['text/html'];

    /**
     * @inheritdoc
     */
    public function getSupportedEncodings(): array
    {
        return self::$supportedEncodings;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedMediaTypes(): array
    {
        return self::$supportedMediaTypes;
    }
}
