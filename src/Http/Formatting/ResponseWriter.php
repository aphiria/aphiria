<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

use Opulence\IO\Streams\IStream;
use Opulence\IO\Streams\Stream;
use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Defines the response writer
 */
class ResponseWriter
{
    /** @var IStream The output stream to write the body to */
    private $outputStream = null;

    /**
     * @param IStream|null $outputStream The output stream to write the body to (null defaults to PHP's output stream)
     */
    public function __construct(IStream $outputStream = null)
    {
        $this->outputStream = $outputStream ?? new Stream(fopen('php://output', 'r+'));
    }

    /**
     * Writes the response to the output stream
     *
     * @param IHttpResponseMessage $response The response to write
     */
    public function writeResponse(IHttpResponseMessage $response) : void
    {
        $startLine = "HTTP/{$response->getProtocolVersion()} {$response->getStatusCode()}";

        if (($reasonPhrase = $response->getReasonPhrase()) !== null) {
            $startLine .= " $reasonPhrase";
        }

        header($startLine);

        foreach ($response->getHeaders() as $kvp) {
            header($kvp->getKey() . ': ' . implode(', ', $kvp->getValue()));
        }

        if (($body = $response->getBody()) !== null) {
            $body->writeToStream($this->outputStream);
        }
    }
}
