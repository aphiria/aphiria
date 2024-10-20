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
 * Defines the base class for media type formatters to implement
 */
abstract class MediaTypeFormatter implements IMediaTypeFormatter
{
    /** @inheritdoc */
    public string $defaultEncoding {
        get {
            return $this->supportedEncodings[0];
        }
    }

    /** @inheritdoc */
    public string $defaultMediaType {
        get {
            return $this->supportedMediaTypes[0];
        }
    }

    /**
     * Checks whether or not an encoding is supported
     *
     * @param string $encoding The encoding to check
     * @return bool True if the encoding is supported, otherwise false
     */
    protected function encodingIsSupported(string $encoding): bool
    {
        $lowercaseSupportedEncodings = \array_map('strtolower', $this->supportedEncodings);
        $lowercaseEncoding = \strtolower($encoding);

        return \in_array($lowercaseEncoding, $lowercaseSupportedEncodings, true);
    }
}
