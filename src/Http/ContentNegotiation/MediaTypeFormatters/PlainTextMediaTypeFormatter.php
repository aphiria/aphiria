<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters;

/**
 * Defines the plain text media type formatter
 */
final class PlainTextMediaTypeFormatter extends TextMediaTypeFormatter
{
    /** @var array The list of supported character encodings */
    private static $supportedEncodings = ['utf-8'];
    /** @var array The list of supported media types */
    private static $supportedMediaTypes = ['text/plain'];

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
