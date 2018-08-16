<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\ContentNegotiation\MediaTypeFormatters;

use Opulence\Serialization\FormUrlEncodedSerializer;

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
