<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

use Opulence\IO\Streams\Stream;
use RuntimeException;
use Throwable;

/**
 * Defines a multipart body part
 */
class MultipartBodyPart
{
    /** @var HttpHeaders The headers of this body part */
    private $headers = null;
    /** @var IHttpBody|null The body of this body part if one is set, otherwise null */
    private $body = null;

    /**
     * @param HttpHeaders $headers The headers of this body part
     * @param IHttpBody|null $body The body of this body part if one is set, otherwise null
     */
    public function __construct(HttpHeaders $headers, ?IHttpBody $body)
    {
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * Gets the multipart body part as a string
     * Note: This can be used in raw HTTP messages
     *
     * @return string The body part as a string
     */
    public function __toString() : string
    {
        return "{$this->headers}\r\n\r\n" . ($this->body === null ? '' : (string)$this->body);
    }

    /**
     * Copies the body to a file path
     *
     * @param string $path The destination path to copy to
     * @throws RuntimeException Thrown if the destination path could not be written to
     */
    public function copyBodyToFile(string $path) : void
    {
        $pathInfo = pathinfo($path);

        if (!is_dir($pathInfo['dirname'])) {
            throw new RuntimeException("Directory {$pathInfo['dirname']} must be created before copying body");
        }

        if (!file_exists($path)) {
            touch($path);
        }

        if (!is_writable($path)) {
            throw new RuntimeException("Path $path is not writable");
        }

        try {
            $destinationHandle = fopen($path, 'r+');

            if ($destinationHandle === false) {
                throw new RuntimeException("Could not open path $path");
            }

            $destinationStream = new Stream($destinationHandle);
        } catch (Throwable $ex) {
            throw new RuntimeException("Could not open path $path", 0, $ex);
        }

        $bodyStream = $this->body->readAsStream();
        $bodyStream->rewind();
        $bodyStream->copyToStream($destinationStream);
    }

    /**
     * Gets the body of this body part
     *
     * @return IHttpBody|null The body of this body part if one is set, otherwise null
     */
    public function getBody() : ?IHttpBody
    {
        return $this->body;
    }

    /**
     * Gets the headers of this body part
     *
     * @return HttpHeaders The headers of this body part
     */
    public function getHeaders() : HttpHeaders
    {
        return $this->headers;
    }
}
