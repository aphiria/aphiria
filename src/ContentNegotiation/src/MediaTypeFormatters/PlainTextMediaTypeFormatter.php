<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\MediaTypeFormatters;

/**
 * Defines the plain text media type formatter
 */
final class PlainTextMediaTypeFormatter extends TextMediaTypeFormatter
{
    /** @var string[] The list of supported character encodings */
    private static array $supportedEncodings = ['utf-8'];
    /** @var string[] The list of supported media types */
    private static array $supportedMediaTypes = ['text/plain'];

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
