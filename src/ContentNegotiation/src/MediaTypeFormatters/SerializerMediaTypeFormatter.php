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

use Aphiria\IO\Streams\IStream;
use Aphiria\Reflection\TypeResolver;
use InvalidArgumentException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Defines the base class for media type formatters that use serializers to extend
 */
abstract class SerializerMediaTypeFormatter extends MediaTypeFormatter
{
    /**
     * @param SerializerInterface $serializer The serializer this formatter uses
     * @param string $format The format to (de)serialize to
     */
    protected function __construct(private readonly SerializerInterface $serializer, private readonly string $format)
    {
    }

    /**
     * @inheritdoc
     */
    public function readFromStream(IStream $stream, string $type): int|float|bool|string|object|array
    {
        if (!$this->canReadType($type)) {
            throw new InvalidArgumentException(static::class . " cannot read type $type");
        }

        /** @var int|float|bool|string|object|array $value */
        $value = $this->serializer->deserialize((string)$stream, $type, $this->format);

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function writeToStream(int|float|bool|string|object|array $value, IStream $stream, ?string $encoding): void
    {
        $type = TypeResolver::resolveType($value);

        if (!$this->canWriteType($type)) {
            throw new InvalidArgumentException(static::class . " cannot write type $type");
        }

        $encoding ??= $this->defaultEncoding;

        if (!$this->encodingIsSupported($encoding)) {
            throw new InvalidArgumentException("$encoding is not supported for " . static::class);
        }

        $serializedObject = $this->serializer->serialize($value, $this->format);
        $encodedSerializedObject = \mb_convert_encoding($serializedObject, $encoding);
        $stream->write($encodedSerializedObject);
    }
}
