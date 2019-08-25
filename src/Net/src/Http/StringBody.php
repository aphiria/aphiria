<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Aphiria\IO\Streams\IStream;
use Aphiria\IO\Streams\Stream;

/**
 * Defines the string HTTP body
 */
class StringBody implements IHttpBody
{
    /** @var string The body content */
    protected string $content = '';
    /** @var IStream|null The underlying stream */
    private ?IStream $stream = null;

    /**
     * @param string $content The body content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
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
    public function getLength(): ?int
    {
        return mb_strlen($this->content);
    }

    /**
     * @inheritdoc
     */
    public function readAsStream(): IStream
    {
        if ($this->stream === null) {
            $this->stream = new Stream(fopen('php://temp', 'r+b'));
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
