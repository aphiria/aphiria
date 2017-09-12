<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Responses;

use Opulence\IO\Streams\IStream;
use Opulence\IO\Streams\Stream;

/**
 * Defines the response sender
 */
class ResponseSender
{
    /** @var IStream The output stream */
    private $outputStream = null;
    
    /**
     * @param IStream|null $outputStream The output stream (null defaults to PHP's output stream)
     */
    public function __construct(IStream $outputStream = null)
    {
        $this->outputStream = $outputStream ?? new Stream(fopen('php://output', 'r+'));
    }
    
    /**
     * Sends the response to the output stream
     * 
     * @param IHttpResponseMessage $response The response to send
     */
    public function sendResponse(IHttpResponseMessage $response) : void
    {
        // Todo: Write the status code, reason phrase, and any headers
        $response->getBody()->writeToStream($this->outputStream);
    }
}
