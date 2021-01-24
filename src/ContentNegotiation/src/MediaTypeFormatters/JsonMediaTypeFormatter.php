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

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Defines the JSON media type formatter
 */
final class JsonMediaTypeFormatter extends SerializerMediaTypeFormatter
{
    /** @var string[] The list of supported character encodings */
    private static array $supportedEncodings = ['utf-8'];
    /** @var string[] The list of supported media types */
    private static array $supportedMediaTypes = ['application/json', 'text/json', 'application/problem+json'];

    /**
     * @param SerializerInterface|null $serializer The JSON serializer to use
     */
    public function __construct(SerializerInterface $serializer = null)
    {
        parent::__construct($serializer ?? new Serializer([new ObjectNormalizer()], [new JsonEncoder()]), 'json');
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
