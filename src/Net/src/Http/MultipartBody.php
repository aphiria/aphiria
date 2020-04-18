<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Aphiria\IO\Streams\MultiStream;
use Aphiria\IO\Streams\Stream;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines a multipart HTTP body
 */
class MultipartBody extends StreamBody
{
    /** @var MultipartBody[] The list of body parts */
    private array $parts;
    /** @var string The boundary string */
    private string $boundary;

    /**
     * @param MultipartBodyPart[] $parts The list of multipart body parts
     * @param string $boundary The boundary between the parts
     * @throws RuntimeException Thrown if the boundary could not be generated
     * @throws InvalidArgumentException Thrown if the internal stream could not be generated
     */
    public function __construct(array $parts, string $boundary = null)
    {
        $this->parts = $parts;
        $this->boundary = $boundary ?? self::createDefaultBoundary();
        $stream = new MultiStream();

        // Create the header boundary
        $stream->addStream($this->createStreamFromString("--{$this->boundary}"));
        $numParts = \count($this->parts);

        for ($i = 0;$i < $numParts;$i++) {
            if ($i > 0) {
                $stream->addStream($this->createStreamFromString("\r\n--{$this->boundary}"));
            }

            if (\count($this->parts[$i]->getHeaders()) > 0) {
                $stream->addStream($this->createStreamFromString("\r\n{$this->parts[$i]->getHeaders()}"));
            }

            $stream->addStream($this->createStreamFromString("\r\n\r\n"));

            if (($body = $this->parts[$i]->getBody()) !== null) {
                $stream->addStream($body->readAsStream());
            }
        }

        // Create the footer boundary
        $stream->addStream($this->createStreamFromString("\r\n--{$this->boundary}--"));

        parent::__construct($stream);
    }

    /**
     * Gets the boundary string
     *
     * @return string The boundary string
     */
    public function getBoundary(): string
    {
        return $this->boundary;
    }

    /**
     * Gets the multipart body parts that make up the body
     *
     * @return MultipartBodyPart[] The list of body parts
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * Creates the default boundary in case one wasn't specified
     *
     * @return string The default boundary
     * @throws RuntimeException Thrown if random bytes could not be generated
     */
    private static function createDefaultBoundary(): string
    {
        // Cannot test a failing random byte call
        // @codeCoverageIgnoreStart
        try {
            // The following creates a UUID v4
            $string = random_bytes(16);
            $string[6] = \chr(\ord($string[6]) & 0x0f | 0x40);
            $string[8] = \chr(\ord($string[8]) & 0x3f | 0x80);

            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($string), 4));
        } catch (Exception $ex) {
            throw new RuntimeException('Failed to generate random bytes', 0, $ex);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Creates a stream from a string
     *
     * @param string $string The string to create a stream for
     * @return Stream The stream
     * @throws RuntimeException Thrown if the stream could not be written to
     */
    private function createStreamFromString(string $string): Stream
    {
        $stream = new Stream(fopen('php://temp', 'r+b'));
        $stream->write($string);
        $stream->rewind();

        return $stream;
    }
}
