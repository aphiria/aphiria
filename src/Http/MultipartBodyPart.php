<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

/**
 * Defines a multipart body part
 */
class MultipartBodyPart
{
    /** @var HttpHeaders The headers of this body part */
    private $headers;
    /** @var IHttpBody|null The body of this body part if one is set, otherwise null */
    private $body;

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
