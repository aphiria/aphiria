<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
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
    /** @var SerializerInterface The serializer this formatter uses */
    private SerializerInterface $serializer;
    /** @var string The format to (de)serialize to */
    private string $format;

    /**
     * @param SerializerInterface $serializer The serializer this formatter uses
     * @param string $format The format to (de)serialize to
     */
    protected function __construct(SerializerInterface $serializer, string $format)
    {
        $this->serializer = $serializer;
        $this->format = $format;
    }

    /**
     * @inheritdoc
     */
    public function readFromStream(IStream $stream, string $type)
    {
        if (!$this->canReadType($type)) {
            throw new InvalidArgumentException(static::class . " cannot read type $type");
        }

        return $this->serializer->deserialize((string)$stream, $type, $this->format);
    }

    /**
     * @inheritdoc
     */
    public function writeToStream($value, IStream $stream, ?string $encoding): void
    {
        $type = TypeResolver::resolveType($value);

        if (!$this->canWriteType($type)) {
            throw new InvalidArgumentException(static::class . " cannot write type $type");
        }

        $encoding = $encoding ?? $this->getDefaultEncoding();

        if (!$this->encodingIsSupported($encoding)) {
            throw new InvalidArgumentException("$encoding is not supported for " . static::class);
        }

        $serializedObject = $this->serializer->serialize($value, $this->format);
        $encodedSerializedObject = mb_convert_encoding($serializedObject, $encoding);
        $stream->write($encodedSerializedObject);
    }
}
