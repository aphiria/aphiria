<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\ContentNegotiation;

use InvalidArgumentException;
use Opulence\IO\Streams\IStream;

/**
 * Defines the base class for text-based media type formatters
 */
abstract class TextMediaTypeFormatter implements IMediaTypeFormatter
{
    /**
     * @inheritdoc
     */
    public function readFromStream(IStream $stream, string $type, bool $readAsArrayOfType = false)
    {
        if (\strtolower($type) !== 'string') {
            throw new InvalidArgumentException(static::class . ' can only read strings');
        }

        if ($readAsArrayOfType) {
            throw new InvalidArgumentException(static::class . ' can not read arrays');
        }

        return (string)$stream;
    }

    /**
     * @inheritdoc
     */
    public function writeToStream($value, IStream $stream): void
    {
        if (!\is_string($value)) {
            throw new InvalidArgumentException(static::class . ' can only write strings');
        }

        $stream->write($value);
    }
}