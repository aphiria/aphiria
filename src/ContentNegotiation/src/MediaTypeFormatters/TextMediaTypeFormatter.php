<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\MediaTypeFormatters;

use Aphiria\IO\Streams\IStream;
use Aphiria\Reflection\TypeResolver;
use InvalidArgumentException;

/**
 * Defines the base class for text-based media type formatters
 */
abstract class TextMediaTypeFormatter extends MediaTypeFormatter
{
    /**
     * @inheritdoc
     */
    public function canReadType(string $type): bool
    {
        return \strtolower($type) === 'string';
    }

    /**
     * @inheritdoc
     */
    public function canWriteType(string $type): bool
    {
        return \strtolower($type) === 'string';
    }

    /**
     * @inheritdoc
     */
    public function readFromStream(IStream $stream, string $type):  int|float|bool|string|object|array
    {
        if (!$this->canReadType($type)) {
            throw new InvalidArgumentException(static::class . ' can only read strings');
        }

        return (string)$stream;
    }

    /**
     * @inheritdoc
     */
    public function writeToStream(int|float|bool|string|object|array $value, IStream $stream, ?string $encoding): void
    {
        if (!$this->canWriteType(TypeResolver::resolveType($value))) {
            throw new InvalidArgumentException(static::class . ' can only write strings');
        }

        /** @var string $value We've verified that the value is a string above */
        $encoding = $encoding ?? $this->getDefaultEncoding();

        if (!$this->encodingIsSupported($encoding)) {
            throw new InvalidArgumentException("$encoding is not supported for " . static::class);
        }

        $encodedValue = \mb_convert_encoding($value, $encoding);
        $stream->write($encodedValue);
    }
}
