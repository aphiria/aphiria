<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Aphiria\IO\Streams\IStream;
use Aphiria\IO\Streams\Stream;

/**
 * Defines the string HTTP body
 */
class StringBody implements IBody
{
    /** @inheritdoc */
    public ?int $length {
        get => \mb_strlen($this->content);
    }
    /** @var IStream|null The underlying stream */
    private ?IStream $stream = null;

    /**
     * @param string $content The body content
     */
    public function __construct(protected readonly string $content)
    {
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->readAsString();
    }

    /**
     * @inheritdoc
     */
    public function readAsStream(): IStream
    {
        if ($this->stream === null) {
            $this->stream = new Stream(\fopen('php://temp', 'r+b'));
            $this->stream->write($this->content);
            $this->stream->rewind();
        }

        return $this->stream;
    }

    /**
     * @inheritdoc
     */
    public function readAsString(): string
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function writeToStream(IStream $stream): void
    {
        $stream->write($this->content);
    }
}
