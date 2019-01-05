<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\ContentNegotiation\MediaTypeFormatters;

use Opulence\Serialization\JsonSerializer;

/**
 * Defines the JSON media type formatter
 */
class JsonMediaTypeFormatter extends SerializerMediaTypeFormatter
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
