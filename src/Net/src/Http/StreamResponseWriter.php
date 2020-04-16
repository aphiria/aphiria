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

use Aphiria\IO\Streams\IStream;
use Aphiria\IO\Streams\Stream;

/**
 * Defines the stream response writer
 */
class StreamResponseWriter implements IResponseWriter
{
    /** @var IStream The output stream to write the body to */
    private IStream $outputStream;

    /**
     * @param IStream|null $outputStream The output stream to write the body to (null defaults to PHP's output stream)
     */
    public function __construct(IStream $outputStream = null)
    {
        $this->outputStream = $outputStream ?? new Stream(fopen('php://output', 'wb'));
    }

    /**
     * @inheritdoc
     */
    public function headersAreSent(): bool
    {
        return headers_sent();
    }

    /**
     * @inheritdoc
     */
    public function writeResponse(IHttpResponseMessage $response): void
    {
        if ($this->headersAreSent()) {
            return;
        }

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
