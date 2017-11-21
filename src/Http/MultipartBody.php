<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

use Opulence\IO\Streams\MultiStream;
use Opulence\IO\Streams\Stream;

/**
 * Defines a multipart HTTP body
 */
class MultipartBody extends StreamBody
{
    /** @var MultipartBody[] The list of body parts */
    private $parts = [];

    /**
     * @param MultipartBodyPart[] $parts The list of multipart body parts
     * @param string $boundary The boundary between the parts
     */
    public function __construct(array $parts, string $boundary = null)
    {
        $this->parts = $parts;
        $boundary = $boundary ?? $this->createDefaultBoundary();
        $stream = new MultiStream();

        // Create the header boundary
        $stream->addStream($this->createStreamFromString("--{$boundary}"));

        for ($i = 0;$i < count($this->parts);$i++) {
            if ($i > 0) {
                $stream->addStream($this->createStreamFromString("\r\n--{$boundary}"));
            }

            if (count($this->parts[$i]->getHeaders()) > 0) {
                $stream->addStream($this->createStreamFromString("\r\n{$this->parts[$i]->getHeaders()}"));
            }

            $stream->addStream($this->createStreamFromString("\r\n\r\n"));
            $stream->addStream($this->parts[$i]->getBody()->readAsStream());
        }

        // Create the footer boundary
        $stream->addStream($this->createStreamFromString("\r\n--{$boundary}--"));

        parent::__construct($stream);
    }

    /**
     * Gets the multipart body parts that make up the body
     *
     * @return MultipartBodyPart[] The list of body parts
     */
    public function getParts() : array
    {
        return $this->parts;
    }

    /**
     * Creates the default boundary in case one wasn't specified
     *
     * @return string The default boundary
     */
    private function createDefaultBoundary() : string
    {
        // The following creates a UUID v4
        $string = \random_bytes(16);
        $string[6] = \chr(\ord($string[6]) & 0x0f | 0x40);
        $string[8] = \chr(\ord($string[8]) & 0x3f | 0x80);

        return \vsprintf('%s%s-%s-%s-%s-%s%s%s', \str_split(\bin2hex($string), 4));
    }

    /**
     * Creates a stream from a string
     *
     * @param string $string The string to create a stream for
     * @return string The stream
     */
    private function createStreamFromString(string $string) : Stream
    {
        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write($string);
        $stream->rewind();

        return $stream;
    }
}
