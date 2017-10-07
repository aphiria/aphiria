<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpBody;

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
