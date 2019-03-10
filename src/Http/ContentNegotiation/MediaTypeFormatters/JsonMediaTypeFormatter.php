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

use Aphiria\Serialization\JsonSerializer;

/**
 * Defines the JSON media type formatter
 */
final class JsonMediaTypeFormatter extends SerializerMediaTypeFormatter
{
    /** @var array The list of supported character encodings */
    private static $supportedEncodings = ['utf-8'];
    /** @var array The list of supported media types */
    private static $supportedMediaTypes = ['application/json', 'text/json'];

    /**
     * @param JsonSerializer|null $serializer The JSON serializer to use
     */
    public function __construct(JsonSerializer $serializer = null)
    {
        parent::__construct($serializer ?? new JsonSerializer());
    }

    /**
     * @inheritdoc
     */
    public function canReadType(string $type): bool
    {
        // We default to true and let a SerializationException bubble up in case it cannot read this type
        return true;
    }

    /**
     * @inheritdoc
     */
    public function canWriteType(string $type): bool
    {
        // We default to true and let a SerializationException bubble up in case it cannot write this type
        return true;
    }

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
