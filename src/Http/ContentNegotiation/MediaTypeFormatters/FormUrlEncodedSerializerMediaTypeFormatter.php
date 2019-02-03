<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters;

use Opulence\Serialization\FormUrlEncodedSerializer;
use Opulence\Serialization\TypeResolver;

/**
 * Defines the form URL-encoded media type formatter
 */
class FormUrlEncodedSerializerMediaTypeFormatter extends SerializerMediaTypeFormatter
{
    /** @var array The list of supported character encodings */
    private static $supportedEncodings = ['utf-8', 'ISO-8859-1'];
    /** @var array The list of supported media types */
    private static $supportedMediaTypes = ['application/x-www-form-urlencoded'];

    /**
     * @param FormUrlEncodedSerializer|null $serializer The form URL-encoded serializer to use
     */
    public function __construct(FormUrlEncodedSerializer $serializer = null)
    {
        parent::__construct($serializer ?? new FormUrlEncodedSerializer());
    }

    /**
     * @inheritdoc
     */
    public function canReadType(string $type): bool
    {
        return TypeResolver::typeIsArray($type) || \class_exists($type);
    }

    /**
     * @inheritdoc
     */
    public function canWriteType(string $type): bool
    {
        return TypeResolver::typeIsArray($type) || \class_exists($type);
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
